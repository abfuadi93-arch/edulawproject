<?php

namespace App\Console\Commands;

use App\Enums\EditorAssignmentStatus;
use App\Enums\InsightStatus;
use App\Models\Insight;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillEditorialAssignments extends Command
{
    protected $signature = 'editorial:backfill-assignments {--dry-run : Tampilkan rencana tanpa menulis data}';

    protected $description = 'Backfill record assignment dari kolom legacy Insight secara aman dan idempoten.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $summary = ['eligible' => 0, 'migrated' => 0, 'skipped' => 0, 'invalid_editor' => 0];

        Insight::query()
            ->whereNotNull('assigned_editor_id')
            ->with('assignedEditor:id,is_active')
            ->orderBy('id')
            ->chunkById(100, function ($insights) use ($dryRun, &$summary): void {
                foreach ($insights as $insight) {
                    $summary['eligible']++;

                    if (! $insight->assignedEditor) {
                        $summary['invalid_editor']++;
                        $this->warn("Insight #{$insight->id} dilewati: Editor legacy tidak ditemukan.");

                        continue;
                    }

                    if ($insight->editorAssignments()->where('editor_id', $insight->assigned_editor_id)->exists()) {
                        $summary['skipped']++;

                        continue;
                    }

                    if ($insight->editorAssignments()->active()->exists()) {
                        $summary['skipped']++;
                        $this->warn("Insight #{$insight->id} dilewati: sudah memiliki assignment aktif dari mekanisme baru.");

                        continue;
                    }

                    if ($dryRun) {
                        $summary['migrated']++;

                        continue;
                    }

                    $migrated = DB::transaction(function () use ($insight): bool {
                        $locked = Insight::query()->lockForUpdate()->findOrFail($insight->id);

                        if ($locked->editorAssignments()->where('editor_id', $locked->assigned_editor_id)->exists()
                            || $locked->editorAssignments()->active()->exists()) {
                            return false;
                        }

                        $assignmentStatus = $this->assignmentStatusFor($locked);
                        $assignedAt = $locked->assigned_at ?: $locked->updated_at;

                        $locked->editorAssignments()->create([
                            'editor_id' => $locked->assigned_editor_id,
                            'assigned_by' => $locked->assigned_by,
                            'workflow_stage' => $locked->workflow_stage,
                            'status' => $assignmentStatus,
                            'assigned_at' => $assignedAt,
                            'started_at' => $assignmentStatus === EditorAssignmentStatus::Active ? ($locked->review_started_at ?: $assignedAt) : null,
                            'completed_at' => $assignmentStatus === EditorAssignmentStatus::Completed ? ($locked->reviewed_at ?: $locked->updated_at) : null,
                            'due_at' => $locked->editor_deadline ?: $locked->editorial_deadline,
                            'assignment_note' => 'Migrated legacy data; assigned_at memakai updated_at bila timestamp penugasan lama tidak tersedia.',
                        ]);

                        return true;
                    });

                    $summary[$migrated ? 'migrated' : 'skipped']++;
                }
            });

        $this->table(['Eligible', 'Migrated', 'Skipped', 'Invalid Editor'], [[
            $summary['eligible'],
            $summary['migrated'],
            $summary['skipped'],
            $summary['invalid_editor'],
        ]]);

        if ($dryRun) {
            $this->info('Dry run selesai; tidak ada data yang ditulis.');
        }

        return self::SUCCESS;
    }

    private function assignmentStatusFor(Insight $insight): EditorAssignmentStatus
    {
        return match ($insight->status) {
            InsightStatus::EditorAssigned => EditorAssignmentStatus::Assigned,
            InsightStatus::InReview, InsightStatus::RevisionRequested, InsightStatus::Revised => EditorAssignmentStatus::Active,
            default => EditorAssignmentStatus::Completed,
        };
    }
}
