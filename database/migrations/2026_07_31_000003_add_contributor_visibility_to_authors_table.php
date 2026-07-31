<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('authors', function (Blueprint $table) {
            if (! Schema::hasColumn('authors', 'show_in_contributor_section')) {
                $table->boolean('show_in_contributor_section')
                    ->default(false)
                    ->after('show_in_organization')
                    ->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('authors', function (Blueprint $table) {
            if (Schema::hasColumn('authors', 'show_in_contributor_section')) {
                $table->dropColumn('show_in_contributor_section');
            }
        });
    }
};
