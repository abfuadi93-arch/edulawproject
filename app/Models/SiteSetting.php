<?php

namespace App\Models;

use App\Support\EdulawSite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return EdulawSite::settings();
    }

    public static function resolveUrl(?string $value, ?string $default = null): ?string
    {
        return EdulawSite::resolveUrl($value, $default);
    }

    public static function assetUrl(?string $value, ?string $default = null): ?string
    {
        return EdulawSite::assetUrl($value, $default);
    }
}
