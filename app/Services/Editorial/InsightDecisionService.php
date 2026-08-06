<?php

namespace App\Services\Editorial;

use App\Enums\EditorialDecisionType;
use App\Models\Insight;
use App\Models\InsightEditorAssignment;
use App\Models\InsightEditorialDecision;
use App\Models\User;

class InsightDecisionService
{
    public function record(
        Insight $insight,
        EditorialDecisionType $decision,
        User $actor,
        ?InsightEditorAssignment $assignment = null,
        ?string $note = null,
        array $metadata = [],
        ?InsightEditorialDecision $supersedes = null,
    ): InsightEditorialDecision {
        return $insight->editorialDecisions()->create([
            'assignment_id' => $assignment?->id,
            'workflow_stage' => $insight->workflow_stage,
            'decision' => $decision,
            'decided_by' => $actor->id,
            'decision_note' => filled($note) ? trim($note) : null,
            'decided_at' => now(),
            'supersedes_decision_id' => $supersedes?->id,
            'metadata' => $metadata ?: null,
        ]);
    }
}
