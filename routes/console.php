<?php

use App\Models\Publication;
use App\Services\PdfCoverGenerator;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

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
