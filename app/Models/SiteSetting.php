<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'key',
        'label',
        'type',
        'value',
        'help_text',
        'sort_order',
        'is_public',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_public' => 'boolean',
    ];

    public static function publicValues(): array
    {
        try {
            if (! Schema::hasTable('site_settings')) {
                return [];
            }

            return static::query()
                ->where('is_public', true)
                ->orderBy('group')
                ->orderBy('sort_order')
                ->pluck('value', 'key')
                ->all();
        } catch (Throwable) {
            return [];
        }
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

    public static function assetUrl(?string $value, ?string $default = null): ?string
    {
        $value = filled($value) ? trim($value) : $default;

        if (! filled($value)) {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        if (Str::startsWith($value, '/')) {
            return url($value);
        }

        if (Str::startsWith($value, 'storage/')) {
            return asset($value);
        }

        if (Str::startsWith($value, 'public/')) {
            return Storage::disk('public')->url(Str::after($value, 'public/'));
        }

        if (Str::startsWith($value, ['images/', 'build/', 'favicon'])) {
            return asset($value);
        }

        return Storage::disk('public')->url($value);
    }
}
