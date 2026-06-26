<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (! function_exists('edulaw_file_url')) {
    function edulaw_file_url(?string $path, ?string $fallback = null): ?string
    {
        if (! $path) {
            return $fallback ? asset($fallback) : null;
        }

        $path = trim($path);

        if ($path === '') {
            return $fallback ? asset($fallback) : null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $path = ltrim($path, '/');

        $changed = true;

        while ($changed) {
            $changed = false;

            foreach (['public/', 'storage/'] as $prefix) {
                if (str_starts_with($path, $prefix)) {
                    $path = substr($path, strlen($prefix));
                    $changed = true;
                }
            }
        }

        return Storage::disk('public')->url($path);
    }
}

if (! function_exists('edulaw_file_exists')) {
    function edulaw_file_exists(?string $path): bool
    {
        if (! $path) {
            return false;
        }

        $path = trim($path);

        if ($path === '') {
            return false;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return true;
        }

        $path = ltrim($path, '/');

        $changed = true;

        while ($changed) {
            $changed = false;

            foreach (['public/', 'storage/'] as $prefix) {
                if (str_starts_with($path, $prefix)) {
                    $path = substr($path, strlen($prefix));
                    $changed = true;
                }
            }
        }

        return Storage::disk('public')->exists($path);
    }
}
