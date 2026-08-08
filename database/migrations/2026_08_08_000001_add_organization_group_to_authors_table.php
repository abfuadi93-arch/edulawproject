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
            if (! Schema::hasColumn('authors', 'organization_group')) {
                $table->string('organization_group')
                    ->nullable()
                    ->after('profile_type')
                    ->index();
            }
        });

        if (! Schema::hasColumn('authors', 'organization_group')) {
            return;
        }

        DB::table('authors')
            ->select(['id', 'name', 'position', 'profile_type'])
            ->orderBy('id')
            ->chunkById(100, function ($profiles): void {
                foreach ($profiles as $profile) {
                    if (! $this->isContributorProfile($profile->profile_type)) {
                        continue;
                    }

                    DB::table('authors')
                        ->where('id', $profile->id)
                        ->update([
                            'organization_group' => $this->inferGroup($profile->position, $profile->name),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('authors', function (Blueprint $table) {
            if (Schema::hasColumn('authors', 'organization_group')) {
                $table->dropColumn('organization_group');
            }
        });
    }

    private function isContributorProfile(?string $profileType): bool
    {
        $profileType = Str::of((string) $profileType)
            ->lower()
            ->squish()
            ->replace(['-', ' '], '_')
            ->toString();

        return ! in_array($profileType, ['director', 'manager', 'founder', 'co_founder'], true);
    }

    private function inferGroup(?string $position, ?string $name): string
    {
        $searchText = Str::lower(collect([$name, $position])->filter()->join(' '));
        $nameKey = Str::of((string) $name)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/i', ' ')
            ->squish()
            ->toString();
        $knownResearchMembers = [
            'siti mahmuda',
            'siti mahmudha',
            'annisa zahra nur umar',
            'anisa zahra nur umar',
            'naufal rizqiyanto',
            'lalu rizqi ramdani alfaen',
            'fadila sharfina',
            'laila andayani',
            'rahmatika monati',
            'amirudin nur wahid',
            'mely noviyanti',
            'putri yuliani',
            'fadlah nur',
        ];

        if (in_array($nameKey, $knownResearchMembers, true)
            || Str::contains($searchText, ['research', 'riset', 'peneliti'])) {
            return 'research_team';
        }

        if (Str::contains($searchText, ['internship', 'intern', 'magang'])) {
            return 'internship_member';
        }

        if (Str::contains($searchText, ['writer', 'penulis'])) {
            return 'writer';
        }

        if (Str::contains($searchText, ['speaker', 'moderator', 'narasumber', 'pembicara'])) {
            return 'speaker_moderator';
        }

        return 'internship_member';
    }
};
