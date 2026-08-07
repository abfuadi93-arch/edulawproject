<?php

namespace App\Models;

use App\Enums\InsightStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Insight extends Model
{
    use HasFactory;

    private const DEFAULT_COVER_IMAGE = 'images/hero/hero-edulaw.jpg';

    protected $attributes = [
        'status' => 'draft',
    ];

    protected $fillable = [
        'insight_category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'cover_image',
        'status',
        'published_at',
        'archived_at',
        'reading_time',
        'featured',
        'editor_pick',
        'sort_order',
        'seo_title',
        'seo_description',
        'og_image',
        'created_by',
        'updated_by',
        'reviewed_by',
        'reviewed_at',
        'editor_notes',
        'assigned_editor_id',
        'assigned_by',
        'submitted_at',
        'assigned_at',
        'revision_requested_at',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'editor_pick' => 'boolean',
        'status' => InsightStatus::class,
        'published_at' => 'datetime',
        'archived_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'submitted_at' => 'datetime',
        'assigned_at' => 'datetime',
        'revision_requested_at' => 'datetime',
        'reading_time' => 'integer',
        'sort_order' => 'integer',
    ];

    public function categoryRelation(): BelongsTo
    {
        return $this->belongsTo(InsightCategory::class, 'insight_category_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InsightCategory::class, 'insight_category_id');
    }

    public function insightCategory(): BelongsTo
    {
        return $this->categoryRelation();
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'author_insight')
            ->withPivot(['author_order', 'role'])
            ->withTimestamps();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'insight_tag')
            ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function assignedEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_editor_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function editorialNotes(): HasMany
    {
        return $this->hasMany(InsightEditorialNote::class)->latest();
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(InsightStatusHistory::class)->latest();
    }

    public function editorialActivities(): HasMany
    {
        return $this->hasMany(InsightEditorialActivity::class)->latest('created_at');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', InsightStatus::Published->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function scopeEditorPick(Builder $query): Builder
    {
        return $query->where('editor_pick', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('published_at')->latest();
    }

    public function getDisplayCategoryAttribute(): string
    {
        $category = $this->relationLoaded('categoryRelation') ? $this->categoryRelation : $this->category;
        $name = $category?->name ?: 'Editorial';

        return InsightCategory::editorialLabel($name);
    }

    public function getDisplayAuthorAttribute(): string
    {
        $names = $this->authors->pluck('name')->filter()->values();

        return $names->isNotEmpty() ? $names->join(', ') : 'Edulaw Project';
    }

    /*
     | Compatibility accessors untuk Blade lama.
     | Setelah view dirapikan, gunakan display_category, display_author,
     | featured, status, seo_title, dan seo_description secara langsung.
     */
    public function getAuthorNameAttribute(): string
    {
        return $this->display_author;
    }

    public function getEditorNameAttribute(): ?string
    {
        return $this->reviewer?->name;
    }

    public function getEditorialStatusAttribute(): string
    {
        return $this->status->label();
    }

    public function getIsFeaturedAttribute(): bool
    {
        return (bool) ($this->attributes['featured'] ?? false);
    }

    public function getIsPublishedAttribute(): bool
    {
        return ($this->attributes['status'] ?? null) === InsightStatus::Published->value;
    }

    public function getMetaTitleAttribute(): ?string
    {
        return $this->attributes['seo_title'] ?? null;
    }

    public function getMetaDescriptionAttribute(): ?string
    {
        return $this->attributes['seo_description'] ?? null;
    }

    public function getCoverImageUrlAttribute(): string
    {
        return edulaw_file_url(
            $this->attributes['cover_image'] ?? null,
            self::DEFAULT_COVER_IMAGE,
        );
    }

    public function getOgImageUrlAttribute(): ?string
    {
        return $this->resolveImageUrl($this->attributes['og_image'] ?? null)
            ?: $this->cover_image_url;
    }

    private function resolveImageUrl(?string $path): ?string
    {
        return edulaw_file_url($path);
    }
}
