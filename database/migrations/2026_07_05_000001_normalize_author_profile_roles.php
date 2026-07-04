<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('authors', 'profile_type')) {
            return;
        }

        DB::table('authors')
            ->select(['id', 'position', 'profile_type'])
            ->orderBy('id')
            ->chunkById(100, function ($profiles): void {
                foreach ($profiles as $profile) {
                    DB::table('authors')
                        ->where('id', $profile->id)
                        ->update([
                            'profile_type' => $this->profileTypeFor($profile->position, $profile->profile_type),
                        ]);
                }
            });
    }

    public function down(): void
    {
        //
    }

    private function profileTypeFor(?string $position, ?string $profileType): string
    {
        $position = Str::of((string) $position)->lower()->squish()->toString();
        $profileType = Str::of((string) $profileType)->lower()->squish()->replace(['-', ' '], '_')->toString();

        if (Str::contains($position, ['co-founder', 'co founder', 'cofounder'])) {
            return 'co_founder';
        }

        if (Str::contains($position, 'founder')) {
            return 'founder';
        }

        if (Str::contains($position, 'manager')) {
            return 'manager';
        }

        return match ($profileType) {
            'founder', 'co_founder', 'manager', 'team' => $profileType,
            default => 'team',
        };
    }
};
