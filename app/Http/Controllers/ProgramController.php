<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\ProgramCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ProgramController extends Controller
{
    public function index(Request $request)
    {
        $programCategories = ProgramCategory::where('is_active', true)
            ->withCount(['programs as programs_count' => fn ($query) => $query->visible()])
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (ProgramCategory $category) => $category->programs_count > 0)
            ->values();

        $statusCounts = Program::query()
            ->visible()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $formatCounts = Program::query()
            ->visible()
            ->whereNotNull('format')
            ->selectRaw('format, COUNT(*) as total')
            ->groupBy('format')
            ->pluck('total', 'format');

        $statusOptions = [
            ['value' => 'upcoming', 'label' => 'Akan Datang', 'count' => (int) ($statusCounts['upcoming'] ?? 0)],
            ['value' => 'ongoing', 'label' => 'Sedang Berjalan', 'count' => (int) ($statusCounts['ongoing'] ?? 0)],
            ['value' => 'archived', 'label' => 'Selesai', 'count' => (int) ($statusCounts['archived'] ?? 0)],
        ];

        $filterCategories = $programCategories
            ->map(fn (ProgramCategory $category) => [
                'value' => $category->slug,
                'label' => $category->name,
                'count' => (int) $category->programs_count,
            ])
            ->values();

        $formatOptions = collect([
            ['value' => 'online', 'label' => 'Online', 'count' => (int) ($formatCounts['online'] ?? 0)],
            ['value' => 'offline', 'label' => 'Offline', 'count' => (int) ($formatCounts['offline'] ?? 0)],
            ['value' => 'hybrid', 'label' => 'Hybrid', 'count' => (int) ($formatCounts['hybrid'] ?? 0)],
        ])->filter(fn (array $option) => $option['count'] > 0)->values();

        $levelOptions = collect([
            ['value' => 'dasar', 'label' => 'Dasar', 'count' => $this->levelCount('dasar')],
            ['value' => 'menengah', 'label' => 'Menengah', 'count' => $this->levelCount('menengah')],
            ['value' => 'lanjutan', 'label' => 'Lanjutan', 'count' => $this->levelCount('lanjutan')],
        ])->filter(fn (array $option) => $option['count'] > 0)->values();

        $selectedStatuses = $this->queryArray($request->query('status', []));
        $selectedCategories = $this->queryArray($request->query('category', []));
        $selectedFormats = $this->queryArray($request->query('format', []));
        $selectedLevels = $this->queryArray($request->query('level', []));
        $activeSearch = trim((string) $request->query('q', ''));
        $selectedSort = in_array($request->query('sort'), ['terbaru', 'terdekat', 'nama'], true)
            ? (string) $request->query('sort')
            : 'terdekat';
        $selectedView = $request->query('view') === 'list' ? 'list' : 'grid';

        $featuredProgram = Program::with('categoryRelation')
            ->visible()
            ->featured()
            ->orderByDesc('event_date')
            ->latest()
            ->first();

        $activeQuery = Program::with('categoryRelation')->visible()->active();

        if ($selectedStatuses !== []) {
            $activeStatuses = array_values(array_intersect($selectedStatuses, ['upcoming', 'ongoing']));

            if ($activeStatuses === []) {
                $activeQuery->whereRaw('1 = 0');
            } else {
                $activeQuery->whereIn('status', $activeStatuses);
            }
        }

        if ($selectedCategories !== []) {
            $this->applyCategoryFilter($activeQuery, $selectedCategories);
        }

        if ($selectedFormats !== []) {
            $activeQuery->whereIn('format', $selectedFormats);
        }

        if ($selectedLevels !== []) {
            $this->applyLevelFilter($activeQuery, $selectedLevels);
        }

        if ($activeSearch !== '') {
            $this->applySearch($activeQuery, $activeSearch);
        }

        $activeCount = Program::query()->visible()->active()->count();
        $portfolioCount = Program::query()->visible()->archived()->count();
        $activeTotal = (clone $activeQuery)->count();

        $activePrograms = $this->applyActiveSort($activeQuery, $selectedSort)->limit(4)->get();

        $archivePrograms = Program::with('categoryRelation')
            ->visible()
            ->archived()
            ->orderByDesc('event_date')
            ->latest()
            ->limit(10)
            ->get();

        return view('programs.index', [
            'featuredProgram' => $featuredProgram,
            'activePrograms' => $activePrograms,
            'activeTotal' => $activeTotal,
            'archivePrograms' => $archivePrograms,
            'programCategories' => $programCategories,
            'filterCategories' => $filterCategories,
            'statusOptions' => $statusOptions,
            'formatOptions' => $formatOptions,
            'levelOptions' => $levelOptions,
            'selectedStatuses' => $selectedStatuses,
            'selectedCategories' => $selectedCategories,
            'selectedFormats' => $selectedFormats,
            'selectedLevels' => $selectedLevels,
            'activeSearch' => $activeSearch,
            'selectedSort' => $selectedSort,
            'selectedView' => $selectedView,
            'audienceOptions' => collect(['Mahasiswa', 'Akademisi', 'Peneliti', 'Praktisi', 'Komunitas', 'Masyarakat']),
            'stats' => [
                [
                    'label' => 'Program Aktif',
                    'value' => $this->formatStatNumber($activeCount),
                    'icon' => 'calendar',
                ],
                [
                    'label' => 'Portofolio',
                    'value' => $this->formatStatNumber($portfolioCount),
                    'icon' => 'briefcase',
                ],
            ],
        ]);
    }

    public function archive(Request $request)
    {
        $programCategories = ProgramCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $archiveSearch = trim((string) $request->query('archive_q', ''));
        $archiveCategory = trim((string) $request->query('archive_category', ''));
        $archiveYear = trim((string) $request->query('archive_year', ''));

        $archiveQuery = Program::with('categoryRelation')
            ->visible()
            ->archived()
            ->orderByDesc('event_date')
            ->latest();

        if ($archiveSearch !== '') {
            $this->applySearch($archiveQuery, $archiveSearch);
        }

        if ($archiveCategory !== '') {
            $this->applyCategoryFilter($archiveQuery, [$archiveCategory]);
        }

        if ($archiveYear !== '') {
            $archiveQuery->whereYear('event_date', $archiveYear);
        }

        return view('programs.archive', [
            'archivePrograms' => $archiveQuery->paginate(12)->withQueryString(),
            'programCategories' => $programCategories,
            'archiveSearch' => $archiveSearch,
            'archiveCategory' => $archiveCategory,
            'archiveYear' => $archiveYear,
            'archiveYears' => $this->archiveYears(),
        ]);
    }

    public function show(string $slug)
    {
        $program = Program::with('categoryRelation')->visible()->where('slug', $slug)->firstOrFail();

        $relatedPrograms = Program::with('categoryRelation')
            ->visible()
            ->active()
            ->whereKeyNot($program->id)
            ->when($program->program_category_id, fn ($query) => $query->where('program_category_id', $program->program_category_id))
            ->ordered()
            ->limit(3)
            ->get();

        return view('programs.show', compact('program', 'relatedPrograms'));
    }

    private function queryArray(mixed $value): array
    {
        return collect(Arr::wrap($value))
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function applySearch($query, string $search): void
    {
        $query->where(function ($inner) use ($search) {
            $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('short_description', 'like', "%{$search}%")
                ->orWhere('audience', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%");
        });
    }

    private function applyCategoryFilter($query, array $categorySlugs): void
    {
        $query->whereHas('categoryRelation', fn ($categoryQuery) => $categoryQuery->whereIn('slug', $categorySlugs));
    }

    private function applyLevelFilter($query, array $levels): void
    {
        $terms = collect($levels)
            ->flatMap(fn (string $level) => $this->levelTerms($level))
            ->map(fn (string $term) => Str::lower($term))
            ->unique()
            ->values();

        if ($terms->isEmpty()) {
            return;
        }

        $query->where(function ($inner) use ($terms) {
            foreach ($terms as $term) {
                $inner->orWhereRaw('LOWER(level) LIKE ?', ["%{$term}%"]);
            }
        });
    }

    private function applyActiveSort($query, string $sort)
    {
        $query->orderByDesc('featured');

        return match ($sort) {
            'terbaru' => $query->orderByDesc('event_date')->latest(),
            'nama' => $query->orderBy('name')->orderByRaw('event_date IS NULL')->orderBy('event_date'),
            default => $query->orderByRaw('event_date IS NULL')->orderBy('event_date')->latest(),
        };
    }

    private function levelCount(string $level): int
    {
        $terms = collect($this->levelTerms($level))
            ->map(fn (string $term) => Str::lower($term))
            ->unique()
            ->values();

        return Program::query()
            ->visible()
            ->where(function ($query) use ($terms) {
                foreach ($terms as $term) {
                    $query->orWhereRaw('LOWER(level) LIKE ?', ["%{$term}%"]);
                }
            })
            ->count();
    }

    private function levelTerms(string $level): array
    {
        return match ($level) {
            'dasar' => ['dasar', 'beginner', 'basic', 'pemula', 'umum'],
            'menengah' => ['menengah', 'intermediate'],
            'lanjutan' => ['lanjutan', 'advanced', 'lanjut'],
            default => [$level],
        };
    }

    private function archiveYears()
    {
        return Program::query()
            ->visible()
            ->archived()
            ->whereNotNull('event_date')
            ->get(['event_date'])
            ->map(fn (Program $program) => optional($program->event_date)->format('Y'))
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();
    }

    private function formatStatNumber(int $value): string
    {
        return number_format($value, 0, ',', '.');
    }
}
