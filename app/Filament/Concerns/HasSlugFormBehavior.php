<?php

namespace App\Filament\Concerns;

use Closure;
use Illuminate\Support\Str;

trait HasSlugFormBehavior
{
    protected static function syncSlugFrom(string $slugField = 'slug'): Closure
    {
        return function ($get, $set, ?string $old, ?string $state) use ($slugField): void {
            $currentSlug = (string) ($get($slugField) ?? '');
            $oldSlug = Str::slug((string) $old);

            if (filled($currentSlug) && ($currentSlug !== $oldSlug)) {
                return;
            }

            $set($slugField, Str::slug((string) $state));
        };
    }
}
