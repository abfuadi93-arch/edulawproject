<?php

namespace App\Support;

use App\Models\Author;
use App\Models\Insight;
use App\Models\Multimedia;
use App\Models\Program;
use App\Models\Publication;
use Illuminate\Support\Str;

class StructuredData
{
    public static function organization(): array
    {
        return self::clean([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => self::siteUrl().'#organization',
            'name' => config('edulaw.site.name', 'Edulaw Project'),
            'legalName' => config('edulaw.site.legal_name', config('edulaw.site.name', 'Edulaw Project')),
            'url' => self::siteUrl(),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => self::siteAsset('images/logo/edulaw-logo.png'),
            ],
            'email' => config('edulaw.contact.email'),
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => config('edulaw.contact.address_locality'),
                'addressCountry' => config('edulaw.contact.address_country'),
            ],
            'sameAs' => collect([
                config('edulaw.social.instagram_url'),
                config('edulaw.social.linkedin_url'),
                config('edulaw.social.youtube_url'),
            ])->filter(fn ($url): bool => self::isHttpUrl($url))->values()->all(),
        ]);
    }

    public static function website(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => self::siteUrl().'#website',
            'url' => self::siteUrl(),
            'name' => config('edulaw.site.name', 'Edulaw Project'),
            'description' => config('edulaw.site.meta_description'),
            'inLanguage' => 'id-ID',
            'publisher' => self::organizationReference(),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => self::siteUrl().'/search?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /**
     * @param  array<int, array{name: string, url: string}>  $items
     */
    public static function breadcrumbs(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)
                ->values()
                ->map(fn (array $item, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['name'],
                    'item' => $item['url'],
                ])
                ->all(),
        ];
    }

    /**
     * @param  array<int, array{name: string, url: string, image?: ?string}>  $items
     */
    public static function itemList(array $items, string $name): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $name,
            'numberOfItems' => count($items),
            'itemListElement' => collect($items)
                ->values()
                ->map(fn (array $item, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'item' => self::clean([
                        '@type' => 'Thing',
                        'url' => $item['url'],
                        'name' => $item['name'],
                        'image' => $item['image'] ?? null,
                    ]),
                ])
                ->all(),
        ];
    }

    public static function article(Insight $insight): array
    {
        $authors = $insight->authors
            ->filter(fn (Author $author): bool => filled($author->name))
            ->map(fn (Author $author): array => [
                '@type' => 'Person',
                'name' => $author->name,
                'url' => route('profiles.show', $author->slug),
            ])
            ->values()
            ->all();

        return self::clean([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $insight->seo_title ?: $insight->title,
            'description' => self::description($insight->seo_description ?: $insight->excerpt ?: $insight->content),
            'image' => filled($insight->og_image) || filled($insight->cover_image)
                ? [$insight->og_image_url]
                : null,
            'datePublished' => $insight->published_at?->toIso8601String(),
            'dateModified' => $insight->updated_at?->toIso8601String(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => route('insights.show', $insight->slug),
            ],
            'author' => $authors ?: [self::organizationReference()],
            'publisher' => self::publisher(),
            'inLanguage' => 'id-ID',
        ]);
    }

    public static function person(Author $author): array
    {
        return self::clean([
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            '@id' => route('profiles.show', $author->slug).'#person',
            'name' => $author->name,
            'url' => route('profiles.show', $author->slug),
            'image' => $author->photo_url,
            'description' => self::description($author->meta_description ?: $author->bio),
            'jobTitle' => $author->position,
            'affiliation' => filled($author->institution) ? [
                '@type' => 'Organization',
                'name' => $author->institution,
            ] : self::organizationReference(),
            'worksFor' => self::organizationReference(),
            'knowsAbout' => $author->interests ?: null,
            'sameAs' => collect($author->socialLinksMap())
                ->values()
                ->filter(fn ($url): bool => self::isHttpUrl($url))
                ->values()
                ->all(),
        ]);
    }

    public static function event(Program $program): ?array
    {
        if (! $program->event_date) {
            return null;
        }

        $locations = collect();
        $format = Str::lower((string) $program->format);
        $registrationUrl = self::isHttpUrl($program->registration_url) ? $program->registration_url : null;

        if (in_array($format, ['online', 'hybrid'], true) && $registrationUrl) {
            $locations->push([
                '@type' => 'VirtualLocation',
                'url' => $registrationUrl,
            ]);
        }

        if ($format !== 'online' && filled($program->location)) {
            $locations->push([
                '@type' => 'Place',
                'name' => $program->location,
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $program->location,
                    'addressCountry' => config('edulaw.contact.address_country', 'ID'),
                ],
            ]);
        }

        if ($locations->isEmpty()) {
            return null;
        }

        $attendanceMode = match ($format) {
            'online' => 'https://schema.org/OnlineEventAttendanceMode',
            'hybrid' => 'https://schema.org/MixedEventAttendanceMode',
            default => 'https://schema.org/OfflineEventAttendanceMode',
        };

        return self::clean([
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $program->display_title,
            'description' => self::description($program->seo_description ?: $program->display_description),
            'startDate' => $program->event_date->toIso8601String(),
            'endDate' => $program->end_date?->toIso8601String(),
            'eventStatus' => $program->is_archived
                ? 'https://schema.org/EventCompleted'
                : 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => $attendanceMode,
            'location' => $locations->count() === 1 ? $locations->first() : $locations->all(),
            'image' => $program->hero_image_url ? [$program->hero_image_url] : null,
            'url' => route('programs.show', $program->slug),
            'organizer' => self::organizationReference(),
            'inLanguage' => 'id-ID',
        ]);
    }

    public static function publication(Publication $publication): array
    {
        $typeName = Str::lower((string) $publication->type?->name);
        $schemaType = match (true) {
            Str::contains($typeName, ['jurnal', 'journal', 'artikel', 'article']) => 'ScholarlyArticle',
            Str::contains($typeName, ['laporan', 'report', 'riset', 'kajian', 'policy brief']) => 'Report',
            default => 'CreativeWork',
        };

        $authors = $publication->authors
            ->filter(fn (Author $author): bool => filled($author->name))
            ->map(fn (Author $author): array => [
                '@type' => 'Person',
                'name' => $author->name,
                'url' => route('profiles.show', $author->slug),
            ])
            ->values()
            ->all();

        return self::clean([
            '@context' => 'https://schema.org',
            '@type' => $schemaType,
            'name' => $publication->seo_title ?: $publication->title,
            'headline' => $schemaType === 'ScholarlyArticle' ? ($publication->seo_title ?: $publication->title) : null,
            'description' => self::description($publication->seo_description ?: $publication->excerpt ?: $publication->description),
            'url' => route('publications.show', $publication->slug),
            'mainEntityOfPage' => route('publications.show', $publication->slug),
            'image' => $publication->cover_image_url,
            'datePublished' => $publication->published_at?->toIso8601String(),
            'dateModified' => $publication->updated_at?->toIso8601String(),
            'author' => $authors ?: [self::organizationReference()],
            'publisher' => self::publisher(),
            'pagination' => $publication->page_count ? (string) $publication->page_count : null,
            'inLanguage' => 'id-ID',
        ]);
    }

    public static function video(Multimedia $media): ?array
    {
        $youtubeId = self::youtubeId($media->media_url);

        if (! $youtubeId || ! $media->published_at) {
            return null;
        }

        return self::clean([
            '@context' => 'https://schema.org',
            '@type' => 'VideoObject',
            'name' => $media->title,
            'description' => self::description($media->description ?: $media->title),
            'thumbnailUrl' => [
                $media->thumbnail_url ?: "https://i.ytimg.com/vi/{$youtubeId}/hqdefault.jpg",
            ],
            'uploadDate' => $media->published_at->toIso8601String(),
            'duration' => self::isoDuration($media->duration),
            'embedUrl' => "https://www.youtube.com/embed/{$youtubeId}",
            'publisher' => self::organizationReference(),
        ]);
    }

    private static function organizationReference(): array
    {
        return [
            '@type' => 'Organization',
            '@id' => self::siteUrl().'#organization',
            'name' => config('edulaw.site.name', 'Edulaw Project'),
            'url' => self::siteUrl(),
        ];
    }

    private static function publisher(): array
    {
        return [
            '@type' => 'Organization',
            '@id' => self::siteUrl().'#organization',
            'name' => config('edulaw.site.name', 'Edulaw Project'),
            'url' => self::siteUrl(),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => self::siteAsset('images/logo/edulaw-logo.png'),
            ],
        ];
    }

    private static function siteUrl(): string
    {
        return rtrim((string) config('edulaw.site.url', 'https://edulawproject.id'), '/');
    }

    private static function siteAsset(string $path): string
    {
        return self::siteUrl().'/'.ltrim($path, '/');
    }

    private static function description(?string $description): ?string
    {
        $description = Str::squish(strip_tags((string) $description));

        return $description !== '' ? Str::limit($description, 160, '…') : null;
    }

    private static function youtubeId(?string $url): ?string
    {
        if (! self::isHttpUrl($url)) {
            return null;
        }

        preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([A-Za-z0-9_-]{6,})~', (string) $url, $match);

        return $match[1] ?? null;
    }

    private static function isoDuration(?string $duration): ?string
    {
        $duration = trim((string) $duration);

        if ($duration === '') {
            return null;
        }

        if (preg_match('/^P(?:\\d+[YMWD])?(?:T(?:\\d+H)?(?:\\d+M)?(?:\\d+S)?)?$/', $duration)) {
            return $duration;
        }

        $parts = array_map('intval', explode(':', $duration));

        if (count($parts) === 2) {
            [$minutes, $seconds] = $parts;

            return "PT{$minutes}M{$seconds}S";
        }

        if (count($parts) === 3) {
            [$hours, $minutes, $seconds] = $parts;

            return "PT{$hours}H{$minutes}M{$seconds}S";
        }

        return null;
    }

    private static function isHttpUrl(mixed $url): bool
    {
        return is_string($url)
            && Str::startsWith($url, ['http://', 'https://'])
            && filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    private static function clean(array $data): array
    {
        return collect($data)
            ->reject(fn ($value): bool => $value === null || $value === '' || $value === [])
            ->map(function ($value) {
                if (is_array($value) && ! array_is_list($value)) {
                    return self::clean($value);
                }

                return $value;
            })
            ->all();
    }
}
