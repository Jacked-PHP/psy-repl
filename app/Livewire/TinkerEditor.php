<?php

namespace App\Livewire;

use App\Enums\DockerType;
use App\Enums\ShellMeta;
use App\Enums\SshPasswordType;
use App\Models\Shell;
use App\Models\User;
use Exception;
use Illuminate\Process\ProcessResult;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Native\Laravel\Dialog;
use Ramsey\Uuid\Uuid;

class TinkerEditor extends Component
{
    // record data
    public ?int $shellId = null;

    public string $title = '';
    
    public string $textSize = 'md';

    public string $php_binary = '';

    public ?string $path = null;

    public string $code = 'dd(App\Models\User::get());';

    public string $output = '';

    // docker settings
    public bool $isDockerContext = false;

    public ?string $dockerContainer = null;

    public ?string $dockerWorkdir = null;

    public ?string $dockerType = null;

    // generic settings
    public bool $settingsOpen = false;

    public bool $wordWrap = false;

    public bool $isShowingHidden = false;

    // remote settings
    public bool $isRemoteContext = false;

    public ?string $remoteHost = null;

    public ?string $remotePort = null;

    public ?string $remoteUser = null;

    public ?string $remotePassword = null;

    public ?string $remotePasswordType = null;

    protected string $phpOpenTag = '<?php'.PHP_EOL;

    public function mount(Shell $shell)
    {
        /** @var User $user */
        $user = auth()->user();
        if ($user === null) {
            throw new Exception('User not logged in');
        }

        $this->shellId = $shell->id;
        $this->title = $shell->title ?? '';
        $this->php_binary = $shell->php_binary ?? '';
        $this->code = $shell->code ?? '';
        $this->path = $shell->path ?? '';
        $this->output = $shell->output ?? '';

        // general meta
        $this->settingsOpen = $shell->getMeta(ShellMeta::SETTINGS_OPEN->value, false);
        $this->wordWrap = $shell->getMeta(ShellMeta::WORD_WRAP->value, false);
        $this->isShowingHidden = $shell->getMeta(ShellMeta::SHOWING_HIDDEN->value, false);

        // docker meta
        $this->isDockerContext = $shell->getMeta(ShellMeta::IS_DOCKER_CONTEXT->value, false);
        $this->dockerWorkdir = $shell->getMeta(ShellMeta::DOCKER_WORKDIR->value, '');
        $this->dockerContainer = $shell->getMeta(ShellMeta::DOCKER_CONTAINER->value, '');
        $this->dockerType = $shell->getMeta(ShellMeta::DOCKER_TYPE->value, DockerType::DOCKER_COMPOSE->value);

        // remote meta
        $this->isRemoteContext = $shell->getMeta(ShellMeta::IS_REMOTE_CONTEXT->value, false);
        $this->remoteHost = $shell->getMeta(ShellMeta::REMOTE_HOST->value, '');
        $this->remotePort = $shell->getMeta(ShellMeta::REMOTE_PORT->value, '');
        $this->remoteUser = $shell->getMeta(ShellMeta::REMOTE_USER->value, '');
        $this->remotePassword = $shell->getMeta(ShellMeta::REMOTE_PASSWORD->value, '');
        $this->remotePasswordType = $shell->getMeta(ShellMeta::REMOTE_PASSWORD_TYPE->value, SshPasswordType::PASSWORD->value);

        // editor settings
        $this->textSize = $shell->getMeta(ShellMeta::TEXT_SIZE->value, 'md');
    }

    public function executeCode(string $content)
    {
        try {
            if ($this->isDockerContext) {
                $result = $this->executeCodeDocker($content);
            } else {
                $result = $this->executeCodeMetal($content);
            }

            return $this->processOutput($content, $result->output(), $result->errorOutput());
        } catch (Exception $e) {
            return 'Error: '.$e->getMessage();
        }
    }

    protected function processOutput(string $content, string $output, string $error): string
    {
        if (! empty($error) && empty($output)) {
            return json_encode([
                'error' => $error,
                'output' => $output,
            ]);
        }

        $shell = Shell::find($this->shellId);
        $shell->code = $content;
        $shell->output = $output;
        $shell->save();

        return $shell->output;
    }

