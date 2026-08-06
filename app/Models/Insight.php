<?php

namespace App\Models;

use App\Enums\EditorialWorkflowStage;
use App\Enums\InsightStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Insight extends Model
{
    use HasFactory;

    private const DEFAULT_COVER_IMAGE = 'images/hero/hero-edulaw.jpg';

    protected $attributes = [
        'status' => 'draft',
        'workflow_stage' => 'submission',
        'revision_round' => 0,
        'current_review_round' => 0,
        'current_revision_number' => 0,
    ];

    protected $fillable = [
        'insight_category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'cover_image',
        'status',
        'workflow_stage',
        'current_review_round',
        'current_revision_number',
        'published_at',
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
        'assigned_editor_id',
        'assigned_by',
        'submitted_at',
        'assigned_at',
        'review_started_at',
        'revision_requested_at',
        'revised_at',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
        'revision_round',
        'editorial_deadline',
        'editor_deadline',
        'writer_deadline',
        'editor_deadline_completed_at',
        'writer_deadline_completed_at',
        'editor_deadline_extension_count',
        'writer_deadline_extension_count',
        'deadline_extension_note',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'editor_pick' => 'boolean',
        'status' => InsightStatus::class,
        'workflow_stage' => EditorialWorkflowStage::class,
        'current_review_round' => 'integer',
        'current_revision_number' => 'integer',
        'published_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'submitted_at' => 'datetime',
        'assigned_at' => 'datetime',
        'review_started_at' => 'datetime',
        'revision_requested_at' => 'datetime',
        'revised_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'editorial_deadline' => 'datetime',
        'editor_deadline' => 'datetime',
        'writer_deadline' => 'datetime',
        'editor_deadline_completed_at' => 'datetime',
        'writer_deadline_completed_at' => 'datetime',
        'editor_deadline_extension_count' => 'integer',
        'writer_deadline_extension_count' => 'integer',
        'revision_round' => 'integer',
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

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function editorialNotes(): HasMany
    {
        return $this->hasMany(InsightEditorialNote::class)->latest();
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(InsightStatusHistory::class)->latest();
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(InsightRevision::class);
    }

    public function editorAssignments(): HasMany
    {
        return $this->hasMany(InsightEditorAssignment::class);
    }

    public function activeEditorAssignment(): HasOne
    {
        return $this->hasOne(InsightEditorAssignment::class)
            ->active()
            ->latestOfMany();
    }

    public function editorialDecisions(): HasMany
    {
        return $this->hasMany(InsightEditorialDecision::class)->latest('decided_at');
    }

    public function editorialActivities(): HasMany
    {
        return $this->hasMany(InsightEditorialActivity::class)->latest('created_at');
    }

    public function currentEditorUser(): ?User
    {
        return $this->activeEditorAssignment?->editor;
    }

    public function latestRevision(): HasOne
    {
        return $this->hasOne(InsightRevision::class)->latestOfMany('revision_number');
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
