<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table): void {
            $table->string('second_link_label', 80)->nullable()->after('additional_link_url');
            $table->string('second_link_url')->nullable()->after('second_link_label');
        });
    }

    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table): void {
            $table->dropColumn(['second_link_label', 'second_link_url']);
        });
    }
};
