<?php

namespace App\Livewire;

use App\Models\Shell;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Dashboard extends Component
{
    public function deleteShell(int $shellId): bool
    {
        return Shell::find($shellId)->delete();
    }

    #[Layout('layouts.alt-app')]
    public function render()
    {
        return view('livewire.dashboard', [
            'shells' => auth()->user()->shells ?? collect(),
        ]);
    }
}
