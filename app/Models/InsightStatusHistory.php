<?php

namespace App\Models;

use App\Enums\InsightStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsightStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'insight_id',
        'changed_by',
        'from_status',
        'to_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'from_status' => InsightStatus::class,
            'to_status' => InsightStatus::class,
        ];
    }

    public function insight(): BelongsTo
    {
        return $this->belongsTo(Insight::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
