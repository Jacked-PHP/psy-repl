<?php

namespace App\Http\Controllers;

use App\Enums\ShellMeta;
use App\Enums\SshPasswordType;
use App\Helpers\ShellHelper;
use App\Models\Shell;
use Exception;
use Illuminate\Http\Request;
use phpseclib3\Crypt\RSA\PrivateKey;
use phpseclib3\Net\SSH2;
use Ramsey\Uuid\Uuid;

class ShellController extends Controller
{
    public function index(Request $request, Shell $shell)
    {
        if (null === $shell->id) {
            $shell = Shell::create([
                'user_id' => auth()->id(),
                'title' => Uuid::uuid4()->toString(),
            ]);
        }

        return view('tinker.editor', [
            'shell' => $shell,
        ]);
    }

    public function executeRemoteCode(Request $request, Shell $shell)
    {
        $host  = $shell->getMeta(ShellMeta::REMOTE_HOST->value);
        $port  = $shell->getMeta(ShellMeta::REMOTE_PORT->value);
        $user  = $shell->getMeta(ShellMeta::REMOTE_USER->value);
        $password = $shell->getMeta(ShellMeta::REMOTE_PASSWORD->value);
        $password_type = $shell->getMeta(ShellMeta::REMOTE_PASSWORD_TYPE->value);
        $code = ShellHelper::preparePHPCodeForTinker($shell->code);

        $ssh = new SSH2($host, $port);

        if ($password_type === SshPasswordType::PRIVATE_KEY->value) {
            $password = PrivateKey::load($password);
        }

        if (! $ssh->login($user, $password)) {
            throw new Exception('Login on remote server failed!');
        }

        $command = 'cd ' . $shell->path . ' && ' . $code . ' | php artisan tinker';

        return response()->stream(function () use ($command, $ssh, $shell) {
            $finalOutput = '';
            $ssh->exec($command, function ($output) use (&$finalOutput) {
                $pieces = explode(PHP_EOL, $output);
                foreach ($pieces as $piece) {
                    if (empty($piece)) {
                        continue;
                    }

                    echo 'id: ' . uniqid() . PHP_EOL;
                    echo 'event: message' . PHP_EOL;
                    echo 'data: ' . $piece . PHP_EOL . PHP_EOL;
                    $finalOutput .= $piece . PHP_EOL;
                }
            });
            $ssh->disconnect();
            $shell->update(['output' => $finalOutput]);

        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }
}