    protected function executeCodeMetal(string $content): ProcessResult
    {
        if (
            $this->php_binary === null
            || empty(Process::run($this->php_binary . ' -v')->output())
        ) {
            throw new Exception('PHP Binary not found - review your settings.');
        }

        $projectPath = $this->path;
        $tempPhpFile = Uuid::uuid4()->toString() . '.php';
        $command = $this->php_binary . ' artisan tinker --execute="include(\''.storage_path('app/' . $tempPhpFile) . '\')"';

        if (! $this->isLaravelFolder($projectPath)) {
            throw new Exception('The path is not pointing to a valid Laravel project!');
        }

        Storage::write($tempPhpFile, $this->phpOpenTag . $content);
        $result = Process::path($projectPath)
            ->env($this->loadCustomEnv(
                $projectPath . DIRECTORY_SEPARATOR . '.env'
            ))
            ->run($command);

        Storage::delete($tempPhpFile);

        return $result;
    }

    protected function executeCodeDocker(string $content): ProcessResult
    {
        $container = $this->dockerContainer;
        $workdir = $this->dockerWorkdir;
        $tempPhpFile = Uuid::uuid4()->toString().'.php';
        $command = 'php artisan tinker --execute=\"include(\''.$workdir.'/'.$tempPhpFile.'\')\"';
        $projectPath = $this->path;
        $result = Process::path($projectPath)->run('command -v docker compose');

        if (empty($result)) {
            throw new Exception('Docker not available in the system!');
        }

        if ($this->dockerType === DockerType::DOCKER_COMPOSE->value) {
            return $this->executeDockerCompose(
                container: $container,
                workdir: $workdir,
                tempPhpFile: $tempPhpFile,
                command: $command,
                projectPath: $projectPath,
                content: $content,
            );
        }

        return $this->executeDocker(
            container: $container,
            workdir: $workdir,
            tempPhpFile: $tempPhpFile,
            command: $command,
            projectPath: $projectPath,
            content: $content,
        );
    }

    protected function executeDockerCompose(
        string $container,
        string $workdir,
        string $tempPhpFile,
        string $command,
        string $projectPath,
        string $content,
    ): ProcessResult {
        $dockerCommand = 'docker compose exec '.$container.' bash -c "cd '.$workdir.' && '.$command.'"';
        Storage::write($tempPhpFile, $this->phpOpenTag . $content);
        Process::path($projectPath)->run('docker compose cp '.storage_path('app/'.$tempPhpFile).' '.$container.':'.$workdir.'/'.$tempPhpFile);
        $result = Process::path($projectPath)->run($dockerCommand);

        // clean temp file
        Process::path($projectPath)->run('docker compose exec '.$container.' bash -c "rm '.$workdir.'/'.$tempPhpFile.'"');
        Storage::delete($tempPhpFile);

        return $result;
    }

    protected function executeDocker(
        string $container,
        string $workdir,
        string $tempPhpFile,
        string $command,
        string $projectPath,
        string $content,
    ): ProcessResult {
        $dockerCommand = 'docker exec ' . $container . ' bash -c "cd ' . $workdir . ' && ' . $command . '"';
        Storage::write($tempPhpFile, $this->phpOpenTag . $content);
        Process::path($projectPath)->run('docker cp '.storage_path('app/' . $tempPhpFile) . ' ' . $container . ':' . $workdir . '/' . $tempPhpFile);
        $result = Process::path($projectPath)->run($dockerCommand);

        // clean temp file
        Process::path($projectPath)->run('docker exec ' . $container . ' bash -c "rm ' . $workdir . '/' . $tempPhpFile . '"');
        Storage::delete($tempPhpFile);

        return $result;
    }

    protected function loadCustomEnv(string $filePath): array
    {
        $envArray = [];

        // Read the file into an array of lines
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            throw new Exception('Error reading the project\'s env file');
        }

        foreach ($lines as $line) {
            // Skip comments
            if (str_starts_with($line, '#')) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            // Handle quoted values
            if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
                $value = substr($value, 1, -1);
            }

