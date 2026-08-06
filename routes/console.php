<?php

use App\Models\Insight;
use App\Models\Publication;
use App\Models\User;
use App\Services\InsightNotificationService;
use App\Services\PdfCoverGenerator;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('insights:notify-deadlines', function (InsightNotificationService $notifications) {
    foreach (['editor', 'writer'] as $owner) {
        $deadlineColumn = "{$owner}_deadline";
        $completedColumn = "{$owner}_deadline_completed_at";

        Insight::query()
            ->whereNull($completedColumn)
            ->whereNotNull($deadlineColumn)
            ->where($deadlineColumn, '<=', now()->addDay())
            ->whereNotIn('status', ['approved', 'rejected', 'published', 'archived'])
            ->with(['creator', 'assignedEditor'])
            ->each(function (Insight $insight) use ($notifications, $owner, $deadlineColumn): void {
                if ($insight->getAttribute($deadlineColumn)->isPast()) {
                    $notifications->notifyDeadlineOverdue($insight, $owner);
                } else {
                    $notifications->notifyDeadlineApproaching($insight, $owner);
                }
            });
    }

    $this->info('Notifikasi tenggat editorial telah diproses.');
})->purpose('Send deduplicated approaching and overdue editorial deadline notifications');

Schedule::command('insights:notify-deadlines')->hourly()->withoutOverlapping();

Artisan::command('publications:generate-covers {--force : Regenerate covers even when a publication already has one}', function () {
    $generator = app(PdfCoverGenerator::class);
    $force = (bool) $this->option('force');
    $generated = 0;
    $skipped = 0;
    $failed = 0;

    Publication::query()
        ->whereNotNull('pdf_file')
        ->orderBy('id')
        ->each(function (Publication $publication) use ($generator, $force, &$generated, &$skipped, &$failed): void {
            if (! $force && filled($publication->cover_image)) {
                $skipped++;
                $this->line("Skipped #{$publication->id}: cover already exists.");

                return;
            }

            $coverImage = $generator->generate($publication->pdf_file, $publication->slug ?: $publication->title);

            if ($coverImage === null) {
                $failed++;
                $this->warn("Failed #{$publication->id}: cover could not be generated.");

                return;
            }

            $publication->forceFill([
                'cover_image' => $coverImage,
            ])->save();

            $generated++;
            $this->info("Generated #{$publication->id}: {$coverImage}");
        });

    $this->newLine();
    $this->info("Generated: {$generated}; skipped: {$skipped}; failed: {$failed}");

    return $failed > 0 ? 1 : 0;
})->purpose('Generate publication cover images from the first PDF page');

Artisan::command('users:normalize-avatar-paths {--null-missing : Set avatar to null when the normalized public disk file is missing}', function () {
    $normalizeRelativePath = static function (string $path): ?string {
        $path = trim(str_replace('\\', '/', $path));

        if ($path === '' || Str::startsWith($path, ['http://', 'https://', 'data:', 'javascript:']) || Str::contains($path, '../')) {
            return null;
        }

        $path = preg_replace('/[?#].*$/', '', $path) ?? $path;
        $path = ltrim($path, '/');

        foreach (['public/storage/', 'storage/', 'public/'] as $prefix) {
            while (Str::startsWith($path, $prefix)) {
                $path = Str::after($path, $prefix);
            }
        }

        return filled($path) ? $path : null;
    };

    $normalizeAvatar = static function (?string $value) use ($normalizeRelativePath): ?string {
        $value = filled($value) ? trim($value) : null;

        if (! filled($value)) {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            if (! filter_var($value, FILTER_VALIDATE_URL)) {
                return null;
            }

            $path = parse_url($value, PHP_URL_PATH) ?: '';

            if (Str::startsWith($path, '/storage/')) {
                return $normalizeRelativePath(Str::after($path, '/storage/'));
            }

            return $value;
        }

        return $normalizeRelativePath($value);
    };

    $checked = 0;
    $updated = 0;
    $unchanged = 0;
    $missing = 0;
    $nulled = 0;
    $skipped = 0;

    User::query()
        ->whereNotNull('avatar')
        ->orderBy('id')
        ->each(function (User $user) use ($normalizeAvatar, &$checked, &$updated, &$unchanged, &$missing, &$nulled, &$skipped): void {
            $checked++;
            $current = trim((string) $user->avatar);
            $normalized = $normalizeAvatar($current);

            if (! filled($normalized)) {
                $skipped++;

                return;
            }

            $isPublicDiskPath = ! Str::startsWith($normalized, ['http://', 'https://']);

            if ($isPublicDiskPath && ! Storage::disk('public')->exists($normalized)) {
                $missing++;

                if ($this->option('null-missing')) {
                    $user->forceFill(['avatar' => null])->saveQuietly();
                    $nulled++;
                    $this->warn("Nulled #{$user->id}: {$current}");

                    return;
                }

                $this->warn("Missing file for #{$user->id}: {$normalized}; kept current value.");

                return;
            }

            if ($normalized === $current) {
                $unchanged++;

                return;
            }

            $user->forceFill(['avatar' => $normalized])->saveQuietly();
            $updated++;
            $this->info("Updated #{$user->id}: {$current} -> {$normalized}");
        });

    $this->newLine();
    $this->info("Checked: {$checked}; updated: {$updated}; unchanged: {$unchanged}; missing: {$missing}; nulled: {$nulled}; skipped: {$skipped}");

    return 0;
})->purpose('Normalize legacy user avatar values to public disk relative paths without deleting files');
