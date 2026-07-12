<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    public function index(Request $request)
    {
        $query = Opportunity::query()
            ->open()
            ->orderByDesc('featured')
            ->orderByRaw('CASE WHEN deadline IS NULL THEN 1 ELSE 0 END')
            ->orderBy('deadline');

        if ($request->filled('q')) {
            $search = $request->q;

            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->whereIn('status', (array) $request->status);
        }

        if ($request->filled('type')) {
            $query->whereIn('type', (array) $request->type);
        }

        if ($request->filled('format')) {
            $query->whereIn('format', (array) $request->format);
        }

        if ($request->deadline === 'month') {
            $query->whereBetween('deadline', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ]);
        }

        if ($request->deadline === 'upcoming') {
            $query->whereDate('deadline', '>=', now());
        }

        if ($request->sort === 'latest') {
            $query->latest();
        } elseif ($request->sort === 'title') {
            $query->orderBy('title');
        }

        $opportunities = $query->paginate(6)->withQueryString();

        $featuredOpportunity = Opportunity::query()
            ->open()
            ->where('featured', true)
            ->orderByRaw('CASE WHEN deadline IS NULL THEN 1 ELSE 0 END')
            ->orderBy('deadline')
            ->first();

        $opportunityTypes = Opportunity::query()
            ->open()
            ->whereNotNull('type')
            ->select('type')
            ->distinct()
            ->pluck('type');

        return view('opportunities.index', compact(
            'opportunities',
            'featuredOpportunity',
            'opportunityTypes'
        ));
    }

    public function show(string $slug)
    {
        $opportunity = Opportunity::query()
            ->open()
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedOpportunities = Opportunity::query()
            ->open()
            ->whereKeyNot($opportunity->id)
            ->when($opportunity->type, fn ($query) => $query->where('type', $opportunity->type))
            ->orderByDesc('featured')
            ->orderByRaw('CASE WHEN deadline IS NULL THEN 1 ELSE 0 END')
            ->orderBy('deadline')
            ->limit(3)
            ->get();

        return view('opportunities.show', compact('opportunity', 'relatedOpportunities'));
    }
}
