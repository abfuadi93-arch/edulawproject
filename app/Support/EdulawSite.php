<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

class EdulawSite
{
    public static function settings(): array
    {
        return collect(config('edulaw', []))
            ->only(['site', 'contact', 'social'])
            ->flatMap(fn (array $values, string $group): array => collect($values)
                ->mapWithKeys(fn (mixed $value, string $key): array => ["{$group}.{$key}" => $value])
                ->all())
            ->all();
    }

    public static function value(string $key, mixed $default = null): mixed
    {
        return self::settings()[$key] ?? $default;
    }

    public static function assetUrl(?string $value, ?string $default = null): ?string
    {
        $value = filled($value) ? trim($value) : $default;

        if (! filled($value)) {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        $path = ltrim($value, '/');

        if (Str::startsWith($path, ['images/', 'build/', 'favicon']) && file_exists(public_path($path))) {
            return asset($path);
        }

        return edulaw_file_url($value);
    }

    public static function resolveUrl(?string $value, ?string $default = null): ?string
    {
        $value = filled($value) ? trim($value) : $default;

        if (! filled($value)) {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://', 'mailto:', 'tel:', '#'])) {
            return $value;
        }

        return url(Str::startsWith($value, '/') ? $value : '/'.$value);
    }

    public static function block(string $area): ?Fluent
    {
        $blocks = self::blocks($area);

        return $blocks->first();
    }

    public static function blocks(string $area): Collection
    {
        $areaBlocks = config('edulaw.content_blocks', [])[$area] ?? [];

        if ($areaBlocks === []) {
            return collect();
        }

        if (! array_is_list($areaBlocks)) {
            $areaBlocks = [$areaBlocks];
        }

        return collect($areaBlocks)
            ->filter(fn (array $block): bool => $block !== [])
            ->map(fn (array $block): Fluent => self::makeBlock($block))
            ->values();
    }

    private static function makeBlock(array $block): Fluent
    {
        $block['meta'] = $block['meta'] ?? [];
        $block['image_url'] = self::assetUrl($block['image'] ?? null);
        $block['resolved_url'] = self::resolveUrl($block['url'] ?? null);

        return new Fluent($block);
    }
}
