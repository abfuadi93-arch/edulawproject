<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Preserve the oldest linked profile and detach legacy duplicates before
        // enforcing the one-account/one-author invariant at database level.
        DB::table('authors')
            ->select('user_id')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('user_id')
            ->each(function (int $userId): void {
                $duplicateIds = DB::table('authors')
                    ->where('user_id', $userId)
                    ->orderBy('id')
                    ->pluck('id')
                    ->slice(1);

                DB::table('authors')
                    ->whereIn('id', $duplicateIds)
                    ->update(['user_id' => null]);
            });

        Schema::table('authors', function (Blueprint $table): void {
            $table->string('title')->nullable()->after('name');
            $table->string('location')->nullable()->after('position');
            $table->string('seo_title', 300)->nullable()->after('social_links');
            $table->text('meta_description')->nullable()->after('seo_title');
            $table->unique('user_id', 'authors_user_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('authors', function (Blueprint $table): void {
            $table->dropUnique('authors_user_id_unique');
            $table->dropColumn([
                'title',
                'location',
                'seo_title',
                'meta_description',
            ]);
        });
    }
};
