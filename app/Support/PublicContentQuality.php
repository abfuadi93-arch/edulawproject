<?php

namespace App\Support;

use App\Models\Author;
use App\Models\Insight;
use App\Models\Multimedia;
use App\Models\Program;
use App\Models\Publication;

/**
 * Conservative, repository-owned readiness checks for public landing pages.
 *
 * These are editorial safeguards, not thresholds published by Google.
 * Publication, program and profile indexing uses PublicContentIndexability,
 * which also recognizes concise pages supported by documents or structured facts.
 * Insight and multimedia retain these conservative checks for now.
 */
final class PublicContentQuality
{
    public static function insight(Insight $insight): bool
    {
        return filled($insight->title)
            && filled($insight->slug)
            && self::wordCount($insight->content) >= 300;
    }

    public static function publication(Publication $publication): bool
    {
        $summaryWords = self::wordCount($publication->description ?: $publication->excerpt);
        $researchQuestions = self::listItems($publication->research_questions);
        $keyFindings = self::listItems($publication->key_findings);
        $sections = collect([
            $researchQuestions->isNotEmpty() ? $researchQuestions->implode(' ') : null,
            $keyFindings->isNotEmpty() ? $keyFindings->implode(' ') : null,
            $publication->methodology,
            $publication->contribution,
            $publication->implications,
        ])->filter(fn ($value): bool => self::wordCount($value) >= 8);

        $totalWords = self::wordCount(
            collect([$publication->description ?: $publication->excerpt])
                ->merge($sections)
                ->filter()
                ->implode(' ')
        );

        return filled($publication->title)
            && filled($publication->slug)
            && $summaryWords >= 120
            && $sections->count() >= 2
            && $totalWords >= 220;
    }

    public static function program(Program $program): bool
    {
        $descriptionWords = self::wordCount($program->description);
        $learningPoints = self::listItems($program->learning_points);
        $sections = collect([
            $learningPoints->isNotEmpty() ? $learningPoints->implode(' ') : null,
            $program->orientation,
            $program->method,
            $program->output,
        ])->filter(fn ($value): bool => self::wordCount($value) >= 8);

        $totalWords = self::wordCount(
            collect([$program->description, $program->short_description])
                ->merge($sections)
                ->filter()
                ->unique()
                ->implode(' ')
        );

        return filled($program->name)
            && filled($program->slug)
            && (
                $descriptionWords >= 120
                || ($descriptionWords >= 80 && $sections->count() >= 2 && $totalWords >= 130)
            );
    }

    public static function multimedia(Multimedia $multimedia): bool
    {
        return filled($multimedia->title)
            && filled($multimedia->slug)
            && filled($multimedia->youtube_video_id)
            && self::wordCount($multimedia->description) >= 100;
    }

    public static function author(Author $author, int $insightCount, int $publicationCount): bool
    {
        return filled($author->name)
            && filled($author->slug)
            && ($insightCount + $publicationCount) > 0
            && (
                self::wordCount($author->bio) >= 40
                || ($insightCount + $publicationCount) >= 3
            );
    }

    public static function wordCount(mixed $value): int
    {
        $text = (string) $value;
        $text = preg_replace('~<(script|style)\b[^>]*>.*?</\1>~is', ' ', $text) ?? $text;
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', trim($text)) ?? trim($text);

        if ($text === '') {
            return 0;
        }

        preg_match_all("/[\p{L}\p{N}]+(?:[’'-][\p{L}\p{N}]+)*/u", $text, $matches);

        return count($matches[0] ?? []);
    }

    private static function listItems(mixed $value)
    {
        return collect(is_array($value) ? $value : [])
            ->map(fn ($item) => is_array($item)
                ? ($item['item'] ?? $item['text'] ?? $item['value'] ?? $item['point'] ?? null)
                : $item)
            ->map(fn ($item): string => trim(strip_tags((string) $item)))
            ->filter()
            ->values();
    }
}
