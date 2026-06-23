<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->default('general');
            $table->string('key')->unique();
            $table->string('label');
            $table->string('type')->default('text');
            $table->text('value')->nullable();
            $table->text('help_text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->index(['group', 'sort_order']);
            $table->index('is_public');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
