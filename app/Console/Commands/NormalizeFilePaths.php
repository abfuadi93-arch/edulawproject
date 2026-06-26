<?php

namespace App\Console\Commands;

use App\Models\Author;
use App\Models\CollaborationSubmission;
use App\Models\Insight;
use App\Models\Multimedia;
use App\Models\Opportunity;
use App\Models\Program;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NormalizeFilePaths extends Command
{
    protected $signature = 'edulaw:normalize-file-paths';

    protected $description = 'Normalize legacy upload paths to public disk relative paths without deleting files.';

    /**
     * @var array<int, array{class-string<Model>, string, string}>
     */
    private const FIELDS = [
        [User::class, 'users', 'avatar'],
        [Author::class, 'authors', 'photo'],
        [Insight::class, 'insights', 'cover_image'],
        [Insight::class, 'insights', 'og_image'],
        [Publication::class, 'publications', 'cover_image'],
        [Publication::class, 'publications', 'pdf_file'],
        [Publication::class, 'publications', 'og_image'],
        [Program::class, 'programs', 'image'],
        [Program::class, 'programs', 'og_image'],
        [Multimedia::class, 'multimedia', 'thumbnail'],
        [Opportunity::class, 'opportunities', 'poster'],
        [Opportunity::class, 'opportunities', 'og_image'],
        [CollaborationSubmission::class, 'collaboration_submissions', 'attachment'],
    ];

    public function handle(): int
    {
        $checked = 0;
        $normalized = 0;
        $warnings = 0;

        foreach (self::FIELDS as [$modelClass, $table, $field]) {
            $modelClass::query()
                ->whereNotNull($field)
                ->orderBy('id')
                ->each(function (Model $record) use ($table, $field, &$checked, &$normalized, &$warnings): void {
                    $checked++;
                    $current = $record->getAttribute($field);

                    if (! is_string($current) || trim($current) === '') {
                        return;
                    }

                    $result = $this->normalizePath($current);

                    if ($result['warning'] !== null) {
                        $warnings++;
                        $this->warn("Skipped {$table}.{$field} ID {$record->getKey()}: {$result['warning']}");

                        return;
                    }

                    $next = $result['path'];

                    if ($next === $current) {
                        return;
                    }

                    $record->forceFill([$field => $next])->saveQuietly();
                    $normalized++;

                    $this->line("Normalized {$table}.{$field} ID {$record->getKey()}: {$current} -> {$next}");
                });
        }

        $this->newLine();
        $this->info("Checked fields: {$checked}; normalized: {$normalized}; warnings: {$warnings}");

        return self::SUCCESS;
    }

    /**
     * @return array{path: string, warning: ?string}
     */
    private function normalizePath(string $path): array
    {
        $path = trim(str_replace('\\', '/', $path));

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return ['path' => $path, 'warning' => null];
        }

        $trimmedPath = ltrim($path, '/');

        if (str_starts_with($path, '/') && ! Str::startsWith($trimmedPath, ['public/', 'storage/'])) {
            return [
                'path' => $path,
                'warning' => 'absolute path detected; check this file manually.',
            ];
        }

        foreach (['public/', 'storage/'] as $prefix) {
            while (str_starts_with($trimmedPath, $prefix)) {
                $trimmedPath = substr($trimmedPath, strlen($prefix));
            }
        }

        return ['path' => $trimmedPath, 'warning' => null];
    }
}
