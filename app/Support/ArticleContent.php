<?php

namespace App\Support;

use Illuminate\Support\Str;

class ArticleContent
{
    /**
     * Prepare rich-editor HTML for public article rendering without mutating
     * the stored content.
     *
     * @return array{html: string, headings: array<int, array{level: int, id: string, title: string}>}
     */
    public static function prepare(?string $html): array
    {
        if (blank($html)) {
            return [
                'html' => '',
                'headings' => [],
            ];
        }

        $headings = [];
        $usedIds = [];
        $fallbackPosition = 0;

        $preparedHtml = preg_replace_callback(
            '/<h([1-4])([^>]*)>(.*?)<\/h\1\s*>/is',
            function (array $matches) use (&$headings, &$usedIds, &$fallbackPosition): string {
                $originalLevel = (int) $matches[1];
                $level = $originalLevel === 1 ? 2 : $originalLevel;
                $attributes = $matches[2];
                $innerHtml = $matches[3];
                $title = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($innerHtml), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');

                preg_match('/\sid\s*=\s*(["\'])(.*?)\1/i', $attributes, $idMatch);
                $attributes = preg_replace('/\s+id\s*=\s*(["\']).*?\1/i', '', $attributes) ?? $attributes;

                $fallbackPosition++;
                $baseId = Str::slug($idMatch[2] ?? $title);
                $baseId = $baseId !== '' ? $baseId : "bagian-{$fallbackPosition}";
                $id = $baseId;
                $suffix = 2;

                while (isset($usedIds[$id])) {
                    $id = "{$baseId}-{$suffix}";
                    $suffix++;
                }

                $usedIds[$id] = true;

                if ($title !== '' && $level <= 3) {
                    $headings[] = [
                        'level' => $level,
                        'id' => $id,
                        'title' => $title,
                    ];
                }

                return sprintf(
                    '<h%d%s id="%s">%s</h%d>',
                    $level,
                    $attributes,
                    e($id),
                    $innerHtml,
                    $level,
                );
            },
            $html,
        );

        return [
            'html' => $preparedHtml ?? $html,
            'headings' => $headings,
        ];
    }
}
