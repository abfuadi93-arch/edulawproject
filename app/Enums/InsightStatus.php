<?php

namespace App\Enums;

enum InsightStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case EditorAssigned = 'editor_assigned';
    case InReview = 'in_review';
    case RevisionRequested = 'revision_requested';
    case Revised = 'revised';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Published = 'published';
    case Archived = 'archived';
    /** @deprecated Nilai lama, dipertahankan agar data historis tetap dapat dibaca. */
    case LegacyReviewed = 'reviewed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Submitted => 'Dikirim',
            self::EditorAssigned => 'Editor Ditugaskan',
            self::InReview => 'Sedang Diperiksa',
            self::RevisionRequested => 'Perlu Perbaikan',
            self::Revised => 'Perbaikan Dikirim',
            self::Approved => 'Disetujui',
            self::Rejected => 'Tidak Dilanjutkan',
            self::Published => 'Diterbitkan',
            self::Archived => 'Diarsipkan',
            self::LegacyReviewed => 'Disetujui',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Published, self::Approved, self::LegacyReviewed => 'success',
            self::Rejected => 'danger',
            self::RevisionRequested => 'warning',
            self::InReview, self::Revised, self::EditorAssigned => 'info',
            self::Submitted => 'primary',
            default => 'gray',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->reject(fn (self $status): bool => $status === self::LegacyReviewed)
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])
            ->all();
    }
}
