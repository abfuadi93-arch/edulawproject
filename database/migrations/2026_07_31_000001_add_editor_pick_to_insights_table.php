<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insights', function (Blueprint $table) {
            $table->boolean('editor_pick')
                ->default(false)
                ->after('featured')
                ->index();
        });

        // Sebelum kolom ini tersedia, artikel featured juga dipakai sebagai
        // pilihan editor. Pertahankan penempatan data lama setelah migrasi.
        DB::table('insights')
            ->where('featured', true)
            ->update(['editor_pick' => true]);
    }

    public function down(): void
    {
        Schema::table('insights', function (Blueprint $table) {
            $table->dropIndex(['editor_pick']);
            $table->dropColumn('editor_pick');
        });
    }
};
