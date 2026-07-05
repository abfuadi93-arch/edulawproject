<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('authors', function (Blueprint $table) {
            if (! Schema::hasColumn('authors', 'sort_order')) {
                $table->unsignedInteger('sort_order')->nullable()->after('profile_type')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('authors', function (Blueprint $table) {
            if (Schema::hasColumn('authors', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }
};
