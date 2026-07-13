<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_visits', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_id', 64)->index();
            $table->string('ip_hash', 64)->nullable()->index();
            $table->string('method', 10)->default('GET');
            $table->string('path', 2048);
            $table->string('full_url', 2048);
            $table->string('route_name')->nullable()->index();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->text('referrer')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('visited_at')->index();
            $table->timestamps();

            $table->index(['visited_at', 'route_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_visits');
    }
};
