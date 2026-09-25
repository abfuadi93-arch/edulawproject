<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Insight;
use App\Models\InsightCategory;
use App\Models\Multimedia;
use App\Models\Program;
use App\Models\Publication;
use App\Support\PublicContentIndexability;
use App\Support\PublicContentQuality;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Menampilkan sitemap XML utama Edulaw Project.
     */
    public function index(): Response
    {
        $indexableCategorySlugs = InsightCategory::query()
            ->where('is_active', true)
            ->whereIn('slug', ['law-governance', 'legal-101', 'regulatory-update', 'edulaw-insight'])
            ->whereHas('insights', fn ($query) => $query->published())
            ->pluck('slug');

        $staticPages = collect([
            [
                'url' => route('home'),
                'lastmod' => null,
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'url' => route('insights.index'),
                'lastmod' => null,
                'changefreq' => 'daily',
                'priority' => '0.9',
            ],
            ...collect(['law-governance', 'legal-101', 'regulatory-update', 'edulaw-insight'])
                ->filter(fn (string $category): bool => $indexableCategorySlugs->contains($category))
                ->map(fn (string $category): array => [
                    'url' => route('insights.categories.show', $category),
                    'lastmod' => null,
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ])
                ->all(),
            [
                'url' => route('publications.index'),
                'lastmod' => null,
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
            [
                'url' => route('programs.index'),
                'lastmod' => null,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
            [
                'url' => route('opportunities.index'),
                'lastmod' => null,
                'changefreq' => 'daily',
                'priority' => '0.8',
            ],
            [
                'url' => route('multimedia.index'),
                'lastmod' => null,
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ],
            [
                'url' => route('about'),
                'lastmod' => null,
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'url' => route('collaboration.index'),
                'lastmod' => null,
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ],
            [
                'url' => route('contact.index'),
                'lastmod' => null,
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
            [
                'url' => route('editorial-standards'),
                'lastmod' => null,
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ],
            [
                'url' => route('corrections-policy'),
                'lastmod' => null,
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
            [
                'url' => route('privacy'),
                'lastmod' => null,
                'changefreq' => 'yearly',
                'priority' => '0.4',
            ],
            [
                'url' => route('terms'),
                'lastmod' => null,
                'changefreq' => 'yearly',
                'priority' => '0.4',
            ],
        ]);

        $insights = Insight::query()
            ->published()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->select(['title', 'slug', 'content', 'updated_at'])
            ->latest('published_at')
            ->get()
            ->filter(fn (Insight $insight): bool => PublicContentQuality::insight($insight))
            ->map(fn (Insight $insight): array => [
                'url' => route('insights.show', $insight->slug),
                'lastmod' => $insight->updated_at,
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ]);

        $publications = Publication::query()
            ->published()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->latest('published_at')
            ->get()
            ->filter(fn (Publication $publication): bool => PublicContentIndexability::publication($publication))
            ->map(fn (Publication $publication): array => [
                'url' => route('publications.show', $publication->slug),
                'lastmod' => $publication->updated_at,
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ]);

        $programs = Program::query()
            ->visible()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->latest('updated_at')
            ->get()
            ->filter(fn (Program $program): bool => PublicContentIndexability::program($program))
            ->map(fn (Program $program): array => [
                'url' => route('programs.show', $program->slug),
                'lastmod' => $program->updated_at,
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ]);

        $authors = Author::query()
            ->publicProfile()
            ->where('show_in_contributor_section', true)
            ->withPublicContribution()
            ->select(['id', 'name', 'slug', 'bio', 'updated_at'])
            ->withCount([
                'insights as published_insights_count' => fn ($query) => $query->published(),
                'publications as published_publications_count' => fn ($query) => $query->published(),
            ])
            ->orderBy('name')
            ->get()
            ->filter(fn (Author $author): bool => PublicContentIndexability::author(
                $author,
                (int) $author->published_insights_count,
                (int) $author->published_publications_count,
            ))
            ->map(fn (Author $author): array => [
                'url' => route('profiles.show', $author->slug),
                'lastmod' => $author->updated_at,
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ]);

        $videos = Multimedia::query()->published()->youtubeVideos()->get()
            ->filter(fn (Multimedia $video): bool => PublicContentQuality::multimedia($video))
            ->map(fn (Multimedia $video): array => [
                'url' => route('multimedia.show', $video->slug),
                'lastmod' => $video->updated_at,
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ]);

        $urls = $staticPages
            ->concat($insights)
            ->concat($publications)
            ->concat($programs)
            ->concat($authors)
            ->concat($videos)
            ->unique('url')
            ->values();

        return response()
            ->view('sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
