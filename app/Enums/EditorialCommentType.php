<?php

namespace App\Enums;

enum EditorialCommentType: string
{
    case General = 'general';
    case Section = 'section';
    case RevisionRequest = 'revision_request';
    case AuthorResponse = 'author_response';
    case Internal = 'internal';
    case Approval = 'approval';
    case Rejection = 'rejection';

    public function label(): string
    {
        return match ($this) {
            self::General => 'Umum',
            self::Section => 'Bagian Naskah',
            self::RevisionRequest => 'Permintaan Perbaikan',
            self::AuthorResponse => 'Balasan Penulis',
            self::Internal => 'Catatan Internal',
            self::Approval => 'Persetujuan',
            self::Rejection => 'Penolakan',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])->all();
    }
}
