<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ShowHiddenIcon extends Component
{
    public function __construct(
        public string $width = 'w-5',
        public string $height = 'h-5',
        public bool $open = false,
        public string $color = 'none',
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.show-hidden-icon');
    }
}
