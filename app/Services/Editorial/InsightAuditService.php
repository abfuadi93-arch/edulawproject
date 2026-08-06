<?php

namespace App\Services\Editorial;

use App\Enums\EditorialActivityType;
use App\Models\Insight;
use App\Models\InsightEditorialActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class InsightAuditService
{
    public function record(
        Insight $insight,
        EditorialActivityType|string $event,
        ?User $actor,
        string $description,
        array $context = [],
    ): InsightEditorialActivity {
        $subject = $context['subject'] ?? null;

        return $insight->editorialActivities()->create([
            'actor_id' => $actor?->id,
            'event' => $event instanceof EditorialActivityType ? $event->value : $event,
            'workflow_stage' => $context['workflow_stage'] ?? $insight->workflow_stage?->value,
            'from_status' => $context['from_status'] ?? null,
            'to_status' => $context['to_status'] ?? null,
            'assignment_id' => $context['assignment_id'] ?? null,
            'decision_id' => $context['decision_id'] ?? null,
            'subject_type' => $subject instanceof Model ? $subject->getMorphClass() : ($context['subject_type'] ?? null),
            'subject_id' => $subject instanceof Model ? $subject->getKey() : ($context['subject_id'] ?? null),
            'description' => $description,
            'metadata' => ($context['metadata'] ?? []) ?: null,
            'created_at' => now(),
        ]);
    }
}
