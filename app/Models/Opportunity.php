<?php

namespace App\Models;

use App\Support\EdulawSite;
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
        'excerpt',
        'description',
        'poster',
        'deadline',
        'application_link',
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
        return EdulawSite::assetUrl($this->attributes['poster'] ?? null);
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
