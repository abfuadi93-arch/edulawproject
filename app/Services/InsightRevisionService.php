<?php

namespace App\Services;

use App\Models\Insight;
use App\Models\InsightRevision;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class InsightRevisionService
{
    public function createInitialSnapshot(Insight $insight, User $actor): InsightRevision
    {
        if ((int) $insight->created_by !== (int) $actor->id && ! $actor->hasRole('super_admin')) {
            throw new AuthorizationException('Snapshot awal hanya dapat dibuat oleh pemilik naskah.');
        }

        $existing = $insight->revisions()->where('revision_number', 1)->first();

        return $existing ?: $this->createSnapshot($insight, $actor, 'Pengiriman naskah pertama.');
    }

    public function createRevisionSnapshot(Insight $insight, User $actor, string $summary): InsightRevision
    {
        return $this->createSnapshot($insight, $actor, trim($summary));
    }

    public function hasMeaningfulChanges(Insight $insight, ?InsightRevision $against = null): bool
    {
        $against ??= $insight->revisions()->latest('revision_number')->first();

        if (! $against) {
            return true;
        }

        $current = $this->currentPayload($insight);
        $previous = $this->revisionPayload($against);

        foreach (array_keys($current) as $field) {
            if ($this->normalizeValue($current[$field]) !== $this->normalizeValue($previous[$field])) {
                return true;
            }
        }

        return false;
    }

    public function compare(InsightRevision $older, InsightRevision $newer): array
    {
        if ((int) $older->insight_id !== (int) $newer->insight_id) {
            throw new \InvalidArgumentException('Versi yang dibandingkan harus berasal dari naskah yang sama.');
        }

        $old = $this->revisionPayload($older);
        $new = $this->revisionPayload($newer);

        return collect($old)->mapWithKeys(function (mixed $oldValue, string $field) use ($new): array {
            $newValue = $new[$field] ?? null;
            $normalizedOld = $this->normalizeValue($oldValue);
            $normalizedNew = $this->normalizeValue($newValue);

            return [$field => [
                'old' => $oldValue,
                'new' => $newValue,
                'changed' => $normalizedOld !== $normalizedNew,
                'diff' => $this->simpleDiff($normalizedOld, $normalizedNew),
            ]];
        })->all();
    }

    private function createSnapshot(Insight $insight, User $actor, string $summary): InsightRevision
    {
        return DB::transaction(function () use ($insight, $actor, $summary): InsightRevision {
            $locked = Insight::query()->lockForUpdate()->findOrFail($insight->id);
            $locked->load(['authors:id,name,slug', 'tags:id,name,slug']);
            $nextNumber = ((int) $locked->revisions()->max('revision_number')) + 1;

            return $locked->revisions()->create([
                ...$this->currentPayload($locked),
                'revision_number' => $nextNumber,
                'revision_summary' => $summary,
                'submitted_by' => $actor->id,
                'submitted_at' => now(),
            ]);
        });
    }

    private function currentPayload(Insight $insight): array
    {
        $insight->loadMissing(['authors:id,name,slug', 'tags:id,name,slug']);

        return [
            'title' => $insight->title,
            'excerpt' => $insight->excerpt,
            'content' => $insight->content,
            'cover_image' => $insight->cover_image,
            'insight_category_id' => $insight->insight_category_id,
            'author_snapshot' => $insight->authors->map(fn ($author): array => [
                'id' => $author->id,
                'name' => $author->name,
                'slug' => $author->slug,
                'author_order' => $author->pivot->author_order,
                'role' => $author->pivot->role,
            ])->sortBy('author_order')->values()->all(),
            'tag_snapshot' => $insight->tags->map(fn ($tag): array => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ])->sortBy('name')->values()->all(),
            'seo_title' => $insight->seo_title,
            'seo_description' => $insight->seo_description,
        ];
    }

    private function revisionPayload(InsightRevision $revision): array
    {
        return [
            'title' => $revision->title,
            'excerpt' => $revision->excerpt,
            'content' => $revision->content,
            'cover_image' => $revision->cover_image,
            'insight_category_id' => $revision->insight_category_id,
            'author_snapshot' => $revision->author_snapshot ?? [],
            'tag_snapshot' => $revision->tag_snapshot ?? [],
            'seo_title' => $revision->seo_title,
            'seo_description' => $revision->seo_description,
        ];
    }

    private function normalizeValue(mixed $value): string
    {
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
        }

        $value = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/>\s+</', '><', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    private function simpleDiff(string $old, string $new): array
    {
        $oldWords = preg_split('/\s+/u', $old, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $newWords = preg_split('/\s+/u', $new, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return [
            'removed' => array_values(array_diff($oldWords, $newWords)),
            'added' => array_values(array_diff($newWords, $oldWords)),
        ];
    }
}
