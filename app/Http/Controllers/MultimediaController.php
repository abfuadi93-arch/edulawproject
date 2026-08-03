<?php

namespace App\Http\Controllers;

use App\Models\Multimedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class MultimediaController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('q');

        $youtubeQuery = Multimedia::query()
            ->published()
            ->youtubeVideos()
            ->whereNotNull('media_url')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            });

        $hasSortOrder = Schema::hasColumn('multimedia', 'sort_order');

        $featuredYoutubeVideo = (clone $youtubeQuery)
            ->orderByDesc('featured')
            ->when($hasSortOrder, fn ($query) => $query->orderBy('sort_order'))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->first();

        $youtubeVideos = (clone $youtubeQuery)
            ->when($featuredYoutubeVideo, fn ($query) => $query->whereKeyNot($featuredYoutubeVideo->getKey()))
            ->when($hasSortOrder, fn ($query) => $query->orderBy('sort_order'))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(perPage: 6, pageName: 'video_page')
            ->withQueryString()
            ->fragment('video');

        $shortsReels = Multimedia::query()
            ->published()
            ->shortsReels()
            ->whereNotNull('media_url')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $photoAlbums = Multimedia::query()
            ->published()
            ->photoAlbums()
            ->whereNotNull('media_url')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $counts = [
            'youtubeVideos' => $youtubeVideos->total() + ($featuredYoutubeVideo ? 1 : 0),
            'shortsReels' => Multimedia::query()->published()->shortsReels()->whereNotNull('media_url')->count(),
            'photoAlbums' => Multimedia::query()->published()->photoAlbums()->whereNotNull('media_url')->count(),
        ];

        return view('multimedia.index', [
            'youtubeVideos' => $youtubeVideos,
            'featuredYoutubeVideo' => $featuredYoutubeVideo,
            'shortsReels' => $shortsReels,
            'photoAlbums' => $photoAlbums,
            'counts' => $counts,
            'search' => $search,
        ]);
    }
}
