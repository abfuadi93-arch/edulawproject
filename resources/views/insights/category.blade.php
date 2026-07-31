@extends('layouts.app')

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $currentPage = $insights->currentPage();
    $pageSuffix = $currentPage > 1 ? " - Halaman {$currentPage}" : '';
    $pageTitle = $definition['seo_title'].$pageSuffix;
    $pageDescription = $definition['seo_description'].($currentPage > 1 ? " Halaman {$currentPage}." : '');
    $categoryArticleCount = (int) ($category?->published_insights_count ?? 0);
    $itemListSchemaItems = collect($insights->items())
        ->map(fn ($item): array => [
            'name' => $item->title,
            'url' => route('insights.show', $item->slug),
            'image' => filled($item->cover_image) ? $item->cover_image_url : null,
        ])
        ->all();
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDescription)
@section('canonical_url', $canonicalUrl)

@push('head')
    @if ($previousPageUrl)
        <link rel="prev" href="{{ $previousPageUrl }}">
    @endif
    @if ($nextPageUrl)
        <link rel="next" href="{{ $nextPageUrl }}">
    @endif
    <x-structured-data :data="\App\Support\StructuredData::breadcrumbs([
        ['name' => 'Beranda', 'url' => route('home')],
        ['name' => 'Insight', 'url' => route('insights.index')],
        ['name' => $definition['name'], 'url' => route('insights.categories.show', $categorySlug)],
    ])" />
    @if ($itemListSchemaItems !== [])
        <x-structured-data :data="\App\Support\StructuredData::itemList($itemListSchemaItems, $definition['name'])" />
    @endif
@endpush

