<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Models\PublicationType;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicationController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->query('type');
        $search = $request->query('q');

        $query = Publication::with(['type', 'authors.user'])
            ->where('status', 'published')
            ->when($type, fn ($q) => $q->whereHas('type', fn ($typeQuery) => $typeQuery->where('slug', $type)))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('published_at')
            ->latest();

        $featuredPublication = Publication::with(['type', 'authors.user'])
            ->where('status', 'published')
            ->where('featured', true)
            ->orderByDesc('published_at')
            ->first();

        return view('publications.index', [
            'featuredPublication' => $featuredPublication,
            'publications' => $query->paginate(9)->withQueryString(),
            'publicationTypes' => PublicationType::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'selectedType' => $type,
            'search' => $search,
        ]);
    }

    public function show(string $slug): View
    {
        $publication = Publication::with(['type', 'authors.user', 'tags'])
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedPublications = Publication::with(['type', 'authors.user'])
            ->where('status', 'published')
            ->whereKeyNot($publication->id)
            ->when($publication->publication_type_id, fn ($query) => $query->where('publication_type_id', $publication->publication_type_id))
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('publications.show', compact('publication', 'relatedPublications'));
    }
}
