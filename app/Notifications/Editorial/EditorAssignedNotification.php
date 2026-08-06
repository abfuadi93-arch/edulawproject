<?php

namespace App\Notifications\Editorial;

use App\Models\Insight;
use App\Models\InsightEditorAssignment;
use App\Models\User;

class EditorAssignedNotification extends EditorialDatabaseNotification
{
    public function __construct(Insight $insight, InsightEditorAssignment $assignment, User $actor)
    {
        parent::__construct(
            $insight,
            $assignment,
            $actor,
            'editor_assigned',
            'Naskah ditugaskan',
            "Anda ditugaskan memeriksa {$insight->title}.",
            (string) $assignment->assigned_at?->timestamp,
        );
    }

    protected function legacyNotificationType(): string
    {
        return 'assignment';
    }
}
