<?php

namespace App\Enums;

enum EditorialActivityType: string
{
    case SubmissionSubmitted = 'submission_submitted';
    case EditorAssigned = 'editor_assigned';
    case EditorReassigned = 'editor_reassigned';
    case AssignmentAccepted = 'assignment_accepted';
    case ReviewStarted = 'review_started';
    case AssignmentCompleted = 'assignment_completed';
    case AssignmentCancelled = 'assignment_cancelled';
    case WorkflowStageChanged = 'workflow_stage_changed';
    case DecisionRecorded = 'decision_recorded';
    case RevisionRequested = 'revision_requested';
    case RevisionSubmitted = 'revision_submitted';
    case InsightApproved = 'insight_approved';
    case InsightRejected = 'insight_rejected';
    case PublicationScheduled = 'publication_scheduled';
    case InsightPublished = 'insight_published';
    case InsightArchived = 'insight_archived';
    case NotificationSent = 'notification_sent';
    case NotificationFailed = 'notification_failed';

    public function label(): string
    {
        return str($this->value)->replace('_', ' ')->headline()->toString();
    }
}
