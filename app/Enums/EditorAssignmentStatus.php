<?php

namespace App\Enums;

enum EditorAssignmentStatus: string
{
    case Assigned = 'assigned';
    case Accepted = 'accepted';
    case Active = 'active';
    case Completed = 'completed';
    case Reassigned = 'reassigned';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Assigned => 'Penugasan Baru',
            self::Accepted => 'Diterima',
            self::Active => 'Aktif',
            self::Completed => 'Selesai',
            self::Reassigned => 'Dialihkan',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Assigned => 'primary',
            self::Accepted, self::Active => 'info',
            self::Completed => 'success',
            self::Reassigned => 'warning',
            self::Cancelled => 'danger',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Assigned, self::Accepted, self::Active], true);
    }

    public static function activeValues(): array
    {
        return [self::Assigned->value, self::Accepted->value, self::Active->value];
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])
            ->all();
    }
}
