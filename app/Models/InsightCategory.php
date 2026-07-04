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
        return str_replace(
            ['Insight Editorial', 'Legal Insight', 'Edulaw Insight', 'Insight', 'insight'],
            ['Editorial', 'Legal Editorial', 'Edulaw Editorial', 'Editorial', 'editorial'],
            $value ?: 'Editorial'
        );
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
