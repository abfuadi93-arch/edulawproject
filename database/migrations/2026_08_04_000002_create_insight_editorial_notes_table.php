<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insight_editorial_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('insight_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('revision_round')->default(0);
            $table->string('type')->default('general');
            $table->longText('note');
            $table->boolean('is_visible_to_writer')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insight_editorial_notes');
    }
};
