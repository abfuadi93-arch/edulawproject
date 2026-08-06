<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insights', function (Blueprint $table): void {
            $table->foreignId('assigned_editor_id')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->after('assigned_editor_id')->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable()->after('assigned_by');
            $table->timestamp('assigned_at')->nullable()->after('submitted_at');
            $table->timestamp('review_started_at')->nullable()->after('assigned_at');
            $table->timestamp('revision_requested_at')->nullable()->after('review_started_at');
            $table->timestamp('revised_at')->nullable()->after('revision_requested_at');
            $table->timestamp('approved_at')->nullable()->after('revised_at');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable()->after('approved_by');
            $table->foreignId('rejected_by')->nullable()->after('rejected_at')->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable()->after('rejected_by');
            $table->unsignedInteger('revision_round')->default(0)->after('rejection_reason');
            $table->dateTime('editorial_deadline')->nullable()->after('revision_round');

            $table->index(['status', 'assigned_editor_id'], 'insights_status_editor_index');
        });

        DB::table('insights')->where('status', 'reviewed')->update(['status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('insights', function (Blueprint $table): void {
            $table->dropIndex('insights_status_editor_index');
            $table->dropConstrainedForeignId('assigned_editor_id');
            $table->dropConstrainedForeignId('assigned_by');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropConstrainedForeignId('rejected_by');
            $table->dropColumn([
                'submitted_at',
                'assigned_at',
                'review_started_at',
                'revision_requested_at',
                'revised_at',
                'approved_at',
                'rejected_at',
                'rejection_reason',
                'revision_round',
                'editorial_deadline',
            ]);
        });
    }
};
