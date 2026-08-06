<?php

namespace App\Enums;

enum EditorialWorkflowStage: string
{
    case Submission = 'submission';
    case EditorialReview = 'editorial_review';
    case AuthorRevision = 'author_revision';
    case FinalApproval = 'final_approval';
    case Publication = 'publication';

    public function label(): string
    {
        return match ($this) {
            self::Submission => 'Pengajuan',
            self::EditorialReview => 'Review Editorial',
            self::AuthorRevision => 'Perbaikan Penulis',
            self::FinalApproval => 'Persetujuan Akhir',
            self::Publication => 'Publikasi',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Submission => 'gray',
            self::EditorialReview => 'info',
            self::AuthorRevision => 'warning',
            self::FinalApproval => 'primary',
            self::Publication => 'success',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $stage): array => [$stage->value => $stage->label()])
            ->all();
    }
}
