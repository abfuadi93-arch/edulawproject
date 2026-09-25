<?php

namespace App\Support;

use App\Models\Author;
use App\Models\Program;
use App\Models\Publication;
use Illuminate\Support\Facades\Storage;

/**
 * Content eligibility for already-public records. Controllers and sitemap must
 * still apply published/visible/publicProfile scopes (including scheduled dates).
 * Editorial completeness is a separate recommendation, not a Google word limit.
 */
final class PublicContentIndexability
{
    public static function publication(Publication $publication): bool
    {
        $summary = self::publicationSummary($publication);

        return filled($publication->title) && filled($publication->slug)
            && self::hasContent($summary, $publication->title)
            && (PublicContentQuality::publication($publication)
                || (($publication->published_at !== null || self::hasContent($publication->publication_date_text))
                    && (self::httpUrl($publication->external_url) || self::hasPublicPdf($publication))));
    }

    public static function program(Program $program): bool
    {
        $details = collect([$program->orientation, $program->method, $program->output])
            ->filter(fn ($text): bool => self::hasContent($text, $program->name))
            ->unique()->count();
        $learningPoints = collect($program->learning_points ?? [])
            ->map(fn ($point) => is_array($point) ? ($point['item'] ?? $point['text'] ?? $point['value'] ?? $point['point'] ?? null) : $point)
            ->filter(fn ($point): bool => self::hasContent($point, $program->name))
            ->unique()->count();

        return filled($program->name) && filled($program->slug)
            && self::hasContent($program->description, $program->name)
            && (PublicContentQuality::program($program)
                || $details >= 2
                || ($learningPoints >= 2 && $program->event_date !== null)
                || ($program->event_date !== null
                    && self::hasContent($program->organizer_name)
                    && (self::hasContent($program->location) || self::httpUrl($program->online_url))));
    }

    public static function author(Author $author, int $insightCount, int $publicationCount): bool
    {
        return filled($author->name) && filled($author->slug)
            && ($insightCount + $publicationCount) > 0
            && (self::hasContent($author->bio, $author->name)
                || (blank($author->bio) && ($insightCount + $publicationCount) >= 3));
    }

    private static function hasContent(mixed $value, ?string $title = null): bool
    {
        $text = preg_replace('~<(script|style)\b[^>]*>.*?</\1>~is', '', (string) $value);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace('/\s+/u', ' ', $text));

        return preg_match('/[\p{L}\p{N}]/u', $text) === 1
            && mb_strtolower($text) !== mb_strtolower(trim((string) $title))
            && preg_match('/\b(lorem ipsum|coming soon|segera hadir|belum (tersedia|lengkap)|sedang (disiapkan|disusun)|placeholder|tulis di sini|publikasi edulaw project untuk mendukung literasi hukum|riset kebijakan, dan penguatan pengetahuan publik)\b/iu', $text) !== 1;
    }

    public static function publicationSummary(Publication $publication): ?string
    {
        return PublicContentQuality::wordCount($publication->description) > 0
            ? $publication->description
            : $publication->excerpt;
    }

    private static function hasPublicPdf(Publication $publication): bool
    {
        $path = $publication->pdf_file;

        return is_string($path) && str_starts_with($path, 'publications/')
            && ! str_contains($path, '..') && ! str_contains($path, '\\')
            && strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf'
            && Storage::disk('public')->exists($path);
    }

    private static function httpUrl(mixed $value): bool
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_URL) !== false
            && in_array(strtolower((string) parse_url($value, PHP_URL_SCHEME)), ['http', 'https'], true);
    }
}
