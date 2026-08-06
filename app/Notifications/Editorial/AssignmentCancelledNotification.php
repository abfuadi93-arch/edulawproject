<?php

namespace App\Notifications\Editorial;

use App\Models\Insight;
use App\Models\InsightEditorAssignment;
use App\Models\User;

class AssignmentCancelledNotification extends EditorialDatabaseNotification
{
    public function __construct(Insight $insight, InsightEditorAssignment $assignment, User $actor)
    {
        parent::__construct(
            $insight,
            $assignment,
            $actor,
            'assignment_cancelled',
            'Penugasan dibatalkan',
            "Penugasan Anda untuk {$insight->title} telah dibatalkan.",
            (string) $assignment->completed_at?->timestamp,
        );
    }
}
