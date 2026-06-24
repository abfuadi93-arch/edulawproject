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
        $selectedSerial = $request->query('serial');
        $selectedTopic = $request->query('topic');
        $typeFilterValues = collect($types)
            ->flatMap(fn (string $type): array => Multimedia::typeVariants($type))
            ->unique()
            ->values()
            ->all();

        $typeOptions = collect(Multimedia::TYPE_OPTIONS)
            ->map(fn (string $label, string $value): array => [
                'value' => $value,
                'label' => $label,
            ])
            ->values();

        $platformOptions = Multimedia::query()
            ->published()
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

        $hasCuratedLatest = Multimedia::query()
            ->published()
            ->where('display_section', 'latest')
            ->exists();

        $shouldUseCuratedLatest = $hasCuratedLatest
            && blank($types)
            && blank($platforms)
            && blank($search)
            && blank($selectedSerial)
            && blank($selectedTopic);

        $query = Multimedia::query()
            ->published()
            ->when($shouldUseCuratedLatest, fn ($q) => $q->where('display_section', 'latest'))
            ->when($typeFilterValues, fn ($q) => $q->whereIn('type', $typeFilterValues))
            ->when($platforms, fn ($q) => $q->whereIn('platform', $platforms))
            ->when($selectedSerial, fn ($q) => $q->where('serial', $selectedSerial))
            ->when($selectedTopic, fn ($q) => $q->where('topic', $selectedTopic))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('serial', 'like', "%{$search}%")
                        ->orWhere('topic', 'like', "%{$search}%");
                });
            });

        match ($sort) {
            'title' => $query->orderBy('title'),
            'oldest' => $query->orderBy('published_at')->orderBy('created_at'),
            default => $query->orderByDesc('published_at')->latest(),
        };

        $featured = Multimedia::query()
            ->published()
            ->where('featured', true)
            ->orderByDesc('published_at')
            ->first()
            ?? Multimedia::query()
                ->published()
                ->orderByDesc('published_at')
                ->latest()
                ->first();

        $hasCuratedShorts = Multimedia::query()
            ->published()
            ->where('display_section', 'short_video')
            ->exists();

        $shortMultimedia = Multimedia::query()
            ->published()
            ->when(
                $hasCuratedShorts,
                fn ($q) => $q->where('display_section', 'short_video'),
                fn ($q) => $q->whereIn('type', Multimedia::typeVariants('shorts'))
            )
            ->orderByDesc('published_at')
            ->latest()
            ->limit(8)
            ->get();

        $hasCuratedSerials = Multimedia::query()
            ->published()
            ->where('display_section', 'serial_edulaw')
            ->exists();

        $serialMultimedia = Multimedia::query()
            ->published()
            ->when(
                $hasCuratedSerials,
                fn ($q) => $q->where('display_section', 'serial_edulaw'),
                fn ($q) => $q->whereNotNull('serial')->where('serial', '!=', '')
            )
            ->orderByDesc('published_at')
            ->latest()
            ->limit(12)
            ->get();

        $hasCuratedTopics = Multimedia::query()
            ->published()
            ->where('display_section', 'topic_multimedia')
            ->exists();

        $topicMultimedia = Multimedia::query()
            ->published()
            ->when(
                $hasCuratedTopics,
                fn ($q) => $q->where('display_section', 'topic_multimedia'),
                fn ($q) => $q->whereNotNull('topic')->where('topic', '!=', '')
            )
            ->orderByDesc('published_at')
            ->latest()
            ->limit(18)
            ->get();

        return view('multimedia.index', [
            'featured' => $featured,
            'multimediaItems' => $query->paginate(9)->withQueryString(),
            'shortMultimedia' => $shortMultimedia,
            'serialMultimedia' => $serialMultimedia,
            'topicMultimedia' => $topicMultimedia,
            'typeOptions' => $typeOptions,
            'platformOptions' => $platformOptions,
            'serialOptions' => Multimedia::SERIAL_OPTIONS,
            'topicOptions' => Multimedia::TOPIC_OPTIONS,
            'selectedTypes' => $types,
            'selectedPlatforms' => $platforms,
            'selectedSerial' => $selectedSerial,
            'selectedTopic' => $selectedTopic,
            'search' => $search,
            'sort' => $sort,
        ]);
    }
}
