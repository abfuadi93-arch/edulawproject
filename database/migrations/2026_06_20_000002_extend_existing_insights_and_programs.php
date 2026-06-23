<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insights', function (Blueprint $table) {
            $table->foreignId('insight_category_id')
                ->nullable()
                ->after('slug');

            $table->index(['insight_category_id', 'published_at']);
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->foreignId('program_category_id')
                ->nullable()
                ->after('slug');

            $table->index(['program_category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('program_category_id');
        });

        Schema::table('insights', function (Blueprint $table) {
            $table->dropColumn('insight_category_id');
        });
    }
};
