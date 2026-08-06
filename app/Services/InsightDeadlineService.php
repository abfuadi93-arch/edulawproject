<?php

namespace App\Services;

use App\Models\Insight;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InsightDeadlineService
{
    public function setEditorDeadline(Insight $insight, User $actor, Carbon|string $deadline): Insight
    {
        $this->authorizeManager($actor, 'set_editor_deadline');

        return $this->setDeadline($insight, 'editor', $deadline);
    }

    public function setWriterDeadline(Insight $insight, User $actor, Carbon|string $deadline): Insight
    {
        $this->authorizeAssignedEditorOrManager($insight, $actor, 'set_writer_deadline');

        return $this->setDeadline($insight, 'writer', $deadline);
    }

    public function extendEditorDeadline(Insight $insight, User $actor, Carbon|string $deadline, string $reason): Insight
    {
        $this->authorizeManager($actor, 'extend_editor_deadline');

        return $this->extendDeadline($insight, 'editor', $deadline, $reason);
    }

    public function extendWriterDeadline(Insight $insight, User $actor, Carbon|string $deadline, string $reason): Insight
    {
        $this->authorizeAssignedEditorOrManager($insight, $actor, 'extend_writer_deadline');

        return $this->extendDeadline($insight, 'writer', $deadline, $reason);
    }

    public function completeEditorDeadline(Insight $insight): Insight
    {
        return $this->completeDeadline($insight, 'editor');
    }

    public function completeWriterDeadline(Insight $insight): Insight
    {
        return $this->completeDeadline($insight, 'writer');
    }

    public function isOverdue(Insight $insight, string $owner): bool
    {
        $this->assertOwner($owner);
        $deadline = $insight->getAttribute("{$owner}_deadline");
        $completedAt = $insight->getAttribute("{$owner}_deadline_completed_at");

        return filled($deadline) && blank($completedAt) && Carbon::parse($deadline)->isPast();
    }

    private function setDeadline(Insight $insight, string $owner, Carbon|string $deadline): Insight
    {
        $this->assertOwner($owner);
        $deadline = Carbon::parse($deadline);

        $insight = DB::transaction(function () use ($insight, $owner, $deadline): Insight {
            $insight->forceFill([
                "{$owner}_deadline" => $deadline,
                "{$owner}_deadline_completed_at" => null,
                'editorial_deadline' => $deadline,
            ])->save();

            return $insight->refresh();
        });

        app(InsightNotificationService::class)->notifyDeadlineChanged($insight, $owner);

        return $insight;
    }

    private function extendDeadline(Insight $insight, string $owner, Carbon|string $deadline, string $reason): Insight
    {
        $this->assertOwner($owner);

        if (blank($reason)) {
            throw ValidationException::withMessages(['reason' => 'Alasan perpanjangan tenggat wajib diisi.']);
        }

        $deadline = Carbon::parse($deadline);
        $counter = "{$owner}_deadline_extension_count";

        $insight = DB::transaction(function () use ($insight, $owner, $deadline, $counter, $reason): Insight {
            $locked = Insight::query()->lockForUpdate()->findOrFail($insight->id);
            $locked->forceFill([
                "{$owner}_deadline" => $deadline,
                "{$owner}_deadline_completed_at" => null,
                $counter => ((int) $locked->getAttribute($counter)) + 1,
                'deadline_extension_note' => trim($reason),
                'editorial_deadline' => $deadline,
            ])->save();

            return $locked->refresh();
        });

        app(InsightNotificationService::class)->notifyDeadlineChanged($insight, $owner);

        return $insight;
    }

    private function completeDeadline(Insight $insight, string $owner): Insight
    {
        $this->assertOwner($owner);

        return DB::transaction(function () use ($insight, $owner): Insight {
            $insight->forceFill(["{$owner}_deadline_completed_at" => $insight->getAttribute("{$owner}_deadline_completed_at") ?: now()])->save();

            return $insight->refresh();
        });
    }

    private function authorizeManager(User $actor, string $permission): void
    {
        if (! $actor->can($permission) || ! $actor->hasRole('super_admin')) {
            throw new AuthorizationException("Izin {$permission} untuk Super Admin diperlukan.");
        }
    }

    private function authorizeAssignedEditorOrManager(Insight $insight, User $actor, string $permission): void
    {
        if (! $actor->can($permission)) {
            throw new AuthorizationException("Izin {$permission} diperlukan.");
        }

        $insight->loadMissing('activeEditorAssignment');

        if (! $actor->hasRole('super_admin') && (int) $insight->activeEditorAssignment?->editor_id !== (int) $actor->id) {
            throw new AuthorizationException('Editor hanya dapat mengubah tenggat Writer pada naskah yang ditugaskan.');
        }
    }

    private function assertOwner(string $owner): void
    {
        if (! in_array($owner, ['editor', 'writer'], true)) {
            throw new \InvalidArgumentException('Pemilik tenggat harus editor atau writer.');
        }
    }
}
