<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsightRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'insight_id',
        'revision_number',
        'title',
        'excerpt',
        'content',
        'cover_image',
        'insight_category_id',
        'author_snapshot',
        'tag_snapshot',
        'seo_title',
        'seo_description',
        'revision_summary',
        'submitted_by',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'revision_number' => 'integer',
            'author_snapshot' => 'array',
            'tag_snapshot' => 'array',
            'submitted_at' => 'datetime',
        ];
    }

    public function insight(): BelongsTo
    {
        return $this->belongsTo(Insight::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InsightCategory::class, 'insight_category_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(InsightEditorialNote::class, 'revision_id');
    }
}
