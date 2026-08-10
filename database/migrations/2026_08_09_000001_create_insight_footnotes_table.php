<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insight_footnotes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('insight_id')->constrained()->cascadeOnDelete();
            $table->uuid('uuid')->unique();
            $table->text('content');
            $table->unsignedInteger('sort_order')->nullable();
            $table->timestamps();

            $table->index(['insight_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insight_footnotes');
    }
};
