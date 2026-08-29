<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publications', function (Blueprint $table): void {
            if (! Schema::hasColumn('publications', 'research_questions')) {
                $table->json('research_questions')->nullable();
            }

            if (! Schema::hasColumn('publications', 'key_findings')) {
                $table->json('key_findings')->nullable();
            }

            if (! Schema::hasColumn('publications', 'methodology')) {
                $table->longText('methodology')->nullable();
            }

            if (! Schema::hasColumn('publications', 'contribution')) {
                $table->longText('contribution')->nullable();
            }

            if (! Schema::hasColumn('publications', 'implications')) {
                $table->longText('implications')->nullable();
            }
        });

        Schema::table('opportunities', function (Blueprint $table): void {
            if (! Schema::hasColumn('opportunities', 'organizer')) {
                $table->string('organizer')->nullable();
            }
        });

        Schema::table('authors', function (Blueprint $table): void {
            if (! Schema::hasColumn('authors', 'joined_at')) {
                $table->date('joined_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table): void {
            $columns = collect([
                'research_questions',
                'key_findings',
                'methodology',
                'contribution',
                'implications',
            ])->filter(fn (string $column): bool => Schema::hasColumn('publications', $column))->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('opportunities', function (Blueprint $table): void {
            if (Schema::hasColumn('opportunities', 'organizer')) {
                $table->dropColumn('organizer');
            }
        });

        Schema::table('authors', function (Blueprint $table): void {
            if (Schema::hasColumn('authors', 'joined_at')) {
                $table->dropColumn('joined_at');
            }
        });
    }
};
