<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            if (! Schema::hasColumn('publications', 'citation_text')) {
                $table->text('citation_text')->nullable()->after('source_name');
            }

            if (! Schema::hasColumn('publications', 'share_title')) {
                $table->string('share_title')->nullable()->after('og_image');
            }

            if (! Schema::hasColumn('publications', 'share_description')) {
                $table->text('share_description')->nullable()->after('share_title');
            }

            if (! Schema::hasColumn('publications', 'language')) {
                $table->string('language', 10)->nullable()->default('id')->after('citation_text');
            }
        });
    }

    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $columns = collect([
                'citation_text',
                'share_title',
                'share_description',
                'language',
            ])->filter(fn (string $column): bool => Schema::hasColumn('publications', $column))->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
