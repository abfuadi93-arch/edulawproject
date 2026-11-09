<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['insights', 'publications'] as $table) {
            Schema::table($table, fn (Blueprint $table) => $table->timestamp('content_modified_at')->nullable());
        }
    }

    public function down(): void
    {
        foreach (['insights', 'publications'] as $table) {
            Schema::table($table, fn (Blueprint $table) => $table->dropColumn('content_modified_at'));
        }
    }
};
