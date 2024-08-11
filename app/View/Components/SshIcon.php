<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SshIcon extends Component
{
    public function __construct(
        public string $width = 'w-6',
        public string $height = 'h-6',
        public string $color = '#000000',
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.ssh-icon');
    }
}
