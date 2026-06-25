<?php

namespace App\Models;

use App\Support\EdulawSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ContentBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'area',
        'eyebrow',
        'title',
        'subtitle',
        'body',
        'image',
        'image_alt',
        'icon',
        'accent',
        'url',
        'url_label',
        'meta',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'meta' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeArea(Builder $query, string $area): Builder
    {
        return $query->where('area', $area);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public static function forArea(string $area): Collection
    {
        try {
            if (! Schema::hasTable('content_blocks')) {
                return new Collection;
            }

            return static::query()
                ->area($area)
                ->active()
                ->ordered()
                ->get();
        } catch (Throwable) {
            return new Collection;
        }
    }

    public static function firstForArea(string $area): ?self
    {
        return static::forArea($area)->first();
    }

    public function getImageUrlAttribute(): ?string
    {
        return EdulawSite::assetUrl($this->image);
    }

    public function getResolvedUrlAttribute(): ?string
    {
        return EdulawSite::resolveUrl($this->url);
    }
}
