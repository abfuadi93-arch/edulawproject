<?php

namespace App\Models;

use App\Support\EdulawSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Multimedia extends Model
{
    use HasFactory;

    protected $table = 'multimedia';

    public const TYPE_OPTIONS = [
        'video' => 'YouTube Video',
        'shorts' => 'Shorts',
        'reels' => 'Reels',
        'gallery' => 'Google Photos Album',
        'documentation' => 'Dokumentasi Foto',
        'podcast' => 'Podcast',
        'poster' => 'Poster',
        'webinar' => 'Webinar',
    ];

    public const TYPE_ALIASES = [
        'short' => 'shorts',
    ];

    public const PLATFORM_OPTIONS = [
        'youtube' => 'YouTube',
        'instagram' => 'Instagram',
        'google_photos' => 'Google Photos',
        'tiktok' => 'TikTok',
        'spotify' => 'Spotify',
        'website' => 'Website / Google Photos',
        'gallery' => 'Galeri',
        'other' => 'Lainnya',
    ];

    public const SERIAL_OPTIONS = [
        'diksi' => 'Diksi',
        'gali_putusan' => 'Gali Putusan',
        'hukum_dalam_60_detik' => 'Hukum dalam 60 Detik',
        'edulaw_talks' => 'Edulaw Talks',
        'kelas_konstitusi' => 'Kelas Konstitusi',
    ];

    public const TOPIC_OPTIONS = [
        'konstitusi' => 'Konstitusi',
        'mahkamah_konstitusi' => 'Mahkamah Konstitusi',
        'pemilu_dan_demokrasi' => 'Pemilu dan Demokrasi',
        'hak_konstitusional' => 'Hak Konstitusional',
        'hukum_digital' => 'Hukum Digital',
        'kebijakan_publik' => 'Kebijakan Publik',
    ];

    public const DISPLAY_SECTION_OPTIONS = [
        'latest' => 'Konten Terbaru',
        'short_video' => 'Video Singkat',
        'serial_edulaw' => 'Serial Edulaw',
        'topic_multimedia' => 'Topik Multimedia',
    ];

    public const PLAYABLE_TYPES = [
        'video',
        'podcast',
        'shorts',
        'reels',
        'webinar',
    ];

    public const GALLERY_TYPES = [
        'gallery',
        'documentation',
    ];

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
        'photo_count',
        'serial',
        'topic',
        'display_section',
        'published_at',
        'status',
        'featured',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'featured' => 'boolean',
        'photo_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Multimedia $multimedia): void {
            if (blank($multimedia->slug) || $multimedia->isDirty('title')) {
                $multimedia->slug = static::uniqueSlug($multimedia->title, $multimedia->getKey());
            }

            if ($multimedia->status === 'published' && blank($multimedia->published_at)) {
                $multimedia->published_at = now();
            }

            if ($multimedia->type !== 'video' || $multimedia->platform !== 'youtube') {
                $multimedia->featured = false;
            }
        });

        static::saved(function (Multimedia $multimedia): void {
            if ($multimedia->featured && $multimedia->type === 'video' && $multimedia->platform === 'youtube') {
                static::query()
                    ->whereKeyNot($multimedia->getKey())
                    ->where('featured', true)
                    ->update(['featured' => false]);
            }
        });
    }

    public static function uniqueSlug(string $title, int|string|null $ignoreId = null): string
    {
        $base = Str::limit(Str::slug($title) ?: 'multimedia', 240, '');
        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = Str::limit($base, 240 - strlen((string) $suffix), '').'-'.$suffix++;
        }

        return $slug;
    }

    public static function normalizeType(?string $type): ?string
    {
        if (blank($type)) {
            return null;
        }

        return self::TYPE_ALIASES[$type] ?? $type;
    }

    public static function typeVariants(?string $type): array
    {
        $normalizedType = self::normalizeType($type);

        if (blank($normalizedType)) {
            return [];
        }

        $variants = collect([$normalizedType])
            ->merge(
                collect(self::TYPE_ALIASES)
                    ->filter(fn (string $alias): bool => $alias === $normalizedType)
                    ->keys()
            );

        if ($normalizedType === 'shorts') {
            $variants->push('reels');
        }

        if ($normalizedType === 'reels') {
            $variants->push('shorts');
        }

        return $variants->unique()->values()->all();
    }

    public function setTypeAttribute(?string $value): void
    {
        $this->attributes['type'] = self::normalizeType($value) ?: 'video';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function scopeYoutubeVideos(Builder $query): Builder
    {
        return $query->where('type', 'video')->where('platform', 'youtube');
    }

    public function scopeShortsReels(Builder $query): Builder
    {
        return $query
            ->whereIn('type', ['shorts', 'reels'])
            ->whereIn('platform', ['instagram', 'youtube']);
    }

    public function scopePhotoAlbums(Builder $query): Builder
    {
        return $query
            ->whereIn('type', ['gallery', 'documentation'])
            ->whereIn('platform', ['google_photos', 'website', 'other']);
    }

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
        $thumbnail = EdulawSite::assetUrl($this->attributes['thumbnail'] ?? null);

        if ($thumbnail || $this->platform !== 'youtube') {
            return $thumbnail;
        }

        $url = (string) ($this->attributes['media_url'] ?? '');

        if (preg_match('~youtu\.be/([A-Za-z0-9_-]{6,})~', $url, $matches)
            || preg_match('~youtube\.com/(?:shorts/|embed/)([A-Za-z0-9_-]{6,})~', $url, $matches)
            || preg_match('~[?&]v=([A-Za-z0-9_-]{6,})~', $url, $matches)) {
            return "https://i.ytimg.com/vi/{$matches[1]}/hqdefault.jpg";
        }

        return null;
    }

    public function getDisplayTypeAttribute(): string
    {
        $type = self::normalizeType($this->attributes['type'] ?? null);

        return match ($type) {
            'video' => 'YouTube Video',
            'shorts', 'reels' => 'Shorts / Reels',
            'gallery', 'documentation' => 'Photo Album',
            default => 'Lainnya',
        };
    }

    public function getDisplayPlatformAttribute(): string
    {
        $platform = $this->attributes['platform'] ?? null;

        return self::PLATFORM_OPTIONS[$platform] ?? Str::headline((string) ($platform ?: 'Website'));
    }

    public function getDisplaySerialAttribute(): ?string
    {
        $serial = $this->attributes['serial'] ?? null;

        if (blank($serial)) {
            return null;
        }

        return self::SERIAL_OPTIONS[$serial] ?? Str::headline((string) $serial);
    }

    public function getDisplayTopicAttribute(): ?string
    {
        $topic = $this->attributes['topic'] ?? null;

        if (blank($topic)) {
            return null;
        }

        return self::TOPIC_OPTIONS[$topic] ?? Str::headline((string) $topic);
    }

    public function getDisplaySectionLabelAttribute(): ?string
    {
        $section = $this->attributes['display_section'] ?? null;

        if (blank($section)) {
            return null;
        }

        return self::DISPLAY_SECTION_OPTIONS[$section] ?? Str::headline((string) $section);
    }

    public function getDisplayMetaAttribute(): string
    {
        $type = self::normalizeType($this->attributes['type'] ?? null);

        if (in_array($type, self::GALLERY_TYPES, true) && filled($this->attributes['photo_count'] ?? null)) {
            return number_format((int) $this->attributes['photo_count']).' foto';
        }

        if ($type === 'poster') {
            return optional($this->published_at)->translatedFormat('d M Y') ?: 'Belum terjadwal';
        }

        if (filled($this->attributes['duration'] ?? null)) {
            return $this->attributes['duration'];
        }

        return optional($this->published_at)->translatedFormat('d M Y') ?: 'Belum terjadwal';
    }
}
