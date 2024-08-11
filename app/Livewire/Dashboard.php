<?php

namespace App\Livewire;

use App\Models\Shell;
use Livewire\Component;

class Dashboard extends Component
{

    public function deleteShell(int $shellId): bool
    {
        return Shell::find($shellId)->delete();
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'shells' => auth()->user()->shells ?? collect(),
        ]);
    }
}
