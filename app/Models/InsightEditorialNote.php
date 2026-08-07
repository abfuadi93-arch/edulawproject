<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsightEditorialNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'insight_id',
        'user_id',
        'revision_round',
        'type',
        'status',
        'note',
        'is_visible_to_writer',
    ];

    protected function casts(): array
    {
        return ['is_visible_to_writer' => 'boolean'];
    }

    public function insight(): BelongsTo
    {
        return $this->belongsTo(Insight::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
