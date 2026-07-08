<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'certificate_available' => 'boolean',
        'featured' => 'boolean',
        'show_on_homepage' => 'boolean',
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
        $query->whereIn('status', ['upcoming', 'ongoing', 'completed', 'portfolio', 'archived']);

        if (static::hasTableColumn('publication_status')) {
            $query->where('publication_status', 'published');
        }

        return $query;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['upcoming', 'ongoing']);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereIn('status', ['completed', 'portfolio', 'archived']);
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
        return match ($this->attributes['status'] ?? null) {
            'upcoming' => 'Segera Dibuka',
            'ongoing' => 'Berjalan',
            'completed' => 'Selesai',
            'portfolio' => 'Portofolio',
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

    public function getIsFeaturedAttribute(): bool
    {
        return (bool) ($this->attributes['featured'] ?? false);
    }

    public function getIsPublishedAttribute(): bool
    {
        $publicationStatus = $this->attributes['publication_status'] ?? null;

        return $publicationStatus === 'published'
            && in_array($this->attributes['status'] ?? null, ['upcoming', 'ongoing', 'completed', 'portfolio', 'archived'], true);
    }

    public function getIsArchivedAttribute(): bool
    {
        return in_array($this->attributes['status'] ?? null, ['completed', 'portfolio', 'archived'], true);
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
