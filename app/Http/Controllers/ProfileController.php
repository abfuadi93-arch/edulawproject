<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(string $slug): View
    {
        $author = Author::query()
            ->publicProfile()
            ->where('slug', $slug)
            ->with('user')
            ->firstOrFail();

        $totalInsights = $author->insights()
            ->published()
            ->count();

        $insights = $author->insights()
            ->with(['categoryRelation'])
            ->published()
            ->orderByDesc('published_at')
            ->latest('insights.id')
            ->take(4)
            ->get();

        $totalPublications = $author->publications()
            ->published()
            ->count();

        $publications = $author->publications()
            ->with(['type'])
            ->published()
            ->orderByDesc('published_at')
            ->latest('publications.id')
            ->take(3)
            ->get();

        $focusTopics = $author->insights()
            ->with(['categoryRelation'])
            ->published()
            ->get()
            ->pluck('categoryRelation.name')
            ->filter()
            ->unique()
            ->take(6)
            ->values();

        return view('profiles.show', [
            'author' => $author,
            'insights' => $insights,
            'totalInsights' => $totalInsights,
            'publications' => $publications,
            'totalPublications' => $totalPublications,
            'focusTopics' => $focusTopics,
        ]);
    }
}
