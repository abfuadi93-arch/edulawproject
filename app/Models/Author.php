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
        'internal_author' => 'Penulis Internal',
        'external_author' => 'Penulis Eksternal',
        'speaker' => 'Narasumber',
        'moderator' => 'Moderator',
        'founder' => 'Founder',
        'co_founder' => 'Co-Founder',
        'team' => 'Tim Edulaw',
        'contributor' => 'Kontributor',
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
        'social_links',
        'is_active',
    ];

    protected $casts = [
        'social_links' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function (Author $author): void {
            $author->syncLinkedUserAvatar();
        });
    }

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
        return self::PROFILE_TYPES[$this->profile_type] ?? null;
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return EdulawSite::assetUrl($this->attributes['photo'] ?? null);
    }

    public function syncLinkedUserAvatar(): void
    {
        if (! $this->user_id) {
            return;
        }

        $user = $this->user()->first();

        if (! $user) {
            return;
        }

        if (filled($this->photo) && $user->avatar !== $this->photo) {
            $user->forceFill(['avatar' => $this->photo])->saveQuietly();

            return;
        }

        if (blank($this->photo) && $this->wasChanged('photo') && $user->avatar === $this->getOriginal('photo')) {
            $user->forceFill(['avatar' => null])->saveQuietly();
        }
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
