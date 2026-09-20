<?php

namespace App\Support;

final class OpportunityCategory
{
    public static function options(): array
    {
        return [
            'scholarship' => 'Beasiswa',
            'internship' => 'Magang',
            'career' => 'Karier',
            'competition' => 'Kompetisi',
            'call_for_paper' => 'Call for Papers',
            'open_collaboration' => 'Program & Kolaborasi',
        ];
    }

    public static function normalize(?string $type): ?string
    {
        return in_array($type, ['fellowship', 'volunteer', 'exchange', 'training', 'summer_school'], true)
            ? 'open_collaboration'
            : $type;
    }

    public static function values(string $type): array
    {
        return self::normalize($type) === 'open_collaboration'
            ? ['open_collaboration', 'fellowship', 'volunteer', 'exchange', 'training', 'summer_school']
            : [$type];
    }

    public static function label(?string $type): string
    {
        return self::options()[self::normalize($type)] ?? ucfirst(str_replace('_', ' ', (string) $type));
    }
}
