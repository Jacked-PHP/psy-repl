<?php

namespace App\Http\Controllers;

use App\Models\Shell;
use Illuminate\Http\Request;
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
}
