<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsightCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public static function editorialLabel(?string $value): string
    {
        $value = $value ?: 'Editorial';

        return [
            'Insight Editorial' => 'Editorial',
            'Legal Insight' => 'Legal Editorial',
            'Insight' => 'Editorial',
            'insight' => 'editorial',
        ][$value] ?? $value;
    }

    public function getNameAttribute(?string $value): string
    {
        return self::editorialLabel($value);
    }

    public function getDescriptionAttribute(?string $value): ?string
    {
        return $value === null ? null : self::editorialLabel($value);
    }

    public function insights(): HasMany
    {
        return $this->hasMany(Insight::class);
    }
}
