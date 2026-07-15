<?php

namespace App\Models;

use App\Support\EdulawSite;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Author extends Model
{
    use HasFactory;

    public const PROFILE_TYPES = [
        'founder' => 'Founder',
        'co_founder' => 'Co-Founder',
        'manager' => 'Manager',
        'team' => 'Officer, Writer, & Designer',
    ];

    public const LEGACY_PROFILE_TYPE_MAP = [
        'internal_author' => 'team',
        'external_author' => 'team',
        'speaker' => 'team',
        'moderator' => 'team',
        'contributor' => 'team',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'title',
        'slug',
        'email',
        'bio',
        'interests',
        'photo',
        'institution',
        'position',
        'location',
        'profile_type',
        'sort_order',
        'social_links',
        'seo_title',
        'meta_description',
        'is_active',
        'show_in_organization',
    ];

    protected $casts = [
        'social_links' => 'array',
        'is_active' => 'boolean',
        'show_in_organization' => 'boolean',
        'sort_order' => 'integer',
    ];

    public static function uniqueSlugFor(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'profil';
        $slug = $baseSlug;
        $suffix = 2;

        while (static::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    public function getAffiliationLabelAttribute(): string
    {
        return collect([$this->position, $this->institution])
            ->filter()
            ->join(' / ') ?: '-';
    }

    public function getProfileTypeLabelAttribute(): ?string
    {
        $profileType = self::canonicalProfileType($this->profile_type);

        return $profileType ? self::PROFILE_TYPES[$profileType] : null;
    }

    public function getProfileRoleKeyAttribute(): ?string
    {
        return self::canonicalProfileType($this->profile_type);
    }

    public static function canonicalProfileType(?string $profileType): ?string
    {
        $profileType = Str::of((string) $profileType)
            ->lower()
            ->squish()
            ->replace(['-', ' '], '_')
            ->toString();

        if ($profileType === '') {
            return null;
        }

        if (array_key_exists($profileType, self::PROFILE_TYPES)) {
            return $profileType;
        }

        return self::LEGACY_PROFILE_TYPE_MAP[$profileType] ?? null;
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return EdulawSite::assetUrl($this->attributes['photo'] ?? null);
    }

    protected function interests(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): array {
                if (blank($value)) {
                    return [];
                }

                $decoded = json_decode($value, true);

                return collect(is_array($decoded) ? $decoded : preg_split('/[,;\r\n]+/', $value))
                    ->flatten()
                    ->map(fn ($interest): string => trim((string) $interest))
                    ->filter()
                    ->unique(fn (string $interest): string => Str::lower($interest))
                    ->values()
                    ->all();
            },
            set: function (array|string|null $value): ?string {
                $interests = collect(is_array($value) ? $value : preg_split('/[,;\r\n]+/', (string) $value))
                    ->flatten()
                    ->map(fn ($interest): string => trim((string) $interest))
                    ->filter()
                    ->unique(fn (string $interest): string => Str::lower($interest))
                    ->values();

                return $interests->isEmpty() ? null : $interests->toJson();
            }
        );
    }

    /**
     * Normalize both the legacy repeater shape and the current keyed JSON shape.
     *
     * @return array<string, string>
     */
    public function socialLinksMap(): array
    {
        $links = $this->social_links ?? [];

        if (! array_is_list($links)) {
            return collect($links)
                ->mapWithKeys(fn ($url, $platform): array => [Str::snake((string) $platform) => trim((string) $url)])
                ->filter()
                ->all();
        }

        return collect($links)
            ->filter(fn ($link): bool => is_array($link) && filled($link['platform'] ?? null) && filled($link['url'] ?? null))
            ->mapWithKeys(fn (array $link): array => [Str::snake((string) $link['platform']) => trim((string) $link['url'])])
            ->all();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function insights(): BelongsToMany
    {
        return $this->belongsToMany(Insight::class, 'author_insight')
            ->withPivot(['author_order', 'role'])
            ->withTimestamps();
    }

    public function publications(): BelongsToMany
    {
        return $this->belongsToMany(Publication::class, 'author_publication')
            ->withPivot(['author_order', 'role'])
            ->withTimestamps();
    }
}
