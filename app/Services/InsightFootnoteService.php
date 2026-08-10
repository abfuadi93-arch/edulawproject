<?php

namespace App\Services;

use App\Models\Insight;
use App\Models\InsightFootnote;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InsightFootnoteService
{
    private const CONTAINER_ID = 'edulaw-footnote-fragment';

    /**
     * Synchronize database records with the markers that actually remain in the article.
     */
    public function sync(Insight $insight): Collection
    {
        return DB::transaction(function () use ($insight): Collection {
            $insight->refresh();
            [$document, $container] = $this->loadFragment((string) $insight->content);

            $existing = $insight->footnotes()
                ->getQuery()
                ->lockForUpdate()
                ->get()
                ->keyBy(fn (InsightFootnote $footnote): string => Str::lower($footnote->uuid));

            $referencedUuids = [];
            $number = 0;

            foreach ($this->markerNodes($document, $container) as $marker) {
                $uuid = Str::lower(trim($marker->getAttribute('data-footnote-id')));

                if (! Str::isUuid($uuid) || in_array($uuid, $referencedUuids, true)) {
                    $marker->parentNode?->removeChild($marker);

                    continue;
                }

                $footnote = $existing->get($uuid);

                if (! $footnote) {
                    $stagedContent = trim($marker->getAttribute('data-footnote-content'));

                    if ($stagedContent === '') {
                        $marker->parentNode?->removeChild($marker);

                        continue;
                    }

                    $footnote = $insight->footnotes()->create([
                        'uuid' => $uuid,
                        'content' => $stagedContent,
                    ]);
                    $existing->put($uuid, $footnote);
                }

                $referencedUuids[] = $uuid;
                $number++;

                if ((int) $footnote->sort_order !== $number) {
                    $footnote->update(['sort_order' => $number]);
                }

                $this->normalizeMarker($document, $marker, $uuid, $number);
            }

            $orphanedFootnotes = $insight->footnotes();

            if ($referencedUuids !== []) {
                $orphanedFootnotes->whereNotIn('uuid', $referencedUuids);
            }

            $orphanedFootnotes->delete();

            $normalizedContent = $this->innerHtml($document, $container);

            if ($normalizedContent !== (string) $insight->content) {
                $insight->forceFill(['content' => $normalizedContent])->saveQuietly();
            }

            return $insight->footnotes()->get();
        });
    }

    /**
     * Build public references and an ordered list without mutating persisted content.
     *
     * @return array{html: string, footnotes: Collection<int, array{number: int, footnote: InsightFootnote}>}
     */
    public function prepareForPublic(Insight $insight): array
    {
        [$document, $container] = $this->loadFragment((string) $insight->content);
        $footnotes = ($insight->relationLoaded('footnotes') ? $insight->footnotes : $insight->footnotes()->get())
            ->keyBy(fn (InsightFootnote $footnote): string => Str::lower($footnote->uuid));
        $ordered = collect();
        $seen = [];
        $number = 0;

        foreach ($this->markerNodes($document, $container) as $marker) {
            $uuid = Str::lower(trim($marker->getAttribute('data-footnote-id')));
            $footnote = $footnotes->get($uuid);

            if (! $footnote || isset($seen[$uuid])) {
                $marker->parentNode?->removeChild($marker);

                continue;
            }

            $number++;
            $seen[$uuid] = true;
            $referenceId = "fnref-{$number}";
            $footnoteId = "fn-{$number}";

            $marker->setAttribute('id', $referenceId);
            $marker->setAttribute('class', 'edulaw-footnote-ref');
            $marker->setAttribute('data-footnote-id', $uuid);
            $marker->setAttribute('data-footnote-number', (string) $number);
            $marker->removeAttribute('data-footnote-content');

            $this->clearChildren($marker);
            $link = $document->createElement('a', (string) $number);
            $link->setAttribute('href', "#{$footnoteId}");
            $link->setAttribute('aria-label', "Lihat catatan kaki {$number}");
            $marker->appendChild($link);

            $ordered->push([
                'number' => $number,
                'footnote' => $footnote,
            ]);
        }

        return [
            'html' => $this->innerHtml($document, $container),
            'footnotes' => $ordered,
        ];
    }

    /** @return array<int, DOMElement> */
    private function markerNodes(DOMDocument $document, DOMElement $container): array
    {
        $nodes = (new DOMXPath($document))->query('.//sup[@data-footnote-id]', $container);

        return $nodes ? iterator_to_array($nodes) : [];
    }

    /** @return array{DOMDocument, DOMElement} */
    private function loadFragment(string $html): array
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $encodedId = htmlspecialchars(self::CONTAINER_ID, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $document->loadHTML(
            '<?xml encoding="UTF-8"><div id="'.$encodedId.'">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $container = $document->getElementById(self::CONTAINER_ID);

        if (! $container instanceof DOMElement) {
            $container = $document->createElement('div');
            $container->setAttribute('id', self::CONTAINER_ID);
            $document->appendChild($container);
        }

        return [$document, $container];
    }

    private function normalizeMarker(DOMDocument $document, DOMElement $marker, string $uuid, int $number): void
    {
        $marker->setAttribute('class', 'edulaw-footnote-ref');
        $marker->setAttribute('data-footnote-id', $uuid);
        $marker->setAttribute('data-footnote-number', (string) $number);
        $marker->removeAttribute('data-footnote-content');
        $this->clearChildren($marker);
        $marker->appendChild($document->createTextNode((string) $number));
    }

    private function clearChildren(DOMNode $node): void
    {
        while ($node->firstChild) {
            $node->removeChild($node->firstChild);
        }
    }

    private function innerHtml(DOMDocument $document, DOMElement $container): string
    {
        $html = '';

        foreach ($container->childNodes as $child) {
            $html .= $document->saveHTML($child);
        }

        return $html;
    }
}
