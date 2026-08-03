<?php

namespace App\Http\Controllers;

use App\Models\Multimedia;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MultimediaController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('q');

        $youtubeVideos = Multimedia::query()
            ->published()
            ->youtubeVideos()
            ->whereNotNull('media_url')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        $featuredYoutubeVideo = $youtubeVideos->firstWhere('featured', true)
            ?? $youtubeVideos->first();

        $shortsReels = Multimedia::query()
            ->published()
            ->shortsReels()
            ->whereNotNull('media_url')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        $photoAlbums = Multimedia::query()
            ->published()
            ->photoAlbums()
            ->whereNotNull('media_url')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        $counts = [
            'youtubeVideos' => Multimedia::query()->published()->youtubeVideos()->whereNotNull('media_url')->count(),
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
