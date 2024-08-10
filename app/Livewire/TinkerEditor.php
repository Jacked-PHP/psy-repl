<?php

namespace App\Livewire;

use App\Enums\ShellMeta;
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
    public ?int $shellId = null;
    public string $title = '';
    public string $php_binary = '';
    public ?string $path = null;
    public string $code = 'dd(App\Models\User::get());';
    public string $output = '';
    public bool $isDockerContext = false;
    public ?string $dockerContainer = null;
    public ?string $dockerWorkdir = null;
    public bool $settingsOpen = false;
    public bool $wordWrap = false;

    protected string $phpOpenTag = '<?php' . PHP_EOL;

    public function mount(Shell $shell)
    {
        /** @var User $user */
        $user = auth()->user();
        if (null === $user) {
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

        // docker meta
        $this->isDockerContext = $shell->getMeta(ShellMeta::IS_DOCKER_CONTEXT->value, false);
        $this->dockerWorkdir = $shell->getMeta(ShellMeta::DOCKER_WORKDIR->value, '');
        $this->dockerContainer = $shell->getMeta(ShellMeta::DOCKER_CONTAINER->value, '');
    }

    public function executeCode(string $content)
    {
        try {
            if ($this->isDockerContext) {
                $result = $this->executeCodeDocker($content);
            } else {
                $result = $this->executeCodeMetal($content);
            }

            $error = $result->errorOutput();
            $output = $result->output();
            if (!empty($error) && empty($output)) {
                return json_encode([
                    'error' => $error,
                    'output' => $result->output(),
                ]);
            }

            $shell = Shell::find($this->shellId);
            $shell->code = $content;
            $shell->output = $result->output();
            $shell->save();

            return $result->output();
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    protected function executeCodeMetal(string $content): ProcessResult
    {
        if (
            null === $this->php_binary
            || empty(Process::run($this->php_binary . ' -v')->output())
        ) {
            throw new Exception('PHP Binary not found - review your settings.');
        }

        $projectPath = $this->path;
        $tempPhpFile = Uuid::uuid4()->toString() . '.php';
        $command = $this->php_binary . ' artisan tinker --execute="include(\'' . storage_path('app/' . $tempPhpFile) . '\')"';

        if (!$this->isLaravelFolder($projectPath)) {
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
        $tempPhpFile = Uuid::uuid4()->toString() . '.php';
        $command = 'php artisan tinker --execute=\"include(\'' . $workdir . '/' . $tempPhpFile . '\')\"';
        $projectPath = $this->path;
        $result = Process::path($projectPath)->run('command -v docker compose');

        if (empty($result)) {
            throw new Exception('Docker not available in the system!');
        }

        // TODO: accept standalone docker as well instead of just docker compose
        // run procedures in container (using docker compose)
        $dockerCommand = 'docker compose exec ' . $container . ' bash -c "cd ' . $workdir . ' && ' . $command . '"';
        Storage::write($tempPhpFile, $this->phpOpenTag . $content);
        Process::path($projectPath)->run('docker compose cp ' . storage_path('app/' . $tempPhpFile) . ' ' . $container . ':' . $workdir . '/' . $tempPhpFile);
        $result = Process::path($projectPath)->run($dockerCommand);

        // clean temp file
        Process::path($projectPath)->run('docker compose exec php bash -c "rm '. $workdir . '/' . $tempPhpFile . '"');
        Storage::delete($tempPhpFile);

        return $result;
    }

    protected function loadCustomEnv(string $filePath): array
    {
        $envArray = [];

        // Read the file into an array of lines
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (false === $lines) {
            throw new Exception('Error reading the project\'s env file');
        }

        foreach ($lines as $line) {
            // Skip comments
            if (str_starts_with($line, '#')) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
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
        return file_exists($folder . DIRECTORY_SEPARATOR . 'artisan') &&
            is_dir($folder . DIRECTORY_SEPARATOR . 'app') &&
            is_dir($folder . DIRECTORY_SEPARATOR . 'bootstrap') &&
            is_dir($folder . DIRECTORY_SEPARATOR . 'config');
    }

    public function openFolderDialog()
    {
        $newPath = Dialog::new()
            ->folders()
            ->open();

        if (null === $newPath) {
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

    public function updated($name, $value)
    {
        // update general meta
        if (in_array($name, [
            'settingsOpen',
            'wordWrap',
        ])) {
            $shell = Shell::find($this->shellId);
            match ($name) {
                'settingsOpen' => $shell->setMeta(ShellMeta::SETTINGS_OPEN->value, $value),
                'wordWrap' => $shell->setMeta(ShellMeta::WORD_WRAP->value, $value),
            };

            if ($name === 'wordWrap') {
                return redirect()->route('shells.show', ['shell' => $this->shellId]);
            }
        }

        // update docker meta
        if (in_array($name, ['isDockerContext', 'dockerContainer', 'dockerWorkdir'])) {
            $shell = Shell::find($this->shellId);
            match ($name) {
                'isDockerContext' => $shell->setMeta(ShellMeta::IS_DOCKER_CONTEXT->value, $value),
                'dockerContainer' => $shell->setMeta(ShellMeta::DOCKER_CONTAINER->value, $value),
                'dockerWorkdir' => $shell->setMeta(ShellMeta::DOCKER_WORKDIR->value, $value),
            };
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

    public function render()
    {
        return view('livewire.tinker-editor');
    }
}
