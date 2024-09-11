<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

class ReplSettings extends Component
{
    #[Layout('layouts.alt-app')]
    public function render()
    {
        return view('livewire.repl-settings', [
            'header' => 'Custom REPL Settings',
        ]);
    }
}
