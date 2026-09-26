<?php

namespace App\Support;

final class ChannelHero
{
    public static function srcset(?string $source): ?string
    {
        foreach (['programs', 'opportunities', 'multimedia', 'publications'] as $channel) {
            if ($source === asset("images/hero/channels/{$channel}-1600.webp")) {
                return collect([640, 960, 1600])
                    ->map(fn (int $width): string => asset("images/hero/channels/{$channel}-{$width}.webp")." {$width}w")
                    ->implode(', ');
            }
        }

        return null;
    }
}
