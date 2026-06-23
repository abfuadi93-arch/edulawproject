<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_category_id',
        'name',
        'slug',
        'short_description',
        'learning_points',
        'image',
        'format',
        'level',
        'audience',
        'event_date',
        'end_date',
        'speakers',
        'registration_link',
        'location',
        'price_type',
        'certificate_available',
        'status',
        'featured',
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
        'certificate_available' => 'boolean',
        'featured' => 'boolean',
        'sort_order' => 'integer',
    ];

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
        return $query->whereIn('status', ['upcoming', 'ongoing', 'archived']);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['upcoming', 'ongoing']);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', 'archived');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->active();
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
        return $this->attributes['name'] ?? 'Program Edulaw';
    }

    public function getDisplayCategoryAttribute(): string
    {
        return $this->category?->name ?: 'Program';
    }

    public function getDisplayStatusAttribute(): string
    {
        return match ($this->attributes['status'] ?? null) {
            'upcoming' => 'Segera Dibuka',
            'ongoing' => 'Berjalan',
            'archived' => 'Arsip',
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
        return $this->attributes['short_description'] ?? null;
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
        return $this->display_description;
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

    public function getIsFeaturedAttribute(): bool
    {
        return (bool) ($this->attributes['featured'] ?? false);
    }

    public function getIsPublishedAttribute(): bool
    {
        return in_array($this->attributes['status'] ?? null, ['upcoming', 'ongoing'], true);
    }

    public function getIsArchivedAttribute(): bool
    {
        return ($this->attributes['status'] ?? null) === 'archived';
    }

    public function getImageUrlAttribute(): ?string
    {
        if (blank($this->attributes['image'] ?? null)) {
            return null;
        }

        $path = trim($this->attributes['image']);

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Str::startsWith($path, ['/storage/', 'storage/'])) {
            return asset(ltrim($path, '/'));
        }

        if (Str::startsWith($path, ['/images/', 'images/'])) {
            return asset(ltrim($path, '/'));
        }

        if (Str::startsWith($path, '/')) {
            return asset(ltrim($path, '/'));
        }

        return Storage::disk('public')->url($path);
    }
}
