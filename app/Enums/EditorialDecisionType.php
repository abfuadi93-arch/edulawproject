<?php

namespace App\Enums;

enum EditorialDecisionType: string
{
    case Submit = 'submit';
    case AssignEditor = 'assign_editor';
    case ReassignEditor = 'reassign_editor';
    case AcceptAssignment = 'accept_assignment';
    case StartReview = 'start_review';
    case RequestRevision = 'request_revision';
    case Resubmit = 'resubmit';
    case Approve = 'approve';
    case Reject = 'reject';
    case ReturnToReview = 'return_to_review';
    case Publish = 'publish';
    case Archive = 'archive';
    case CancelAssignment = 'cancel_assignment';

    public function label(): string
    {
        return match ($this) {
            self::Submit => 'Naskah Dikirim',
            self::AssignEditor => 'Editor Ditugaskan',
            self::ReassignEditor => 'Editor Diganti',
            self::AcceptAssignment => 'Penugasan Diterima',
            self::StartReview => 'Review Dimulai',
            self::RequestRevision => 'Perbaikan Diminta',
            self::Resubmit => 'Perbaikan Dikirim',
            self::Approve => 'Naskah Disetujui',
            self::Reject => 'Naskah Ditolak',
            self::ReturnToReview => 'Kembali ke Review',
            self::Publish => 'Naskah Diterbitkan',
            self::Archive => 'Naskah Diarsipkan',
            self::CancelAssignment => 'Penugasan Dibatalkan',
        };
    }
}
