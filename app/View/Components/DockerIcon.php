<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DockerIcon extends Component
{
    public function __construct(
        public string $width = 'w-5',
        public string $height = 'h-5',
        public string $color = '#2396ED',
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.docker-icon');
    }
}
