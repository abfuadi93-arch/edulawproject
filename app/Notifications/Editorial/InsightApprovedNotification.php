<?php

namespace App\Notifications\Editorial;

use App\Models\Insight;
use App\Models\InsightEditorAssignment;
use App\Models\User;

class InsightApprovedNotification extends EditorialDatabaseNotification
{
    public function __construct(Insight $insight, ?InsightEditorAssignment $assignment, User $actor)
    {
        parent::__construct(
            $insight,
            $assignment,
            $actor,
            'insight_approved',
            'Naskah disetujui',
            "{$insight->title} telah disetujui.",
            (string) $insight->approved_at?->timestamp,
        );
    }
}
