<?php

namespace App\Models;

use App\Support\EdulawSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Author extends Model
{
    use HasFactory;

    protected $attributes = [
        'show_in_contributor_section' => false,
    ];

    public const PROFILE_TYPES = [
        'director' => 'Director',
        'manager' => 'Manager',
        'team' => 'Contributor',
    ];

    public const ORGANIZATION_GROUPS = [
        'research_team' => 'Research Team',
        'internship_member' => 'Internship Member',
        'writer' => 'Writer',
        'speaker_moderator' => 'Speaker and Moderator',
    ];

    public const LEGACY_PROFILE_TYPE_MAP = [
        'internal_author' => 'team',
        'external_author' => 'team',
        'speaker' => 'team',
        'moderator' => 'team',
        'contributor' => 'team',
        'founder' => 'director',
        'co_founder' => 'director',
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
        'joined_at',
        'profile_type',
        'organization_group',
        'sort_order',
        'social_links',
        'seo_title',
        'meta_description',
        'is_active',
        'show_in_organization',
        'show_in_contributor_section',
    ];

    protected $casts = [
        'social_links' => 'array',
        'is_active' => 'boolean',
        'show_in_organization' => 'boolean',
        'show_in_contributor_section' => 'boolean',
        'sort_order' => 'integer',
        'joined_at' => 'date',
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

    public function getOrganizationGroupLabelAttribute(): ?string
    {
        return self::organizationGroupLabel($this->organization_group);
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

    public static function canonicalOrganizationGroup(?string $group): ?string
    {
        $group = Str::of((string) $group)
            ->lower()
            ->squish()
            ->replace(['-', ' ', '&'], '_')
            ->replaceMatches('/_+/', '_')
            ->trim('_')
            ->toString();

        if ($group === '') {
            return null;
        }

        if (array_key_exists($group, self::ORGANIZATION_GROUPS)) {
            return $group;
        }

        return match ($group) {
            'research', 'researcher', 'riset', 'peneliti' => 'research_team',
            'intern', 'internship', 'magang' => 'internship_member',
            'penulis' => 'writer',
            'speaker', 'moderator', 'speaker_and_moderator' => 'speaker_moderator',
            default => null,
        };
    }

    public static function organizationGroupLabel(?string $group): ?string
    {
        $group = self::canonicalOrganizationGroup($group);

        return $group ? self::ORGANIZATION_GROUPS[$group] : null;
    }

    public static function inferOrganizationGroup(?string $position, ?string $name = null): string
    {
        $searchText = Str::lower(collect([$name, $position])->filter()->join(' '));
        $nameKey = Str::of((string) $name)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/i', ' ')
            ->squish()
            ->toString();
        $knownResearchMembers = [
            'siti mahmuda',
            'siti mahmudha',
            'annisa zahra nur umar',
            'anisa zahra nur umar',
            'naufal rizqiyanto',
            'lalu rizqi ramdani alfaen',
            'fadila sharfina',
            'laila andayani',
            'rahmatika monati',
            'amirudin nur wahid',
            'mely noviyanti',
            'putri yuliani',
            'fadlah nur',
        ];

        if (in_array($nameKey, $knownResearchMembers, true)
            || Str::contains($searchText, ['research', 'riset', 'peneliti'])) {
            return 'research_team';
        }

        if (Str::contains($searchText, ['internship', 'intern', 'magang'])) {
            return 'internship_member';
        }

        if (Str::contains($searchText, ['writer', 'penulis'])) {
            return 'writer';
        }

        if (Str::contains($searchText, ['speaker', 'moderator', 'narasumber', 'pembicara'])) {
            return 'speaker_moderator';
        }

        return 'internship_member';
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return EdulawSite::assetUrl($this->attributes['photo'] ?? null);
    }

    public function getInitialsAttribute(): string
    {
        $initials = Str::of($this->name)
            ->squish()
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'EP';
    }

    public function scopeVisibleInContributorSection(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('show_in_contributor_section', true);
    }

    public function scopePublicProfile(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->where('slug', '!=', 'super-admin')
            ->whereRaw('LOWER(TRIM(name)) NOT IN (?, ?)', ['super admin', 'redaksi edulaw'])
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('position')
                    ->orWhereRaw('LOWER(TRIM(position)) NOT IN (?, ?, ?)', ['admin', 'superadmin', 'user']);
            });
    }

    public function scopeWithPublicContribution(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query
                ->whereHas('insights', fn (Builder $query): Builder => $query->published())
                ->orWhereHas('publications', fn (Builder $query): Builder => $query->published());
        });
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
