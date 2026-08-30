<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class Program extends Model
{
    use HasFactory;

    protected static array $schemaColumnCache = [];

    protected $fillable = [
        'program_category_id',
        'type',
        'name',
        'short_title',
        'subtitle',
        'duration',
        'slug',
        'short_description',
        'description',
        'learning_points',
        'orientation',
        'method',
        'output',
        'notes',
        'image',
        'hero_image',
        'format',
        'level',
        'audience',
        'event_date',
        'end_date',
        'speakers',
        'moderator_name',
        'moderator_affiliation',
        'registration_link',
        'youtube_url',
        'material_link',
        'primary_button_text',
        'primary_button_url',
        'secondary_button_text',
        'secondary_button_url',
        'location',
        'price_type',
        'ticket_price',
        'certificate_available',
        'status',
        'publication_status',
        'featured',
        'show_on_homepage',
        'sort_order',
        'seo_title',
        'seo_description',
        'og_image',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'learning_points' => 'array',
        'speakers' => 'array',
        'event_date' => 'datetime',
        'end_date' => 'datetime',
        'ticket_price' => 'decimal:2',
        'certificate_available' => 'boolean',
        'featured' => 'boolean',
        'show_on_homepage' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Program $program): void {
            $storedStatus = $program->getAttributes()['status']
                ?? $program->getRawOriginal('status');

            $program->setAttribute('status', static::statusFromDates(
                $program->event_date,
                $program->end_date,
                $storedStatus,
            ));
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProgramCategory::class, 'program_category_id');
    }

    public function categoryRelation(): BelongsTo
    {
        return $this->category();
    }

    public function programCategory(): BelongsTo
    {
        return $this->category();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeVisible(Builder $query): Builder
    {
        $query->where(function (Builder $query): void {
            $query
                ->whereNotNull('event_date')
                ->orWhere(function (Builder $query): void {
                    $query
                        ->whereNull('event_date')
                        ->whereIn('status', ['upcoming', 'ongoing', 'completed', 'portfolio', 'archived']);
                });
        });

        if (static::hasTableColumn('publication_status')) {
            $query->where('publication_status', 'published');
        }

        return $query;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query
                ->upcoming()
                ->orWhere(fn (Builder $query): Builder => $query->ongoing());
        });
    }

    public function scopeArchived(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where(function (Builder $query) use ($today): void {
            $query
                ->where(function (Builder $query) use ($today): void {
                    $query
                        ->whereNotNull('event_date')
                        ->where(function (Builder $query) use ($today): void {
                            $query
                                ->whereDate('end_date', '<', $today)
                                ->orWhere(function (Builder $query) use ($today): void {
                                    $query
                                        ->whereNull('end_date')
                                        ->whereDate('event_date', '<', $today);
                                });
                        });
                })
                ->orWhere(function (Builder $query): void {
                    $query
                        ->whereNull('event_date')
                        ->whereIn('status', ['completed', 'portfolio', 'archived']);
                });
        });
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where(function (Builder $query) use ($today): void {
            $query
                ->whereDate('event_date', '>', $today)
                ->orWhere(function (Builder $query): void {
                    $query
                        ->whereNull('event_date')
                        ->where('status', 'upcoming');
                });
        });
    }

    public function scopeOngoing(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where(function (Builder $query) use ($today): void {
            $query
                ->where(function (Builder $query) use ($today): void {
                    $query
                        ->whereNotNull('event_date')
                        ->whereDate('event_date', '<=', $today)
                        ->where(function (Builder $query) use ($today): void {
                            $query
                                ->whereDate('end_date', '>=', $today)
                                ->orWhere(function (Builder $query) use ($today): void {
                                    $query
                                        ->whereNull('end_date')
                                        ->whereDate('event_date', '>=', $today);
                                });
                        });
                })
                ->orWhere(function (Builder $query): void {
                    $query
                        ->whereNull('event_date')
                        ->where('status', 'ongoing');
                });
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->visible()->active();
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderByDesc('event_date')
            ->latest();
    }

    /*
    |--------------------------------------------------------------------------
    | Display Accessors
    |--------------------------------------------------------------------------
    */

    public function getDisplayTitleAttribute(): string
    {
        return $this->attributes['short_title']
            ?? $this->attributes['name']
            ?? 'Program Edulaw';
    }

    public function getDisplayCategoryAttribute(): string
    {
        return $this->category?->name ?: 'Program';
    }

    public function getDisplayStatusAttribute(): string
    {
        return match ($this->status) {
            'upcoming' => 'Segera Dibuka',
            'ongoing' => 'Berjalan',
            'completed', 'portfolio', 'archived' => 'Arsip',
            default => ucfirst((string) ($this->attributes['status'] ?? 'Program')),
        };
    }

    public function getDisplayFormatAttribute(): ?string
    {
        return ! empty($this->attributes['format'])
            ? Str::headline($this->attributes['format'])
            : null;
    }

    public function getDisplayDescriptionAttribute(): ?string
    {
        if (! empty($this->attributes['short_description'])) {
            return $this->attributes['short_description'];
        }

        return ! empty($this->attributes['description'])
            ? Str::limit(strip_tags($this->attributes['description']), 180)
            : null;
    }

    public function getDisplayLevelAttribute(): ?string
    {
        return ! empty($this->attributes['level'])
            ? Str::headline($this->attributes['level'])
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Compatibility Accessors for Older Blade Files
    |--------------------------------------------------------------------------
    */

    public function getTitleAttribute(): string
    {
        return $this->display_title;
    }

    public function getExcerptAttribute(): ?string
    {
        return $this->display_description;
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->attributes['description']
            ?? $this->display_description;
    }

    public function getTargetAudiencesAttribute(): array
    {
        return collect(explode(',', (string) ($this->attributes['audience'] ?? '')))
            ->map(fn ($audience) => trim($audience))
            ->filter()
            ->values()
            ->all();
    }

    public function getOrganizerAttribute(): string
    {
        return 'Edulaw Project';
    }

    public function getLanguageAttribute(): string
    {
        return 'Indonesia';
    }

    public function getContactEmailAttribute(): ?string
    {
        return null;
    }

    public function getStartedAtAttribute()
    {
        return $this->event_date;
    }

    public function getRegistrationUrlAttribute(): ?string
    {
        return $this->attributes['registration_link'] ?? null;
    }

    public function getRegistrationPriceAttribute(): ?string
    {
        if ($this->ticket_price !== null) {
            return is_numeric($this->ticket_price) && (float) $this->ticket_price >= 0
                ? $this->ticket_price
                : null;
        }

        // Only explicit free labels are safe to interpret as a zero ticket price.
        return in_array(Str::lower(trim((string) $this->price_type)), ['free', 'gratis'], true)
            ? '0.00'
            : null;
    }

    public function getDisplayPriceAttribute(): ?string
    {
        $price = $this->registration_price;

        if ($price === null) {
            return $this->price_type;
        }

        return (float) $price === 0.0
            ? 'Gratis'
            : 'Rp '.number_format((float) $price, 2, ',', '.');
    }

    public function getIsFeaturedAttribute(): bool
    {
        return (bool) ($this->attributes['featured'] ?? false);
    }

    public function getIsPublishedAttribute(): bool
    {
        $publicationStatus = $this->attributes['publication_status'] ?? null;

        return $publicationStatus === 'published'
            && in_array($this->status, ['upcoming', 'ongoing', 'archived'], true);
    }

    public function getIsArchivedAttribute(): bool
    {
        return $this->status === 'archived';
    }

    public function getStatusAttribute(?string $storedStatus): string
    {
        return static::statusFromDates(
            $this->attributes['event_date'] ?? null,
            $this->attributes['end_date'] ?? null,
            $storedStatus,
        );
    }

    public static function statusFromDates(
        CarbonInterface|string|null $eventDate,
        CarbonInterface|string|null $endDate = null,
        ?string $fallback = null,
        CarbonInterface|string|null $referenceDate = null,
    ): string {
        if (blank($eventDate)) {
            return match ($fallback) {
                'ongoing' => 'ongoing',
                'completed', 'portfolio', 'archived' => 'archived',
                default => 'upcoming',
            };
        }

        $today = $referenceDate
            ? Carbon::parse($referenceDate)->startOfDay()
            : now()->startOfDay();
        $start = Carbon::parse($eventDate)->startOfDay();
        $end = filled($endDate)
            ? Carbon::parse($endDate)->startOfDay()
            : $start->copy();

        if ($start->isAfter($today)) {
            return 'upcoming';
        }

        return $end->isBefore($today) ? 'archived' : 'ongoing';
    }

    public function getImageUrlAttribute(): ?string
    {
        return edulaw_file_url($this->attributes['image'] ?? null);
    }

    public function getHeroImageUrlAttribute(): ?string
    {
        return edulaw_file_url($this->attributes['hero_image'] ?? null)
            ?: $this->image_url;
    }

    protected static function hasTableColumn(string $column): bool
    {
        $instance = new static;
        $key = $instance->getTable().'.'.$column;

        if (array_key_exists($key, static::$schemaColumnCache)) {
            return static::$schemaColumnCache[$key];
        }

        try {
            return static::$schemaColumnCache[$key] = Schema::hasColumn($instance->getTable(), $column);
        } catch (Throwable) {
            return static::$schemaColumnCache[$key] = false;
        }
    }
}
