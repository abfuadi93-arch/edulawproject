<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insight_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insight_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['insight_id', 'tag_id']);
            $table->index(['tag_id', 'insight_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insight_tag');
    }
};
