<?php

namespace App\Console\Commands;

use App\Models\Multimedia;
use App\Models\Program;
use App\Support\PublicContentQuality;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EnrichDiksiContent extends Command
{
    protected $signature = 'edulaw:enrich-diksi {--apply : Save updates to matching public records}';

    protected $description = 'Preview or apply sourced DIKSI program and video descriptions';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        DB::transaction(function () use ($apply): void {
            $query = Multimedia::query()->published()->youtubeVideos();
            $videos = ($apply ? $query->lockForUpdate() : $query)->get()->groupBy('youtube_video_id');

            foreach (config('diksi-editorial') as $id => $entry) {
                $series = $entry['series'];
                $matches = $videos->get($id, collect());
                if ($matches->count() === 1) {
                    $this->enrich($matches->first(), implode("\n\n", $entry['video']), "Video DIKSI {$series}", $apply);
                } else {
                    $this->warn("Video DIKSI {$series}: tidak ditemukan atau ID ganda; dilewati.");
                }

                if (isset($entry['program_slug'])) {
                    $query = Program::query()->visible()->where('slug', $entry['program_slug']);
                    $program = ($apply ? $query->lockForUpdate() : $query)->first();

                    if (! $program) {
                        $this->warn("Program DIKSI {$series}: tidak ditemukan; dilewati.");

                        continue;
                    }

                    $paragraphs = array_map(fn ($paragraph) => '<p>'.e($paragraph).'</p>', [
                        $entry['program'],
                        ...array_slice($entry['video'], 0, 2),
                    ]);
                    $paragraphs[] = '<p><strong>Sumber:</strong> <a href="'.e($entry['source']).'">Liputan resmi Mahkamah Konstitusi RI</a> · <a href="https://www.youtube.com/watch?v='.e($id).'">Rekaman diskusi di YouTube</a>.</p>';
                    $this->enrich($program, implode("\n", $paragraphs), "Program DIKSI {$series}", $apply);
                }
            }
        });

        return self::SUCCESS;
    }

    private function enrich(Model $record, string $description, string $label, bool $apply): void
    {
        $existing = (string) $record->getRawOriginal('description');
        if ($existing === $description || PublicContentQuality::wordCount($existing) >= PublicContentQuality::wordCount($description)) {
            $this->line("{$label}: ringkasan saat ini dipertahankan.");

            return;
        }

        if ($apply) {
            $record->update(['description' => $description]);
        }

        $this->info($label.($apply ? ': diperbarui.' : ': dapat diperbarui (--apply).'));
    }
}
