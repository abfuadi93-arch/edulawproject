<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('authors', 'whatsapp')) {
            return;
        }

        Schema::table('authors', function (Blueprint $table): void {
            $table->dropColumn('whatsapp');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('authors', 'whatsapp')) {
            return;
        }

        Schema::table('authors', function (Blueprint $table): void {
            $table->string('whatsapp', 40)->nullable()->after('email');
        });
    }
};
