<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\ProgramCategory;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category');
        $format = $request->query('format');
        $search = trim((string) $request->query('q', ''));
        $query = Program::with('categoryRelation')->visible()->ordered();

        if ($category) {
            $query->whereHas('categoryRelation', fn ($q) => $q->where('slug', $category));
        }

        if ($format) {
            $query->where('format', $format);
        }

        if ($search !== '') {
            $query->where(function ($inner) use ($search) {
                $inner
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('audience', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        return view('programs.index', [
            'featuredProgram' => (clone $query)->featured()->ordered()->first(),
            'programs' => $query->paginate(6)->withQueryString(),
            'programCategories' => ProgramCategory::where('is_active', true)->orderBy('sort_order')->get(),
            'selectedCategory' => $category,
            'selectedFormat' => $format,
            'search' => $search,
            'audienceOptions' => Program::query()
                ->visible()
                ->whereNotNull('audience')
                ->pluck('audience')
                ->flatMap(fn ($audience) => explode(',', (string) $audience))
                ->map(fn ($audience) => trim($audience))
                ->filter()
                ->unique()
                ->values(),
        ]);
    }

    public function show(string $slug)
    {
        $program = Program::with('categoryRelation')->visible()->where('slug', $slug)->firstOrFail();

        $relatedPrograms = Program::with('categoryRelation')
            ->active()
            ->whereKeyNot($program->id)
            ->when($program->program_category_id, fn ($query) => $query->where('program_category_id', $program->program_category_id))
            ->ordered()
            ->limit(3)
            ->get();

        return view('programs.show', compact('program', 'relatedPrograms'));
    }
}