            $envArray[$name] = $value;
        }

        return $envArray;
    }

    protected function isLaravelFolder(string $folder): bool
    {
        return file_exists($folder.DIRECTORY_SEPARATOR.'artisan') &&
            is_dir($folder.DIRECTORY_SEPARATOR.'app') &&
            is_dir($folder.DIRECTORY_SEPARATOR.'bootstrap') &&
            is_dir($folder.DIRECTORY_SEPARATOR.'config');
    }

    public function openFolderDialog()
    {
        $newPath = Dialog::new()
            ->folders()
            ->open();

        if ($newPath === null) {
            return;
        }

        $this->path = $newPath;

        $shell = Shell::find($this->shellId);
        $shell->path = $newPath;
        $shell->save();
    }

    public function saveCode(string $code)
    {
        $shell = Shell::find($this->shellId);
        $shell->code = $code;
        $shell->save();
    }

    public function updated($name, $value): void
    {
        // update general meta
        if (in_array($name, [
            'settingsOpen',
            'wordWrap',
            'isShowingHidden',
        ])) {
            $shell = Shell::find($this->shellId);
            match ($name) {
                'settingsOpen' => $shell->setMeta(ShellMeta::SETTINGS_OPEN->value, $value),
                'wordWrap' => $shell->setMeta(ShellMeta::WORD_WRAP->value, $value),
                'isShowingHidden' => $shell->setMeta(ShellMeta::SHOWING_HIDDEN->value, $value),
            };

            if (in_array($name, ['wordWrap', 'isShowingHidden'])) {
                redirect()->route('shells.show', ['shell' => $this->shellId]);
            }

            return;
        }

        // update docker or remote meta
        if (in_array($name, [
            // docker settings
            'isDockerContext',
            'dockerContainer',
            'dockerWorkdir',
            'dockerType',
            // remote settings
            'isRemoteContext',
            'remoteHost',
            'remotePort',
            'remoteUser',
            'remotePassword',
            'remotePasswordType',
            // editor settings
            'textSize',
        ])) {
            $shell = Shell::find($this->shellId);
            match ($name) {
                // docker settings
                'dockerContainer' => $shell->setMeta(ShellMeta::DOCKER_CONTAINER->value, $value),
                'dockerWorkdir' => $shell->setMeta(ShellMeta::DOCKER_WORKDIR->value, $value),
                'dockerType' => $shell->setMeta(ShellMeta::DOCKER_TYPE->value, $value),
                // remote settings
                'remoteHost' => $shell->setMeta(ShellMeta::REMOTE_HOST->value, $value),
                'remotePort' => $shell->setMeta(ShellMeta::REMOTE_PORT->value, $value),
                'remoteUser' => $shell->setMeta(ShellMeta::REMOTE_USER->value, $value),
                'remotePassword' => $shell->setMeta(ShellMeta::REMOTE_PASSWORD->value, $value),
                'remotePasswordType' => $shell->setMeta(ShellMeta::REMOTE_PASSWORD_TYPE->value, $value),
                'textSize' => $shell->setMeta(ShellMeta::TEXT_SIZE->value, $value),
            };

            if (in_array($name, ['textSize'])) {
                redirect()->route('shells.show', ['shell' => $this->shellId]);
            }

            return;
        }

        if (in_array($name, ['title', 'path', 'php_binary', 'code'])) {
            $shell = Shell::find($this->shellId);
            match ($name) {
                'title' => $shell->title = $value,
                'path' => $shell->path = $value,
                'php_binary' => $shell->php_binary = $value,
                'code' => $shell->code = $value,
            };
            $shell->save();
        }
    }

    public function toggleContext(string $meta): void
    {
        /** @var Shell $shell */
        $shell = Shell::find($this->shellId);

        if ($meta === ShellMeta::IS_REMOTE_CONTEXT->value) {
            $this->isRemoteContext = ! $this->isRemoteContext;
            $this->isDockerContext = false;
            $shell->setManyMeta([
                ShellMeta::IS_REMOTE_CONTEXT->value => $this->isRemoteContext,
                ShellMeta::IS_DOCKER_CONTEXT->value => false,
            ]);
        } elseif ($meta === ShellMeta::IS_DOCKER_CONTEXT->value) {
            $this->isDockerContext = ! $this->isDockerContext;
            $this->isRemoteContext = false;
            $shell->setManyMeta([
                ShellMeta::IS_DOCKER_CONTEXT->value => $this->isDockerContext,
                ShellMeta::IS_REMOTE_CONTEXT->value => false,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.tinker-editor');
    }
}
