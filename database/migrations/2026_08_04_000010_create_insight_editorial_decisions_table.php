<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insight_editorial_decisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('insight_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assignment_id')->nullable()->constrained('insight_editor_assignments')->nullOnDelete();
            $table->string('workflow_stage');
            $table->string('decision');
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('decision_note')->nullable();
            $table->timestamp('decided_at');
            $table->foreignId('supersedes_decision_id')->nullable()->constrained('insight_editorial_decisions')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['insight_id', 'decided_at']);
            $table->index(['assignment_id', 'decision']);
            $table->index(['workflow_stage', 'decision']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insight_editorial_decisions');
    }
};