@section('content')
<main class="bg-white">
    <section class="relative isolate overflow-hidden bg-brand-navy text-white">
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_82%_18%,rgba(212,159,59,0.2),transparent_34%),radial-gradient(circle_at_12%_82%,rgba(28,128,121,0.22),transparent_38%)]"></div>
        <div class="mx-auto max-w-7xl px-5 py-14 sm:px-6 sm:py-16 lg:px-8 lg:py-20">
            <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-xs font-semibold text-white/65">
                <a href="{{ route('home') }}" class="transition hover:text-white">Beranda</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('insights.index') }}" class="transition hover:text-white">Insight</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page" class="text-white">{{ $definition['name'] }}</span>
            </nav>

            <div class="mt-8 max-w-4xl">
                <p class="text-[11px] font-black uppercase tracking-[0.2em] text-brand-amber">Kanal Editorial</p>
                <h1 class="mt-3 font-display text-4xl font-black leading-tight tracking-tight text-white sm:text-5xl lg:text-6xl">
                    {{ $definition['title'] }}
                </h1>
                <p class="mt-6 max-w-3xl text-base font-medium leading-8 text-white/78 sm:text-lg">
                    {{ $definition['seo_description'] }}
                </p>
                <div class="mt-7 flex flex-wrap items-center gap-3">
                    <span class="rounded-full border border-white/20 bg-white/8 px-4 py-2 text-xs font-bold text-white">
                        {{ number_format($categoryArticleCount, 0, ',', '.') }} artikel terbit
                    </span>
                    @if ($currentPage > 1)
                        <span class="rounded-full border border-brand-amber/35 bg-brand-amber/10 px-4 py-2 text-xs font-bold text-brand-amber">
                            Halaman {{ $currentPage }} dari {{ $insights->lastPage() }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section aria-labelledby="category-introduction-heading" class="border-b border-slate-200 bg-[#fbfaf7]">
        <div class="mx-auto grid max-w-7xl gap-8 px-5 py-10 sm:px-6 lg:grid-cols-[minmax(0,1fr)_280px] lg:px-8 lg:py-12">
            <div>
                <h2 id="category-introduction-heading" class="font-display text-2xl font-bold text-brand-navy">
                    Tentang {{ $definition['name'] }}
                </h2>
                <p class="mt-4 max-w-4xl text-[15px] leading-8 text-slate-700">
                    {{ $definition['introduction'] }}
                </p>
            </div>
            <aside class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-label="Navigasi kanal">
                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-brand-coral">Jelajahi Insight</p>
                <p class="mt-2 text-sm leading-6 text-slate-600">Temukan analisis lintas kanal atau kembali ke halaman utama editorial.</p>
                <a href="{{ route('insights.index') }}" class="mt-4 inline-flex min-h-10 items-center text-sm font-bold text-brand-navy underline decoration-brand-amber decoration-2 underline-offset-4">
                    Lihat semua Insight
                </a>
            </aside>
        </div>
    </section>

    <section id="insight-archive" aria-labelledby="category-articles-heading" class="mx-auto max-w-7xl px-5 py-12 sm:px-6 lg:px-8 lg:py-16">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-brand-coral">Artikel Pilihan Kanal</p>
                <h2 id="category-articles-heading" class="mt-2 font-display text-3xl font-bold text-brand-navy">
                    Artikel {{ $definition['name'] }}
                </h2>
            </div>
            <p class="text-sm font-semibold text-slate-500">
                Menampilkan {{ $insights->firstItem() ?? 0 }}–{{ $insights->lastItem() ?? 0 }} dari {{ $insights->total() }} artikel
            </p>
        </div>

        <div class="mt-8 grid gap-x-6 gap-y-9 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($insights as $article)
                <article class="group flex min-w-0 flex-col">
                    <a href="{{ route('insights.show', $article->slug) }}" class="block rounded-2xl focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">
                        <div class="relative aspect-[16/10] overflow-hidden rounded-2xl bg-brand-navy shadow-sm ring-1 ring-black/5">
                            @if (filled($article->cover_image) && edulaw_file_exists($article->cover_image))
                                <img src="{{ $article->cover_image_url }}" alt="{{ $article->title }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03] motion-reduce:transition-none">
                            @else
                                <div class="absolute inset-0 bg-linear-to-br from-brand-navy via-[#244972] to-[#0f766e]"></div>
                                <div class="absolute inset-0 grid place-items-center text-4xl font-black text-white/15">{{ Str::upper(Str::substr($definition['name'], 0, 1)) }}</div>
                            @endif
                        </div>
                        <div class="pt-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-brand-coral">
                                {{ $definition['name'] }}
                            </p>
                            <h3 class="mt-2 line-clamp-2 text-xl font-bold leading-snug text-brand-ink transition group-hover:text-brand-navy">
                                {{ $article->title }}
                            </h3>
                            @if (filled($article->excerpt))
                                <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-600">
                                    {{ Str::limit(strip_tags($article->excerpt), 150) }}
                                </p>
                            @endif
                            <p class="mt-4 text-xs font-semibold text-slate-500">
                                {{ Carbon::parse($article->published_at)->translatedFormat('d M Y') }}
                                @if ($article->reading_time)
                                    · {{ $article->reading_time }} menit baca
                                @endif
                            </p>
                        </div>
                    </a>
                </article>
            @empty
                <div class="md:col-span-2 lg:col-span-3">
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-[#fbfaf7] px-6 py-12 text-center">
                        <h3 class="text-lg font-bold text-brand-navy">Artikel sedang dipersiapkan</h3>
                        <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-600">Kunjungi kembali kanal ini atau jelajahi kanal editorial lain yang sudah memiliki artikel terbit.</p>
                    </div>
                </div>
            @endforelse
        </div>

        @if ($insights->hasPages())
            <div class="mt-12 border-t border-slate-200 pt-8">
                {{ $insights->onEachSide(1)->links() }}
            </div>
        @endif
    </section>

    <section aria-labelledby="related-categories-heading" class="border-t border-slate-200 bg-[#fbfaf7] py-12 sm:py-14">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-brand-coral">Kanal Terkait</p>
                <h2 id="related-categories-heading" class="mt-2 font-display text-3xl font-bold text-brand-navy">Lanjutkan penelusuran</h2>
            </div>
            <div class="mt-7 grid gap-4 md:grid-cols-3">
                @foreach ($relatedCategories as $related)
                    <a href="{{ $related['url'] }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-navy/30 hover:shadow-md">
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-brand-coral">{{ number_format($related['article_count'], 0, ',', '.') }} artikel</p>
                        <h3 class="mt-2 text-lg font-bold text-brand-ink transition group-hover:text-brand-navy">{{ $related['name'] }}</h3>
                        <p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-600">{{ $related['seo_description'] }}</p>
                        <span class="mt-4 inline-flex text-sm font-bold text-brand-navy underline decoration-brand-amber decoration-2 underline-offset-4">Buka kanal</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
</main>
@endsection
