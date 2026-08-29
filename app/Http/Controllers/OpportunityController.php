<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OpportunityController extends Controller
{
    private const ITEMS_PER_PAGE = 12;

    public function index(Request $request): View
    {
        $featuredOpportunity = Opportunity::query()
            ->active()
            ->withExternalLink()
            ->where('featured', true)
            ->orderByRaw('CASE WHEN deadline IS NULL THEN 1 ELSE 0 END')
            ->orderBy('deadline')
            ->first();

        $selectedStatus = in_array($request->string('status')->toString(), ['open', 'closed'], true)
            ? $request->string('status')->toString()
            : 'open';
        $selectedType = in_array($request->string('type')->toString(), array_keys(self::typeLabels()), true)
            ? $request->string('type')->toString()
            : null;
        $selectedFormat = in_array($request->string('format')->toString(), ['online', 'offline', 'hybrid'], true)
            ? $request->string('format')->toString()
            : null;
        $selectedDeadline = in_array($request->string('deadline')->toString(), ['7_days', '30_days', 'month', 'all'], true)
            ? $request->string('deadline')->toString()
            : null;
        $selectedSort = in_array($request->string('sort')->toString(), ['deadline', 'deadline_desc', 'latest'], true)
            ? $request->string('sort')->toString()
            : 'deadline';
        $selectedLocation = Str::limit(trim($request->string('location')->toString()), 160, '');
        $search = Str::limit(trim($request->string('q')->toString()), 120, '');

        $query = Opportunity::query()
            ->withExternalLink()
            ->where('status', $selectedStatus)
            ->when($selectedStatus === 'open', fn ($query) => $query->where(function ($query): void {
                $query->whereNull('deadline')->orWhereDate('deadline', '>=', today());
            }))
            ->when($featuredOpportunity, fn ($query) => $query->whereKeyNot($featuredOpportunity->getKey()))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            }))
            ->when($selectedType, fn ($query, string $type) => $query->where('type', $type))
            ->when($selectedFormat, function ($query, string $format): void {
                if ($format === 'hybrid') {
                    $query->where(function ($query): void {
                        $query
                            ->whereRaw('LOWER(format) LIKE ?', ['%hybrid%'])
                            ->orWhere(function ($query): void {
                                $query
                                    ->whereRaw('LOWER(format) LIKE ?', ['%online%'])
                                    ->whereRaw('LOWER(format) LIKE ?', ['%offline%']);
                            });
                    });

                    return;
                }

                $query->whereRaw('LOWER(format) LIKE ?', ["%{$format}%"]);
            })
            ->when($selectedLocation !== '', fn ($query) => $query->where('location', $selectedLocation));

        match ($selectedDeadline) {
            '7_days' => $query->whereBetween('deadline', [today(), today()->addDays(7)]),
            '30_days' => $query->whereBetween('deadline', [today(), today()->addDays(30)]),
            'month' => $query->whereBetween('deadline', [today()->startOfMonth(), today()->endOfMonth()]),
            default => null,
        };

        match ($selectedSort) {
            'deadline_desc' => $query
                ->orderByRaw('CASE WHEN deadline IS NULL THEN 1 ELSE 0 END')
                ->orderByDesc('deadline')
                ->orderByDesc('id'),
            'latest' => $query->latest()->orderByDesc('id'),
            default => $query
                ->orderByRaw('CASE WHEN deadline IS NULL THEN 1 ELSE 0 END')
                ->orderBy('deadline')
                ->orderByDesc('id'),
        };

        $opportunities = $query->paginate(self::ITEMS_PER_PAGE)->withQueryString();

        $relevantOpportunities = Opportunity::query()
            ->withExternalLink()
            ->whereIn('status', ['open', 'closed']);

        $openOpportunities = Opportunity::query()
            ->active()
            ->withExternalLink();

        $statistics = [
            'total' => (clone $relevantOpportunities)->count(),
            'open' => (clone $openOpportunities)->count(),
            'nearest_deadline' => ($nearestDeadline = (clone $openOpportunities)
                ->whereNotNull('deadline')
                ->min('deadline'))
                    ? Carbon::parse($nearestDeadline)->locale('id')->translatedFormat('d F')
                    : null,
        ];

        $availableTypes = Opportunity::query()
            ->active()
            ->withExternalLink()
            ->whereNotNull('type')
            ->select('type')
            ->distinct()
            ->pluck('type')
            ->filter(fn (string $type): bool => array_key_exists($type, self::typeLabels()))
            ->push('career')
            ->unique()
            ->values();

        $availableFormats = Opportunity::query()
            ->withExternalLink()
            ->whereIn('status', ['open', 'closed'])
            ->whereNotNull('format')
            ->distinct()
            ->pluck('format')
            ->map(fn (string $format): ?string => self::formatBucket($format))
            ->filter()
            ->unique()
            ->values();

        $availableLocations = Opportunity::query()
            ->withExternalLink()
            ->whereIn('status', ['open', 'closed'])
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->orderBy('location')
            ->pluck('location');

        $filters = [
            'q' => $search,
            'status' => $selectedStatus,
            'type' => $selectedType,
            'format' => $selectedFormat,
            'deadline' => $selectedDeadline,
            'location' => $selectedLocation,
            'sort' => $selectedSort,
        ];

        return view('opportunities.index', compact(
            'opportunities',
            'featuredOpportunity',
            'statistics',
            'availableTypes',
            'availableFormats',
            'availableLocations',
            'filters',
        ));
    }

    private static function typeLabels(): array
    {
        return [
            'scholarship' => 'Beasiswa',
            'internship' => 'Magang',
            'competition' => 'Kompetisi',
            'call_for_paper' => 'Call for Papers',
            'fellowship' => 'Fellowship',
            'career' => 'Karier',
            'open_collaboration' => 'Kolaborasi',
            'volunteer' => 'Volunteer',
        ];
    }

    private static function formatBucket(string $format): ?string
    {
        $format = Str::lower($format);

        return match (true) {
            Str::contains($format, 'hybrid'),
            Str::containsAll($format, ['online', 'offline']) => 'hybrid',
            Str::contains($format, 'online') => 'online',
            Str::contains($format, 'offline') => 'offline',
            default => null,
        };
    }

    public function retired(string $slug): RedirectResponse
    {
        return redirect()->route('opportunities.index', status: 301);
    }
}
