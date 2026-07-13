<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PageVisit extends Model
{
    protected $fillable = [
        'visitor_id',
        'ip_hash',
        'method',
        'path',
        'full_url',
        'route_name',
        'status_code',
        'referrer',
        'user_agent',
        'visited_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    public function scopeSince(Builder $query, Carbon $date): Builder
    {
        return $query->where('visited_at', '>=', $date);
    }
}
