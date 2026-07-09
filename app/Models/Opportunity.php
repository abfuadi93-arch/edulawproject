<?php

namespace App\Models;

use App\Support\EdulawSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Opportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'type',
        'excerpt',
        'description',
        'poster',
        'deadline',
        'application_link',
        'format',
        'location',
        'eligibility',
        'benefits',
        'status',
        'featured',
        'seo_title',
        'seo_description',
        'og_image',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'deadline' => 'date',
        'eligibility' => 'array',
        'benefits' => 'array',
        'featured' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    public function getPosterUrlAttribute(): ?string
    {
        return EdulawSite::assetUrl($this->attributes['poster'] ?? null);
    }

    public function getDisplayTypeAttribute(): string
    {
        return match ($this->attributes['type'] ?? null) {
            'scholarship' => 'Beasiswa',
            'internship' => 'Magang',
            'volunteer' => 'Volunteer',
            'fellowship' => 'Fellowship',
            'call_for_paper' => 'Call for Paper',
            'competition' => 'Kompetisi',
            'open_collaboration' => 'Kolaborasi Terbuka',
            default => ucfirst(str_replace('_', ' ', (string) ($this->attributes['type'] ?? 'Opportunity'))),
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->display_type;
    }

    public function getDisplayStatusAttribute(): string
    {
        return match ($this->attributes['status'] ?? null) {
            'open' => 'Masih Dibuka',
            'closed' => 'Ditutup',
            default => 'Arsip',
        };
    }
}
