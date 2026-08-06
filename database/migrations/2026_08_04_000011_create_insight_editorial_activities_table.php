<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insight_editorial_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('insight_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event');
            $table->string('workflow_stage')->nullable();
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->foreignId('assignment_id')->nullable()->constrained('insight_editor_assignments')->nullOnDelete();
            $table->foreignId('decision_id')->nullable()->constrained('insight_editorial_decisions')->nullOnDelete();
            $table->nullableMorphs('subject');
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['insight_id', 'created_at']);
            $table->index(['event', 'created_at']);
            $table->index(['assignment_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insight_editorial_activities');
    }
};
