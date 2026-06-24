<?php

namespace App\Models;

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
        'video' => 'Video',
        'podcast' => 'Podcast',
        'documentation' => 'Dokumentasi',
        'shorts' => 'Shorts',
        'webinar' => 'Webinar',
        'gallery' => 'Galeri',
        'poster' => 'Poster',
    ];

    public const TYPE_ALIASES = [
        'reels' => 'shorts',
        'short' => 'shorts',
    ];

    public const PLATFORM_OPTIONS = [
        'youtube' => 'YouTube',
        'instagram' => 'Instagram',
        'tiktok' => 'TikTok',
        'spotify' => 'Spotify',
        'website' => 'Website',
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
        'published_at' => 'date',
        'featured' => 'boolean',
        'photo_count' => 'integer',
    ];

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

        return collect([$normalizedType])
            ->merge(
                collect(self::TYPE_ALIASES)
                    ->filter(fn (string $alias): bool => $alias === $normalizedType)
                    ->keys()
            )
            ->unique()
            ->values()
            ->all();
    }

    public function setTypeAttribute(?string $value): void
    {
        $this->attributes['type'] = self::normalizeType($value) ?: 'video';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereIn('status', ['published', 'terbit']);
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
        return SiteSetting::assetUrl($this->attributes['thumbnail'] ?? null);
    }

    public function getDisplayTypeAttribute(): string
    {
        $type = self::normalizeType($this->attributes['type'] ?? null);

        return self::TYPE_OPTIONS[$type] ?? Str::headline((string) ($type ?: 'Multimedia'));
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
            return number_format((int) $this->attributes['photo_count']) . ' foto';
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
