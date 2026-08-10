<?php

namespace App\Filament\RichEditor\Extensions;

use Tiptap\Core\Node;
use Tiptap\Utils\HTML;

class FootnoteExtension extends Node
{
    /** @var string */
    public static $name = 'footnote';

    /** @return array<string, mixed> */
    public function addOptions(): array
    {
        return ['HTMLAttributes' => []];
    }

    /** @return array<array<string, mixed>> */
    public function parseHTML(): array
    {
        return [['tag' => 'sup[data-footnote-id]']];
    }

    /** @return array<string, array<string, mixed>> */
    public function addAttributes(): array
    {
        return [
            'id' => [
                'parseHTML' => fn ($node) => $node->getAttribute('data-footnote-id') ?: null,
                'renderHTML' => fn ($attributes) => ['data-footnote-id' => $attributes->id ?? null],
            ],
            'number' => [
                'default' => 1,
                'parseHTML' => fn ($node) => (int) ($node->getAttribute('data-footnote-number') ?: 1),
                'renderHTML' => fn ($attributes) => ['data-footnote-number' => (int) ($attributes->number ?? 1)],
            ],
            'content' => [
                'default' => null,
                'parseHTML' => fn ($node) => $node->getAttribute('data-footnote-content') ?: null,
                'renderHTML' => fn ($attributes) => filled($attributes->content ?? null)
                    ? ['data-footnote-content' => $attributes->content]
                    : [],
            ],
        ];
    }

    public function renderText($node): string
    {
        return (string) ($node->attrs->number ?? 1);
    }

    /** @return array<mixed> */
    public function renderHTML($node, $HTMLAttributes = []): array
    {
        $number = (string) ($node->attrs->number ?? 1);
        $node->content = [(object) ['type' => 'text', 'text' => $number]];

        return [
            'sup',
            HTML::mergeAttributes(
                ['class' => 'edulaw-footnote-ref'],
                $this->options['HTMLAttributes'],
                $HTMLAttributes,
            ),
            0,
        ];
    }
}
