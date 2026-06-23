<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Multimedia extends Model
{
    use HasFactory;

    protected $table = 'multimedia';

    protected $fillable = [
        'title',
        'slug',
        'type',
        'description',
        'thumbnail',
        'media_url',
        'embed_url',
        'platform',
        'duration',
        'published_at',
        'status',
        'featured',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'published_at' => 'date',
        'featured' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return SiteSetting::assetUrl($this->attributes['thumbnail'] ?? null);
    }

    public function getDisplayTypeAttribute(): string
    {
        return match ($this->attributes['type'] ?? null) {
            'video' => 'Video',
            'podcast' => 'Podcast',
            'poster' => 'Poster',
            'gallery' => 'Galeri',
            'documentation' => 'Dokumentasi',
            'reels' => 'Reels',
            'shorts' => 'Shorts',
            'webinar' => 'Webinar',
            default => ucfirst((string) ($this->attributes['type'] ?? 'Multimedia')),
        };
    }

    public function getDisplayPlatformAttribute(): string
    {
        return match ($this->attributes['platform'] ?? null) {
            'youtube' => 'YouTube',
            'instagram' => 'Instagram',
            'tiktok' => 'TikTok',
            'spotify' => 'Spotify',
            'website' => 'Website',
            'other' => 'Lainnya',
            default => ucfirst((string) ($this->attributes['platform'] ?? 'Website')),
        };
    }
}
