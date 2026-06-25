<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasFactory;
    use HasRoles;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'bio',
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

    protected static function booted(): void
    {
        static::created(function (User $user): void {
            $user->ensureProfile();
        });

        static::updated(function (User $user): void {
            $user->syncLinkedProfileBasics();
        });
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active !== false;
    }

    public function getAvatarUrlAttribute(): ?string
    {
        $avatar = filled($this->attributes['avatar'] ?? null)
            ? trim((string) $this->attributes['avatar'])
            : null;

        if (! filled($avatar)) {
            return null;
        }

        if (Str::startsWith($avatar, ['http://', 'https://'])) {
            if (! filter_var($avatar, FILTER_VALIDATE_URL) || self::urlUsesLocalhost($avatar)) {
                return null;
            }

            $path = parse_url($avatar, PHP_URL_PATH) ?: '';

            if (Str::startsWith($path, '/storage/')) {
                return $this->publicAvatarUrl(Str::after($path, '/storage/'));
            }

            return $avatar;
        }

        return $this->publicAvatarUrl($avatar);
    }

    public function getInitialsAttribute(): string
    {
        $name = Str::of($this->name ?: 'Admin')->squish();
        $parts = $name->explode(' ')->filter()->values();

        if ($parts->isEmpty()) {
            return 'AD';
        }

        if ($parts->count() === 1) {
            return Str::of((string) $parts->first())->substr(0, 2)->upper()->toString();
        }

        return $parts
            ->take(2)
            ->map(fn (string $part): string => Str::of($part)->substr(0, 1)->upper()->toString())
            ->implode('');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url;
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
                'bio' => $this->bio,
                'photo' => $this->avatar,
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
            'bio' => $this->bio,
            'photo' => $profile->photo ?: $this->avatar,
            'institution' => $this->institution,
            'position' => $this->position,
            'profile_type' => $profile->profile_type ?: 'team',
            'is_active' => $profile->is_active ?? ($this->is_active !== false),
        ])->saveQuietly();

        return $profile->refresh();
    }

    public function syncLinkedProfileBasics(): void
    {
        $profile = $this->ensureProfile();

        $profile->forceFill([
            'name' => $this->name,
            'email' => $this->email,
            'bio' => $this->bio,
            'photo' => $this->avatar ?: $profile->photo,
            'institution' => $this->institution,
            'position' => $this->position,
            'is_active' => $this->is_active !== false,
        ])->saveQuietly();
    }

    private function publicAvatarUrl(string $path): ?string
    {
        $path = $this->normalizeAvatarPath($path);

        if (! filled($path) || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $url = Storage::disk('public')->url($path);

        if (self::urlUsesLocalhost($url)) {
            return '/storage/'.ltrim($path, '/');
        }

        return $url;
    }

    private function normalizeAvatarPath(string $path): ?string
    {
        $path = trim(str_replace('\\', '/', $path));

        if ($path === '' || Str::startsWith($path, ['data:', 'javascript:']) || Str::contains($path, '../')) {
            return null;
        }

        $path = preg_replace('/[?#].*$/', '', $path) ?? $path;
        $path = ltrim($path, '/');

        foreach (['public/storage/', 'storage/', 'public/'] as $prefix) {
            while (Str::startsWith($path, $prefix)) {
                $path = Str::after($path, $prefix);
            }
        }

        return filled($path) ? $path : null;
    }

    private static function urlUsesLocalhost(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (! filled($host)) {
            return false;
        }

        $host = Str::lower($host);

        return in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || Str::endsWith($host, '.localhost');
    }
}
