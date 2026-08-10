<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Insight;
use App\Models\Program;
use App\Models\Publication;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Menampilkan sitemap XML utama Edulaw Project.
     */
    public function index(): Response
    {
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
        ]);

        $insights = Insight::query()
            ->published()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->select(['slug', 'updated_at'])
            ->latest('published_at')
            ->get()
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
            ->select(['slug', 'updated_at'])
            ->latest('published_at')
            ->get()
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
            ->select(['slug', 'updated_at'])
            ->latest('updated_at')
            ->get()
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
            ->select(['slug', 'updated_at'])
            ->orderBy('name')
            ->get()
            ->map(fn (Author $author): array => [
                'url' => route('profiles.show', $author->slug),
                'lastmod' => $author->updated_at,
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ]);

        $urls = $staticPages
            ->concat($insights)
            ->concat($publications)
            ->concat($programs)
            ->concat($authors)
            ->unique('url')
            ->values();

        return response()
            ->view('sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
