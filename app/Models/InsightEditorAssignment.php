<?php

namespace App\Models;

use App\Enums\EditorAssignmentStatus;
use App\Enums\EditorialWorkflowStage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsightEditorAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'insight_id',
        'editor_id',
        'assigned_by',
        'workflow_stage',
        'status',
        'assigned_at',
        'accepted_at',
        'started_at',
        'completed_at',
        'due_at',
        'assignment_note',
        'reassignment_reason',
    ];

    protected $casts = [
        'workflow_stage' => EditorialWorkflowStage::class,
        'status' => EditorAssignmentStatus::class,
        'assigned_at' => 'datetime',
        'accepted_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'due_at' => 'datetime',
    ];

    public function insight(): BelongsTo
    {
        return $this->belongsTo(Insight::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(InsightEditorialDecision::class, 'assignment_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(InsightEditorialActivity::class, 'assignment_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', EditorAssignmentStatus::activeValues());
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }
}
