<?php

namespace App\Models;

use App\Support\EdulawSite;
use App\Support\OpportunityLink;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Opportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'type',
        'organizer',
        'excerpt',
        'description',
        'poster',
        'posters',
        'deadline',
        'application_link',
        'additional_link_label',
        'additional_link_url',
        'second_link_label',
        'second_link_url',
        'format',
        'location',
        'eligibility',
        'benefits',
        'status',
        'featured',
        'seo_title',
        'seo_description',
        'og_image',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'deadline' => 'date',
        'posters' => 'array',
        'eligibility' => 'array',
        'benefits' => 'array',
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

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->open()
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('deadline')
                    ->orWhereDate('deadline', '>=', today());
            });
    }

    public function scopeWithExternalLink(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query
                ->where('application_link', 'like', 'https://%')
                ->orWhere('application_link', 'like', 'http://%');
        });
    }

    public function getPosterUrlAttribute(): ?string
    {
        $primaryPoster = $this->poster_paths[0] ?? null;

        return EdulawSite::assetUrl($primaryPoster);
    }

    public function getExternalUrlAttribute(): ?string
    {
        $url = trim((string) ($this->attributes['application_link'] ?? ''));

        return Str::startsWith($url, ['https://', 'http://']) ? $url : null;
    }

    public function getAdditionalUrlAttribute(): ?string
    {
        $url = trim((string) ($this->attributes['additional_link_url'] ?? ''));

        return OpportunityLink::isValid($url) ? $url : null;
    }

    public function getOptionalLinksAttribute(): array
    {
        return collect([
            ['label' => $this->additional_link_label, 'url' => $this->additional_link_url],
            ['label' => $this->second_link_label, 'url' => $this->second_link_url],
        ])
            ->map(fn (array $link): array => array_map(fn ($value): string => trim((string) $value), $link))
            ->filter(fn (array $link): bool => filled($link['label']) && OpportunityLink::isValid($link['url']))
            ->values()
            ->all();
    }

    public function getTargetAudienceAttribute(): ?string
    {
        return collect($this->eligibility ?? [])
            ->map(fn ($item) => is_array($item) ? ($item['item'] ?? $item['text'] ?? $item['value'] ?? null) : $item)
            ->map(fn ($item): string => trim(strip_tags((string) $item)))
            ->filter()
            ->first();
    }

    /** @return list<string> */
    public function getPosterPathsAttribute(): array
    {
        $posters = $this->getAttribute('posters');

        if (is_string($posters)) {
            $posters = json_decode($posters, true);
        }

        $paths = collect(is_array($posters) ? $posters : [])
            ->filter(fn (mixed $path): bool => is_string($path) && filled(trim($path)))
            ->map(fn (string $path): string => trim($path))
            ->unique()
            ->values();

        if ($paths->isEmpty() && filled($this->attributes['poster'] ?? null)) {
            $paths->push(trim((string) $this->attributes['poster']));
        }

        return $paths->all();
    }

    /** @return list<string> */
    public function getPosterUrlsAttribute(): array
    {
        return collect($this->poster_paths)
            ->map(fn (string $path): ?string => EdulawSite::assetUrl($path))
            ->filter()
            ->values()
            ->all();
    }

    public function getDisplayTypeAttribute(): string
    {
        return match ($this->attributes['type'] ?? null) {
            'scholarship' => 'Beasiswa',
            'internship' => 'Magang',
            'volunteer' => 'Volunteer',
            'fellowship' => 'Fellowship',
            'call_for_paper' => 'Call for Papers',
            'competition' => 'Kompetisi',
            'career' => 'Karier',
            'open_collaboration' => 'Kolaborasi',
            default => ucfirst(str_replace('_', ' ', (string) ($this->attributes['type'] ?? 'Opportunity'))),
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->display_type;
    }

    public function getDisplayStatusAttribute(): string
    {
        return match ($this->attributes['status'] ?? null) {
            'open' => 'Masih Dibuka',
            'closed' => 'Sudah Ditutup',
            'archived' => 'Diarsipkan',
            default => Str::headline((string) ($this->attributes['status'] ?? 'Status')),
        };
    }

    public function getDisplayFormatAttribute(): string
    {
        $format = trim((string) ($this->attributes['format'] ?? ''));

        if ($format === '') {
            return 'Fleksibel';
        }

        $normalized = Str::lower($format);

        return match (true) {
            Str::contains($normalized, 'hybrid'),
            Str::containsAll($normalized, ['online', 'offline']) => 'Hybrid',
            Str::contains($normalized, 'online') => 'Online',
            Str::contains($normalized, 'offline') => 'Offline',
            default => Str::limit(Str::headline($format), 36),
        };
    }

    public function getDeadlineDisplayAttribute(): string
    {
        return $this->deadline?->locale('id')->translatedFormat('d F Y') ?? 'Tenggat fleksibel';
    }

    public function getDeadlineRelativeLabelAttribute(): string
    {
        if (! $this->deadline) {
            return 'Tanpa batas waktu';
        }

        $days = (int) today()->diffInDays($this->deadline->copy()->startOfDay(), false);

        return match (true) {
            $days < 0 => 'Deadline berakhir',
            $days === 0 => 'Hari ini',
            $days === 1 => 'Besok',
            $days < 7 => $days.' hari lagi',
            $days < 14 => '1 minggu lagi',
            $days < 21 => '2 minggu lagi',
            default => $days.' hari lagi',
        };
    }

    public function getIsOpenForApplicationsAttribute(): bool
    {
        return $this->status === 'open'
            && ($this->deadline === null || $this->deadline->isToday() || $this->deadline->isFuture());
    }
}
