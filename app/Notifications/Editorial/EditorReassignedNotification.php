<?php

namespace App\Notifications\Editorial;

use App\Models\Insight;
use App\Models\InsightEditorAssignment;
use App\Models\User;

class EditorReassignedNotification extends EditorialDatabaseNotification
{
    public function __construct(Insight $insight, InsightEditorAssignment $assignment, User $actor, bool $isPreviousEditor)
    {
        parent::__construct(
            $insight,
            $assignment,
            $actor,
            'editor_reassigned',
            $isPreviousEditor ? 'Penugasan dipindahkan' : 'Penugasan baru',
            $isPreviousEditor
                ? "Penugasan {$insight->title} telah dialihkan ke Editor lain."
                : "Anda kini ditugaskan memeriksa {$insight->title}.",
            ($isPreviousEditor ? 'previous' : 'new').':'.$assignment->assigned_at?->timestamp,
        );
    }
}
