<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type')->default('open_collaboration');
            $table->text('excerpt')->nullable();
            $table->longText('description')->nullable();
            $table->string('poster')->nullable();
            $table->date('deadline')->nullable();
            $table->string('application_link')->nullable();
            $table->string('format')->nullable();
            $table->string('location')->nullable();
            $table->json('eligibility')->nullable();
            $table->json('benefits')->nullable();
            $table->string('status')->default('open');
            $table->boolean('featured')->default(false);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('og_image')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'deadline']);
            $table->index(['featured', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};