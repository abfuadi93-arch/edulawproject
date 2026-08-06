<?php

namespace App\Models;

use App\Enums\EditorialDecisionType;
use App\Enums\EditorialWorkflowStage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class InsightEditorialDecision extends Model
{
    use HasFactory;

    protected $fillable = [
        'insight_id',
        'assignment_id',
        'workflow_stage',
        'decision',
        'decided_by',
        'decision_note',
        'decided_at',
        'supersedes_decision_id',
        'metadata',
    ];

    protected $casts = [
        'workflow_stage' => EditorialWorkflowStage::class,
        'decision' => EditorialDecisionType::class,
        'decided_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Decision editorial bersifat immutable.'));
        static::deleting(fn () => throw new LogicException('Decision editorial tidak dapat dihapus.'));
    }

    public function insight(): BelongsTo
    {
        return $this->belongsTo(Insight::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(InsightEditorAssignment::class, 'assignment_id');
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function supersedes(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supersedes_decision_id');
    }

    public function supersededBy(): HasMany
    {
        return $this->hasMany(self::class, 'supersedes_decision_id');
    }
}
