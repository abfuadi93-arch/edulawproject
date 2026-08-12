@extends('layouts.app')

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $currentPage = $insights->currentPage();
    $pageSuffix = $currentPage > 1 ? " - Halaman {$currentPage}" : '';
    $pageTitle = $definition['seo_title'].$pageSuffix;
    $pageDescription = $definition['seo_description'].($currentPage > 1 ? " Halaman {$currentPage}." : '');
    $categoryArticleCount = (int) ($category?->published_insights_count ?? 0);
    $categoryHeroImage = asset('images/hero/insight-category-pattern.webp');
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
    <x-shared.page-header
        :title="$definition['title']"
        :description="$definition['seo_description']"
        :compact="true"
        :channel-header="true"
        :break-title-after-colon="true"
        eyebrow="Kanal Editorial"
        :background-image="$categoryHeroImage"
        :background-alt="'Pola latar kategori '.$definition['name']"
        grid-class="gap-5 px-4 py-7 sm:w-full sm:px-6 lg:min-h-[240px] lg:grid-cols-2 lg:items-center lg:px-8 lg:py-6"
        title-class="text-3xl sm:text-4xl lg:text-[2.45rem]"
        :overlay-opacity="0.48"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Editorial', 'url' => route('insights.index')],
            ['label' => $definition['name']],
        ]"
    >
        <div class="flex w-full flex-wrap gap-2.5 lg:justify-end">
            <span class="inline-flex min-h-10 items-center rounded-full border border-white/25 bg-white/10 px-4 text-xs font-bold text-white backdrop-blur">
                {{ number_format($categoryArticleCount, 0, ',', '.') }} artikel terbit
            </span>
            @if ($currentPage > 1)
                <span class="inline-flex min-h-10 items-center rounded-full border border-brand-amber/40 bg-brand-amber/10 px-4 text-xs font-bold text-brand-amber backdrop-blur">
                    Halaman {{ $currentPage }} dari {{ $insights->lastPage() }}
                </span>
            @endif
        </div>
    </x-shared.page-header>

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
