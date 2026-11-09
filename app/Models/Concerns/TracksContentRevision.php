<?php

namespace App\Models\Concerns;

use Illuminate\Support\Carbon;

trait TracksContentRevision
{
    protected static function bootTracksContentRevision(): void
    {
        static::updating(function (self $record): void {
            if ($record->isDirty(self::PUBLIC_CONTENT_FIELDS)) {
                $record->content_modified_at = now();
            }
        });
    }

    public function publicRevisionDate(): ?Carbon
    {
        $modified = $this->content_modified_at;

        return $modified && $this->published_at && $modified->gt($this->published_at) ? $modified : null;
    }
}
