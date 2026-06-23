<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'email',
        'bio',
        'photo',
        'institution',
        'position',
        'social_links',
        'is_active',
    ];

    protected $casts = [
        'social_links' => 'array',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function insights(): BelongsToMany
    {
        return $this->belongsToMany(Insight::class, 'author_insight')
            ->withPivot(['author_order', 'role'])
            ->withTimestamps();
    }

    public function publications(): BelongsToMany
    {
        return $this->belongsToMany(Publication::class, 'author_publication')
            ->withPivot(['author_order', 'role'])
            ->withTimestamps();
    }
}