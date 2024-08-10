<?php

namespace App\Livewire;

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
    public $shellId = null;
    public $title = '';
    public $php_binary = '';
    public $path = null;
    public $code = 'dd(App\Models\User::get());';
    public $output = '';
    public $isDockerContext = false;
    public $dockerContainer = null;
    public $dockerWorkdir = null;

    protected $phpOpenTag = '<?php' . PHP_EOL;

    public function mount(Shell $shell)
    {
        /** @var User $user */
        $user = auth()->user();
        if (null === $user) {
            throw new Exception('User not logged in');
        }

        $this->shellId = $shell->id;
        $this->title = $shell->title;
        $this->php_binary = $shell->php_binary;
        $this->code = $shell->code;
        $this->path = $shell->path;
        $this->isDockerContext = (bool) $shell->is_docker_context;
        $this->dockerWorkdir = $shell->docker_workdir;
        $this->dockerContainer = $shell->docker_container;
        $this->output = $shell->output;
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

    protected function getDockerContainerNameFromDockerCompose(): string
    {
        $container = 'php';
        $projectPath = '/home/savior/Code/TCPigeon/panel2';
        $result = Process::path($projectPath)->run("docker-compose ps -q " . $container . " | xargs docker inspect --format '{{ .Name }}' | sed 's/^\///'");

        $error = $result->errorOutput();
        if (!empty($error)) {
            throw new Exception('Failed to retrieve docker container name!');
        }

        return $result->output();
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

    public function updated($name, $value)
    {
        $shell = Shell::find($this->shellId);
        match ($name) {
            'title' => $shell->title = $value,
            'path' => $shell->path = $value,
            'php_binary' => $shell->php_binary = $value,
            'code' => $shell->code = $value,
            'isDockerContext' => $shell->is_docker_context = $value,
            'dockerContainer' => $shell->docker_container = $value,
            'dockerWorkdir' => $shell->docker_workdir = $value,
        };
        $shell->save();
    }

    public function render()
    {
        return view('livewire.tinker-editor');
    }
}
