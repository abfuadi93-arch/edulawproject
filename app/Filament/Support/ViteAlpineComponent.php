<?php

namespace App\Filament\Support;

use Filament\Support\Assets\AlpineComponent;

class ViteAlpineComponent extends AlpineComponent
{
    public function getSrc(): string
    {
        return (string) $this->getPath();
    }
}
