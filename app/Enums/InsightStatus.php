<?php

namespace App\Enums;

enum InsightStatus: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Published = 'published';
    case Archived = 'archived';

    /** @deprecated Status lama hanya untuk membaca data sebelum migrasi penyederhanaan. */
    case Submitted = 'submitted';
    case EditorAssigned = 'editor_assigned';
    case InReview = 'in_review';
    case RevisionRequested = 'revision_requested';
    case Revised = 'revised';
    case Approved = 'approved';
    case Rejected = 'rejected';
    /** @deprecated Nilai lama, dipertahankan agar data historis tetap dapat dibaca. */
    case LegacyReviewed = 'reviewed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Review,
            self::Submitted,
            self::EditorAssigned,
            self::InReview,
            self::Revised,
            self::Approved,
            self::Rejected,
            self::LegacyReviewed => 'Sedang Direview',
            self::RevisionRequested => 'Draf',
            self::Published => 'Terbit',
            self::Archived => 'Diarsipkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Published => 'success',
            self::Review,
            self::Submitted,
            self::EditorAssigned,
            self::InReview,
            self::Revised,
            self::Approved,
            self::Rejected,
            self::LegacyReviewed => 'warning',
            default => 'gray',
        };
    }

    public function canonical(): self
    {
        return match ($this) {
            self::RevisionRequested => self::Draft,
            self::Submitted,
            self::EditorAssigned,
            self::InReview,
            self::Revised,
            self::Approved,
            self::Rejected,
            self::LegacyReviewed => self::Review,
            default => $this,
        };
    }

    public static function options(): array
    {
        return collect([self::Draft, self::Review, self::Published])
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])
            ->all();
    }
}
