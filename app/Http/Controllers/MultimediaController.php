<?php

namespace App\Http\Controllers;

use App\Models\Multimedia;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class MultimediaController extends Controller
{
    public function index(Request $request): View
    {
        $types = collect(Arr::wrap($request->query('type', [])))
            ->filter(fn ($value) => filled($value))
            ->values()
            ->all();

        $platforms = collect(Arr::wrap($request->query('platform', [])))
            ->filter(fn ($value) => filled($value))
            ->values()
            ->all();

        $search = $request->query('q');
        $sort = $request->query('sort', 'latest');

        $typeOptions = Multimedia::query()
            ->where('status', 'published')
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->map(fn (string $value): array => [
                'value' => $value,
                'label' => (new Multimedia(['type' => $value]))->display_type,
            ]);

        $platformOptions = Multimedia::query()
            ->where('status', 'published')
            ->whereNotNull('platform')
            ->where('platform', '!=', '')
            ->select('platform')
            ->distinct()
            ->orderBy('platform')
            ->pluck('platform')
            ->map(fn (string $value): array => [
                'value' => $value,
                'label' => (new Multimedia(['platform' => $value]))->display_platform,
            ]);

        $query = Multimedia::query()
            ->where('status', 'published')
            ->when($types, fn ($q) => $q->whereIn('type', $types))
            ->when($platforms, fn ($q) => $q->whereIn('platform', $platforms))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            });

        match ($sort) {
            'title' => $query->orderBy('title'),
            'oldest' => $query->orderBy('published_at')->orderBy('created_at'),
            default => $query->orderByDesc('published_at')->latest(),
        };

        $featured = Multimedia::query()
            ->where('status', 'published')
            ->where('featured', true)
            ->orderByDesc('published_at')
            ->first()
            ?? Multimedia::query()
                ->where('status', 'published')
                ->orderByDesc('published_at')
                ->latest()
                ->first();

        $shortMultimedia = Multimedia::query()
            ->where('status', 'published')
            ->whereIn('type', ['shorts', 'reels'])
            ->orderByDesc('published_at')
            ->latest()
            ->limit(8)
            ->get();

        return view('multimedia.index', [
            'featured' => $featured,
            'multimediaItems' => $query->paginate(9)->withQueryString(),
            'shortMultimedia' => $shortMultimedia,
            'typeOptions' => $typeOptions,
            'platformOptions' => $platformOptions,
            'selectedTypes' => $types,
            'selectedPlatforms' => $platforms,
            'search' => $search,
            'sort' => $sort,
        ]);
    }
}
