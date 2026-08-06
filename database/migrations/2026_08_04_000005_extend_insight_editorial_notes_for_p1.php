<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insight_editorial_notes', function (Blueprint $table): void {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('insight_editorial_notes')->cascadeOnDelete();
            $table->foreignId('revision_id')->nullable()->after('user_id')->constrained('insight_revisions')->nullOnDelete();
            $table->string('field_name')->nullable()->after('type');
            $table->text('quoted_text')->nullable()->after('field_name');
            $table->string('status')->default('open')->after('quoted_text')->index();
            $table->foreignId('addressed_by')->nullable()->after('is_visible_to_writer')->constrained('users')->nullOnDelete();
            $table->timestamp('addressed_at')->nullable()->after('addressed_by');
            $table->foreignId('resolved_by')->nullable()->after('addressed_at')->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable()->after('resolved_by');
            $table->foreignId('reopened_by')->nullable()->after('resolved_at')->constrained('users')->nullOnDelete();
            $table->timestamp('reopened_at')->nullable()->after('reopened_by');
        });

        DB::table('insight_editorial_notes')
            ->where('type', 'revision_summary')
            ->update(['type' => 'author_response']);
    }

    public function down(): void
    {
        Schema::table('insight_editorial_notes', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropConstrainedForeignId('revision_id');
            $table->dropConstrainedForeignId('addressed_by');
            $table->dropConstrainedForeignId('resolved_by');
            $table->dropConstrainedForeignId('reopened_by');
            $table->dropColumn(['field_name', 'quoted_text', 'status', 'addressed_at', 'resolved_at', 'reopened_at']);
        });
    }
};
