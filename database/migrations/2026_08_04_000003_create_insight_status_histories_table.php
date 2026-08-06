<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insight_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('insight_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['insight_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insight_status_histories');
    }
};
