<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final class OpportunityDeadline
{
    public static function today(): CarbonImmutable
    {
        return CarbonImmutable::now('Asia/Jakarta')->startOfDay();
    }

    public static function daysRemaining(CarbonInterface $deadline): int
    {
        // A deadline is a calendar date, not a timestamp to convert between zones.
        $date = CarbonImmutable::parse($deadline->toDateString(), 'Asia/Jakarta');

        return (int) self::today()->diffInDays($date, false);
    }
}
