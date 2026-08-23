<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->json('posters')->nullable()->after('poster');
        });

        DB::table('opportunities')
            ->whereNotNull('poster')
            ->where('poster', '!=', '')
            ->orderBy('id')
            ->chunkById(200, function ($opportunities): void {
                foreach ($opportunities as $opportunity) {
                    DB::table('opportunities')
                        ->where('id', $opportunity->id)
                        ->update(['posters' => json_encode([$opportunity->poster])]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropColumn('posters');
        });
    }
};
