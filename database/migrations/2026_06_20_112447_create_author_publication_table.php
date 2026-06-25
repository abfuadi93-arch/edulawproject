<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('author_publication', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained()->cascadeOnDelete();
            $table->foreignId('publication_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('author_order')->default(0);
            $table->string('role')->nullable();
            $table->timestamps();

            $table->unique(['author_id', 'publication_id']);
            $table->index(['publication_id', 'author_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('author_publication');
    }
};
