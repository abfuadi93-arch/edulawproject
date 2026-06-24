<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('authors', function (Blueprint $table) {
            if (! Schema::hasColumn('authors', 'interests')) {
                $table->text('interests')->nullable()->after('bio');
            }

            if (! Schema::hasColumn('authors', 'profile_type')) {
                $table->string('profile_type')->nullable()->after('position')->index();
            }
        });

        $this->ensureEveryUserHasProfile();
    }

    public function down(): void
    {
        Schema::table('authors', function (Blueprint $table) {
            if (Schema::hasColumn('authors', 'profile_type')) {
                $table->dropColumn('profile_type');
            }

            if (Schema::hasColumn('authors', 'interests')) {
                $table->dropColumn('interests');
            }
        });
    }

    private function ensureEveryUserHasProfile(): void
    {
        DB::table('users')
            ->orderBy('id')
            ->get()
            ->each(function (object $user): void {
                $profile = DB::table('authors')
                    ->where('user_id', $user->id)
                    ->orderBy('id')
                    ->first();

                if (! $profile && filled($user->email)) {
                    $profile = DB::table('authors')
                        ->where('email', $user->email)
                        ->whereNull('user_id')
                        ->orderBy('id')
                        ->first();
                }

                $now = now();

                if ($profile) {
                    DB::table('authors')
                        ->where('id', $profile->id)
                        ->update([
                            'user_id' => $user->id,
                            'email' => $profile->email ?: $user->email,
                            'bio' => $profile->bio ?: $user->bio,
                            'photo' => $profile->photo ?: $user->avatar,
                            'institution' => $profile->institution ?: $user->institution,
                            'position' => $profile->position ?: $user->position,
                            'profile_type' => $profile->profile_type ?: 'team',
                            'is_active' => (bool) $user->is_active,
                            'updated_at' => $now,
                        ]);

                    return;
                }

                DB::table('authors')->insert([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'slug' => $this->uniqueProfileSlug($user->name),
                    'email' => $user->email,
                    'bio' => $user->bio,
                    'interests' => null,
                    'photo' => $user->avatar,
                    'institution' => $user->institution,
                    'position' => $user->position,
                    'profile_type' => 'team',
                    'social_links' => null,
                    'is_active' => (bool) $user->is_active,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
    }

    private function uniqueProfileSlug(string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'profil';
        $slug = $baseSlug;
        $suffix = 2;

        while (DB::table('authors')->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
};
