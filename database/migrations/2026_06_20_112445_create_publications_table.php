<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('pdf_file')->nullable();
            $table->string('external_url')->nullable();
            $table->string('source_name')->nullable();
            $table->date('published_at')->nullable();
            $table->unsignedInteger('page_count')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('featured')->default(false);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('og_image')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index(['featured', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};
