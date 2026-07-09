<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory;
    use HasRoles;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'institution',
        'position',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active !== false;
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Author::class)->oldestOfMany();
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(Author::class);
    }

    public function ensureProfile(): Author
    {
        $profile = $this->profile()->first();

        if (! $profile && filled($this->email)) {
            $profile = Author::query()
                ->where('email', $this->email)
                ->whereNull('user_id')
                ->oldest('id')
                ->first();
        }

        if (! $profile) {
            return Author::query()->create([
                'user_id' => $this->id,
                'name' => $this->name,
                'slug' => Author::uniqueSlugFor($this->name),
                'email' => $this->email,
                'institution' => $this->institution,
                'position' => $this->position,
                'profile_type' => 'team',
                'is_active' => $this->is_active !== false,
            ]);
        }

        $profile->forceFill([
            'user_id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'institution' => $this->institution,
            'position' => $this->position,
            'profile_type' => $profile->profile_type ?: 'team',
            'is_active' => $profile->is_active ?? ($this->is_active !== false),
        ])->saveQuietly();

        return $profile->refresh();
    }

    public function syncLinkedProfileBasics(): void
    {
        $profile = $this->profile()->first();

        if (! $profile) {
            return;
        }

        $profile->forceFill([
            'name' => $this->name,
            'email' => $this->email,
            'institution' => $this->institution,
            'position' => $this->position,
            'is_active' => $this->is_active !== false,
        ])->saveQuietly();
    }
}
