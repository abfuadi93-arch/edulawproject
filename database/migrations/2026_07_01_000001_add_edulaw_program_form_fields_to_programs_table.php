<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('programs')) {
            return;
        }

        $hadPublicationStatus = Schema::hasColumn('programs', 'publication_status');

        Schema::table('programs', function (Blueprint $table) {
            if (! Schema::hasColumn('programs', 'type')) {
                $table->string('type')->nullable()->after('program_category_id');
            }

            if (! Schema::hasColumn('programs', 'short_title')) {
                $table->string('short_title')->nullable()->after('name');
            }

            if (! Schema::hasColumn('programs', 'subtitle')) {
                $table->string('subtitle')->nullable()->after('short_title');
            }

            if (! Schema::hasColumn('programs', 'duration')) {
                $table->string('duration')->nullable()->after('subtitle');
            }

            if (! Schema::hasColumn('programs', 'description')) {
                $table->longText('description')->nullable()->after('short_description');
            }

            if (! Schema::hasColumn('programs', 'moderator_name')) {
                $table->string('moderator_name')->nullable()->after('speakers');
            }

            if (! Schema::hasColumn('programs', 'moderator_affiliation')) {
                $table->string('moderator_affiliation')->nullable()->after('moderator_name');
            }

            if (! Schema::hasColumn('programs', 'hero_image')) {
                $table->string('hero_image')->nullable()->after('image');
            }

            if (! Schema::hasColumn('programs', 'orientation')) {
                $table->text('orientation')->nullable()->after('description');
            }

            if (! Schema::hasColumn('programs', 'method')) {
                $table->text('method')->nullable()->after('orientation');
            }

            if (! Schema::hasColumn('programs', 'output')) {
                $table->text('output')->nullable()->after('method');
            }

            if (! Schema::hasColumn('programs', 'notes')) {
                $table->text('notes')->nullable()->after('output');
            }

            if (! Schema::hasColumn('programs', 'youtube_url')) {
                $table->string('youtube_url')->nullable()->after('registration_link');
            }

            if (! Schema::hasColumn('programs', 'material_link')) {
                $table->string('material_link')->nullable()->after('youtube_url');
            }

            if (! Schema::hasColumn('programs', 'primary_button_text')) {
                $table->string('primary_button_text')->nullable()->after('material_link');
            }

            if (! Schema::hasColumn('programs', 'primary_button_url')) {
                $table->string('primary_button_url')->nullable()->after('primary_button_text');
            }

            if (! Schema::hasColumn('programs', 'secondary_button_text')) {
                $table->string('secondary_button_text')->nullable()->after('primary_button_url');
            }

            if (! Schema::hasColumn('programs', 'secondary_button_url')) {
                $table->string('secondary_button_url')->nullable()->after('secondary_button_text');
            }

            if (! Schema::hasColumn('programs', 'publication_status')) {
                $table->string('publication_status')->default('draft')->index()->after('status');
            }

            if (! Schema::hasColumn('programs', 'show_on_homepage')) {
                $table->boolean('show_on_homepage')->default(false)->index()->after('featured');
            }
        });

        if (! $hadPublicationStatus && Schema::hasColumn('programs', 'publication_status')) {
            DB::table('programs')
                ->whereIn('status', ['upcoming', 'ongoing', 'archived', 'completed', 'portfolio'])
                ->update(['publication_status' => 'published']);
        }
    }

    public function down(): void
    {
        // Keep existing production content intact on rollback. These columns are additive
        // and may already contain live program data by the time this migration is rolled back.
    }
};
