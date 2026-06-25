<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollaborationSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'institution',
        'position',
        'collaboration_type',
        'subject',
        'message',
        'attachment',
        'status',
        'internal_notes',
        'handled_by',
        'handled_at',
    ];

    protected $casts = [
        'handled_at' => 'datetime',
    ];

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
