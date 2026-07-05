<?php

namespace App\Http\Controllers;

use App\Models\Insight;
use App\Models\Multimedia;
use App\Models\Opportunity;
use App\Models\Program;
use App\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $results = collect();

        if ($query !== '') {
            $insights = Insight::query()
                ->published()
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('excerpt', 'like', "%{$query}%")
                        ->orWhere('content', 'like', "%{$query}%");
                })
                ->orderByDesc('published_at')
                ->limit(8)
                ->get()
                ->map(fn (Insight $item) => [
                    'type' => 'Editorial',
                    'title' => $item->title,
                    'excerpt' => $item->excerpt,
                    'date' => optional($item->published_at)->translatedFormat('d M Y'),
                    'url' => route('insights.show', $item->slug),
                    'meta' => $item->reading_time ? $item->reading_time.' min read' : 'Editorial',
                    'sort_date' => optional($item->published_at)?->timestamp ?? 0,
                ]);

            $publications = Publication::query()
                ->where('status', 'published')
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('excerpt', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                })
                ->orderByDesc('published_at')
                ->limit(8)
                ->get()
                ->map(fn (Publication $item) => [
                    'type' => 'Riset & Publikasi',
                    'title' => $item->title,
                    'excerpt' => $item->excerpt,
                    'date' => optional($item->published_at)->translatedFormat('d M Y'),
                    'url' => route('publications.show', $item->slug),
                    'meta' => $item->type?->name ?? 'Publikasi',
                    'sort_date' => optional($item->published_at)?->timestamp ?? 0,
                ]);

            $programs = Program::query()
                ->published()
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('short_description', 'like', "%{$query}%");
                })
                ->orderByDesc('event_date')
                ->limit(8)
                ->get()
                ->map(fn (Program $item) => [
                    'type' => 'Program',
                    'title' => $item->name,
                    'excerpt' => $item->short_description,
                    'date' => optional($item->event_date)->translatedFormat('d M Y'),
                    'url' => route('programs.show', $item->slug),
                    'meta' => $item->display_format ?? 'Program',
                    'sort_date' => optional($item->event_date)?->timestamp ?? 0,
                ]);

            $opportunities = Opportunity::query()
                ->whereIn('status', ['open', 'closed'])
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('excerpt', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                })
                ->orderBy('deadline')
                ->limit(8)
                ->get()
                ->map(fn (Opportunity $item) => [
                    'type' => 'Opportunities',
                    'title' => $item->title,
                    'excerpt' => $item->excerpt,
                    'date' => optional($item->deadline)->translatedFormat('d M Y'),
                    'url' => $item->application_link ?: route('opportunities.index'),
                    'meta' => ucfirst(str_replace('_', ' ', (string) $item->type)),
                    'external' => filled($item->application_link),
                    'sort_date' => optional($item->deadline)?->timestamp ?? 0,
                ]);

            $multimedia = Multimedia::query()
                ->published()
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                })
                ->orderByDesc('published_at')
                ->limit(8)
                ->get()
                ->map(fn (Multimedia $item) => [
                    'type' => 'Multimedia',
                    'title' => $item->title,
                    'excerpt' => $item->description,
                    'date' => optional($item->published_at)->translatedFormat('d M Y'),
                    'url' => $item->media_url ?: route('multimedia.index'),
                    'meta' => ucfirst((string) $item->platform),
                    'external' => filled($item->media_url),
                    'sort_date' => optional($item->published_at)?->timestamp ?? 0,
                ]);

            $results = collect()
                ->merge($insights)
                ->merge($publications)
                ->merge($programs)
                ->merge($opportunities)
                ->merge($multimedia)
                ->sortByDesc('sort_date')
                ->values();
        }

        return view('search.index', [
            'query' => $query,
            'results' => $results,
        ]);
    }
}
