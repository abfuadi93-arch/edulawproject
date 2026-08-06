<?php

namespace App\Enums;

enum EditorialCommentStatus: string
{
    case Open = 'open';
    case Addressed = 'addressed';
    case Resolved = 'resolved';
    case Reopened = 'reopened';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Terbuka',
            self::Addressed => 'Sudah Ditanggapi',
            self::Resolved => 'Selesai',
            self::Reopened => 'Dibuka Kembali',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])->all();
    }
}
