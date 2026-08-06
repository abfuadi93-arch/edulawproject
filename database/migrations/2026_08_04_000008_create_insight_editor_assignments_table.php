<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insight_editor_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('insight_id')->constrained()->cascadeOnDelete();
            $table->foreignId('editor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('workflow_stage');
            $table->string('status');
            $table->timestamp('assigned_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->text('assignment_note')->nullable();
            $table->text('reassignment_reason')->nullable();
            $table->timestamps();

            $table->index('insight_id');
            $table->index('editor_id');
            $table->index('status');
            $table->index('workflow_stage');
            $table->index('due_at');
            $table->index(['insight_id', 'status']);
            $table->index(['editor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insight_editor_assignments');
    }
};
