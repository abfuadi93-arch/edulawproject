<?php

namespace App\Models;

use App\Support\EdulawSite;
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
        'slug',
        'email',
        'bio',
        'interests',
        'photo',
        'institution',
        'position',
        'profile_type',
        'sort_order',
        'social_links',
        'is_active',
    ];

    protected $casts = [
        'social_links' => 'array',
        'is_active' => 'boolean',
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
