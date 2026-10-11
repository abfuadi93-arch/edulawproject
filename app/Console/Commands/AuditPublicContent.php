<?php

namespace App\Console\Commands;

use App\Models\Insight;
use App\Models\Multimedia;
use App\Models\Program;
use App\Models\Publication;
use App\Support\PublicContentIndexability;
use App\Support\PublicContentQuality;
use Illuminate\Console\Command;

class AuditPublicContent extends Command
{
    protected $signature = 'edulaw:audit-public-content {--json : Output machine-readable editorial findings}';

    protected $description = 'Read-only audit of published content requiring editorial review; not an AdSense eligibility test.';

    public function handle(): int
    {
        $rows = collect();
        $totals = [];

        foreach ([Insight::class, Publication::class, Program::class, Multimedia::class] as $model) {
            $type = strtolower(class_basename($model));
            $totals[$type] = 0;
            $query = $model::query();
            $query = $type === 'program' ? $query->visible() : $query->published();

            foreach ($query->lazyById(100) as $record) {
                $totals[$type]++;
                $text = match ($type) {
                    'insight' => $record->content,
                    'publication' => PublicContentIndexability::publicationSummary($record),
                    default => $record->description,
                };
                $words = PublicContentQuality::wordCount($text);
                $reasons = [];
                if (! PublicContentQuality::{$type}($record)) {
                    $reasons[] = $words === 0
                        ? 'Isi atau ringkasan belum tersedia.'
                        : 'Tinjau manfaat dan kelengkapan isi; belum memenuhi pedoman editorial internal.';
                }
                if ($type === 'insight' && ! $record->authors()->exists()) {
                    $reasons[] = 'Belum memiliki penulis yang tercatat.';
                }
                if ($reasons === []) {
                    continue;
                }
                $indexable = match ($type) {
                    'publication', 'program' => PublicContentIndexability::{$type}($record),
                    default => PublicContentQuality::{$type}($record),
                };
                $rows->push([
                    'type' => $type,
                    'id' => $record->getKey(),
                    'slug' => $record->slug,
                    'title' => $record->title ?? $record->name,
                    'words' => $words,
                    // Only detail pages supported by this application have indexing eligibility.
                    'indexable' => $type === 'multimedia' ? ($record->watch_url ? $indexable : null) : $indexable,
                    'reasons' => $reasons,
                ]);
            }
        }

        if ($this->option('json')) {
            $this->line(json_encode(['totals' => $totals, 'review_count' => $rows->count(), 'findings' => $rows->all()], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        } else {
            $this->warn('Pedoman internal, bukan syarat jumlah kata Google atau jaminan persetujuan AdSense. Data tidak diubah.');
            $this->table(['Jenis', 'Publik'], collect($totals)->map(fn ($count, $type) => [$type, $count])->all());
            $this->table(['Jenis / ID', 'Slug', 'Kata', 'Tinjauan editorial'], $rows->map(fn ($row) => [
                $row['type'].' / '.$row['id'], $row['slug'], $row['words'], implode(' ', $row['reasons']),
            ])->all());
            $this->info($rows->count().' konten perlu ditinjau. Isi dari sumber asli; hindari penambahan teks pengisi.');
        }

        return self::SUCCESS;
    }
}
