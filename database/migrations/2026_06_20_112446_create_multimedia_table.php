<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('multimedia', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type')->default('video');
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('media_url')->nullable();
            $table->string('embed_url')->nullable();
            $table->string('platform')->default('website');
            $table->string('duration')->nullable();
            $table->date('published_at')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('featured')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index(['featured', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('multimedia');
    }
};