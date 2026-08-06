<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insights', function (Blueprint $table): void {
            $table->dateTime('editor_deadline')->nullable()->after('editorial_deadline');
            $table->dateTime('writer_deadline')->nullable()->after('editor_deadline');
            $table->timestamp('editor_deadline_completed_at')->nullable()->after('writer_deadline');
            $table->timestamp('writer_deadline_completed_at')->nullable()->after('editor_deadline_completed_at');
            $table->unsignedInteger('editor_deadline_extension_count')->default(0)->after('writer_deadline_completed_at');
            $table->unsignedInteger('writer_deadline_extension_count')->default(0)->after('editor_deadline_extension_count');
            $table->text('deadline_extension_note')->nullable()->after('writer_deadline_extension_count');

            $table->index(['status', 'editor_deadline'], 'insights_status_editor_deadline_index');
            $table->index(['status', 'writer_deadline'], 'insights_status_writer_deadline_index');
        });
    }

    public function down(): void
    {
        Schema::table('insights', function (Blueprint $table): void {
            $table->dropIndex('insights_status_editor_deadline_index');
            $table->dropIndex('insights_status_writer_deadline_index');
            $table->dropColumn([
                'editor_deadline',
                'writer_deadline',
                'editor_deadline_completed_at',
                'writer_deadline_completed_at',
                'editor_deadline_extension_count',
                'writer_deadline_extension_count',
                'deadline_extension_note',
            ]);
        });
    }
};
