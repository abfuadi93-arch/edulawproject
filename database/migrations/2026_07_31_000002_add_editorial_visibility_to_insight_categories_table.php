<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insight_categories', function (Blueprint $table) {
            $table->boolean('show_on_editorial_index')
                ->default(true)
                ->after('is_active')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('insight_categories', function (Blueprint $table) {
            $table->dropIndex(['show_on_editorial_index']);
            $table->dropColumn('show_on_editorial_index');
        });
    }
};
