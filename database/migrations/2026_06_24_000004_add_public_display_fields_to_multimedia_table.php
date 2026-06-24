<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('multimedia', function (Blueprint $table) {
            if (! Schema::hasColumn('multimedia', 'photo_count')) {
                $table->unsignedInteger('photo_count')->nullable()->after('duration');
            }

            if (! Schema::hasColumn('multimedia', 'serial')) {
                $table->string('serial')->nullable()->after('photo_count');
            }

            if (! Schema::hasColumn('multimedia', 'topic')) {
                $table->string('topic')->nullable()->after('serial');
            }

            if (! Schema::hasColumn('multimedia', 'display_section')) {
                $table->string('display_section')->default('latest')->after('topic');
            }

            $table->index(['status', 'display_section', 'published_at'], 'multimedia_status_section_published_idx');
            $table->index(['serial', 'status'], 'multimedia_serial_status_idx');
            $table->index(['topic', 'status'], 'multimedia_topic_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('multimedia', function (Blueprint $table) {
            $table->dropIndex('multimedia_status_section_published_idx');
            $table->dropIndex('multimedia_serial_status_idx');
            $table->dropIndex('multimedia_topic_status_idx');

            $table->dropColumn([
                'photo_count',
                'serial',
                'topic',
                'display_section',
            ]);
        });
    }
};
