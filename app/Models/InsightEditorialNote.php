<?php

namespace App\Models;

use App\Enums\EditorialCommentField;
use App\Enums\EditorialCommentStatus;
use App\Enums\EditorialCommentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsightEditorialNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'insight_id',
        'parent_id',
        'user_id',
        'revision_id',
        'revision_round',
        'type',
        'field_name',
        'quoted_text',
        'status',
        'note',
        'is_visible_to_writer',
        'addressed_by',
        'addressed_at',
        'resolved_by',
        'resolved_at',
        'reopened_by',
        'reopened_at',
    ];

    protected function casts(): array
    {
        return [
            'revision_round' => 'integer',
            'type' => EditorialCommentType::class,
            'field_name' => EditorialCommentField::class,
            'status' => EditorialCommentStatus::class,
            'is_visible_to_writer' => 'boolean',
            'addressed_at' => 'datetime',
            'resolved_at' => 'datetime',
            'reopened_at' => 'datetime',
        ];
    }

    public function insight(): BelongsTo
    {
        return $this->belongsTo(Insight::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->oldest();
    }

    public function revision(): BelongsTo
    {
        return $this->belongsTo(InsightRevision::class, 'revision_id');
    }

    public function addressedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'addressed_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function reopenedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by');
    }

    public function scopeVisibleToWriter(Builder $query): Builder
    {
        return $query->where('is_visible_to_writer', true)->where('type', '!=', EditorialCommentType::Internal->value);
    }

    public function scopeBlockingApproval(Builder $query): Builder
    {
        return $query
            ->whereNull('parent_id')
            ->whereNotIn('type', [
                EditorialCommentType::Internal->value,
                EditorialCommentType::AuthorResponse->value,
                EditorialCommentType::Approval->value,
                EditorialCommentType::Rejection->value,
            ])
            ->whereIn('status', [
                EditorialCommentStatus::Open->value,
                EditorialCommentStatus::Addressed->value,
                EditorialCommentStatus::Reopened->value,
            ]);
    }
}
