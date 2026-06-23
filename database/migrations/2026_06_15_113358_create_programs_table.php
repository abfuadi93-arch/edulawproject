<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            // program_category_id ditambahkan pada migration 2026_06_20_000002
            // karena tabel program_categories dibuat setelah file ini.
            $table->text('short_description')->nullable();
            $table->json('learning_points')->nullable();
            $table->string('image')->nullable();
            $table->string('format')->nullable()->index();
            $table->string('level')->nullable();
            $table->string('audience')->nullable();
            $table->date('event_date')->nullable()->index();
            $table->date('end_date')->nullable();
            $table->json('speakers')->nullable();
            $table->string('registration_link')->nullable();
            $table->string('location')->nullable();
            $table->string('price_type')->nullable();
            $table->boolean('certificate_available')->default(false);
            $table->string('status')->default('upcoming')->index();
            $table->boolean('featured')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('og_image')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'featured', 'event_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
