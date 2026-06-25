<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function insights(): BelongsToMany
    {
        return $this->belongsToMany(Insight::class, 'insight_tag')
            ->withTimestamps();
    }

    public function publications(): BelongsToMany
    {
        return $this->belongsToMany(Publication::class, 'publication_tag')
            ->withTimestamps();
    }
}
