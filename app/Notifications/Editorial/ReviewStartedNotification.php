<?php

namespace App\Notifications\Editorial;

use App\Models\Insight;
use App\Models\InsightEditorAssignment;
use App\Models\User;

class ReviewStartedNotification extends EditorialDatabaseNotification
{
    public function __construct(Insight $insight, InsightEditorAssignment $assignment, User $actor)
    {
        parent::__construct(
            $insight,
            $assignment,
            $actor,
            'review_started',
            'Review dimulai',
            "Editor mulai memeriksa {$insight->title}.",
            (string) $assignment->started_at?->timestamp,
        );
    }
}
