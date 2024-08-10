<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class WrapIcon extends Component
{
    public function __construct(
        public string $width = 'w-5',
        public string $height = 'h-5',
        public string $color = '#000000',
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.wrap-icon');
    }
}
