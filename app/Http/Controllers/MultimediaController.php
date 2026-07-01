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
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('featured')
            ->orderByDesc('published_at')
            ->latest()
            ->limit(12)
            ->get();

        $featuredYoutubeVideo = $youtubeVideos->firstWhere('featured', true)
            ?? $youtubeVideos->first();

        $shortsReels = Multimedia::query()
            ->published()
            ->shortsReels()
            ->orderByDesc('published_at')
            ->latest()
            ->limit(12)
            ->get();

        $photoAlbums = Multimedia::query()
            ->published()
            ->photoAlbums()
            ->orderByDesc('published_at')
            ->latest()
            ->limit(12)
            ->get();

        $counts = [
            'youtubeVideos' => Multimedia::query()->published()->youtubeVideos()->count(),
            'shortsReels' => Multimedia::query()->published()->shortsReels()->count(),
            'photoAlbums' => Multimedia::query()->published()->photoAlbums()->count(),
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
