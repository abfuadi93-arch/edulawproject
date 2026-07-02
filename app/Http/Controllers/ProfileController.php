<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Author $author): View
    {
        abort_unless($author->is_active, 404);

        $author->loadMissing('user');

        $insights = $author->insights()
            ->with(['categoryRelation'])
            ->published()
            ->orderByDesc('published_at')
            ->latest('insights.id')
            ->paginate(6, ['*'], 'tulisan_page')
            ->withQueryString();

        $publications = $author->publications()
            ->with(['type'])
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->latest('publications.id')
            ->paginate(6, ['*'], 'publikasi_page')
            ->withQueryString();

        return view('profiles.show', [
            'author' => $author,
            'insights' => $insights,
            'publications' => $publications,
        ]);
    }
}
