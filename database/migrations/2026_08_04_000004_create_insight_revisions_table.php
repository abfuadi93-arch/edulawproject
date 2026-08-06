<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insight_revisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('insight_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('revision_number');
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('cover_image')->nullable();
            $table->foreignId('insight_category_id')->nullable()->constrained()->nullOnDelete();
            $table->json('author_snapshot')->nullable();
            $table->json('tag_snapshot')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->longText('revision_summary')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['insight_id', 'revision_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insight_revisions');
    }
};
