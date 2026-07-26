<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Models\PublicationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicationController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->query('type');
        $search = $request->query('q');

        $query = Publication::with(['type', 'authors.user'])
            ->published()
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
            ->published()
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
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedPublications = Publication::with(['type', 'authors.user'])
            ->published()
            ->whereKeyNot($publication->id)
            ->when($publication->publication_type_id, fn ($query) => $query->where('publication_type_id', $publication->publication_type_id))
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $hasPdfFile = $this->publicPdfPath($publication) !== null;

        return view('publications.show', compact('publication', 'relatedPublications', 'hasPdfFile'));
    }

    public function preview(string $slug): StreamedResponse
    {
        return $this->servePublicPdf($slug, 'inline');
    }

    public function download(string $slug): StreamedResponse
    {
        return $this->servePublicPdf($slug, 'attachment');
    }

    private function servePublicPdf(string $slug, string $disposition): StreamedResponse
    {
        $publication = Publication::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $path = $this->publicPdfPath($publication);

        abort_if($path === null, 404);

        $filename = (Str::slug($publication->title) ?: 'publikasi-edulaw').'.pdf';

        return Storage::disk('public')->response(
            $path,
            $filename,
            ['Content-Type' => 'application/pdf'],
            $disposition,
        );
    }

    private function publicPdfPath(Publication $publication): ?string
    {
        $path = trim((string) $publication->pdf_file);

        if ($path === '' || Str::startsWith($path, ['http://', 'https://'])) {
            return null;
        }

        $path = ltrim($path, '/');

        do {
            $originalPath = $path;

            foreach (['public/', 'storage/'] as $prefix) {
                if (Str::startsWith($path, $prefix)) {
                    $path = Str::after($path, $prefix);
                }
            }
        } while ($path !== $originalPath);

        if ($path === '' || Str::contains($path, ['../', '..\\'])) {
            return null;
        }

        return Storage::disk('public')->exists($path) ? $path : null;
    }
}
