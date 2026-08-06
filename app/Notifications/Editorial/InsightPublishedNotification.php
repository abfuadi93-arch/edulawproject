<?php

namespace App\Notifications\Editorial;

use App\Models\Insight;
use App\Models\InsightEditorAssignment;
use App\Models\User;

class InsightPublishedNotification extends EditorialDatabaseNotification
{
    public function __construct(Insight $insight, ?InsightEditorAssignment $assignment, User $actor)
    {
        parent::__construct(
            $insight,
            $assignment,
            $actor,
            'insight_published',
            'Naskah diterbitkan',
            "{$insight->title} sudah tayang.",
            (string) $insight->published_at?->timestamp,
        );
    }
}
