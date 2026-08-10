<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InsightFootnote extends Model
{
    protected $fillable = [
        'insight_id',
        'uuid',
        'content',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (InsightFootnote $footnote): void {
            $footnote->uuid ??= (string) Str::uuid();
        });
    }

    public function insight(): BelongsTo
    {
        return $this->belongsTo(Insight::class);
    }
}
