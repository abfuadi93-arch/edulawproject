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
        'url' => url('/'),
        'lastmod' => null,
        'changefreq' => 'daily',
        'priority' => '1.0',
    ],
    [
        'url' => url('/insight'),
        'lastmod' => null,
        'changefreq' => 'daily',
        'priority' => '0.9',
    ],
    [
        'url' => url('/riset-publikasi'),
        'lastmod' => null,
        'changefreq' => 'weekly',
        'priority' => '0.9',
    ],
    [
        'url' => url('/program'),
        'lastmod' => null,
        'changefreq' => 'weekly',
        'priority' => '0.8',
    ],
    [
        'url' => url('/opportunities'),
        'lastmod' => null,
        'changefreq' => 'daily',
        'priority' => '0.8',
    ],
    [
        'url' => url('/multimedia'),
        'lastmod' => null,
        'changefreq' => 'weekly',
        'priority' => '0.7',
    ],
    [
        'url' => url('/tentang'),
        'lastmod' => null,
        'changefreq' => 'monthly',
        'priority' => '0.7',
    ],
    [
        'url' => url('/kolaborasi'),
        'lastmod' => null,
        'changefreq' => 'monthly',
        'priority' => '0.6',
    ],
    [
        'url' => url('/kontak'),
        'lastmod' => null,
        'changefreq' => 'monthly',
        'priority' => '0.5',
    ],
]);

        $insights = Insight::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
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
            ->where('status', 'published')
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
            ->whereIn('status', ['upcoming', 'ongoing'])
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
            ->where('is_active', true)
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
