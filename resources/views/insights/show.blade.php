@extends('layouts.app')

@section('title', $insight->seo_title ?: $insight->title)
@section('meta_description', $insight->seo_description ?: \Illuminate\Support\Str::limit(strip_tags($insight->excerpt ?: ($insight->content ?? '')), 160) ?: 'Editorial Edulaw Project menyajikan analisis hukum yang relevan, jernih, dan mudah dipahami.')
@section('canonical_url', route('insights.show', $insight->slug))
@section('og_type', 'article')
@section('og_image', edulaw_file_url($insight->og_image ?: $insight->cover_image, 'images/hero/hero-edulaw.jpg'))
@section('og_image_alt', $insight->title)

@push('head')
    <x-structured-data :data="\App\Support\StructuredData::article($insight)" />
    <x-structured-data :data="\App\Support\StructuredData::breadcrumbs([
        ['name' => 'Beranda', 'url' => route('home')],
        ['name' => 'Insight', 'url' => route('insights.index')],
        ['name' => $insight->title, 'url' => route('insights.show', $insight->slug)],
    ])" />
@endpush

@section('content')
@php
    $readingTime = function ($item) {
        if ($item?->reading_time) {
            return $item->reading_time . ' menit baca';
        }

        $words = str_word_count(strip_tags($item?->content ?? ''));

        return max(1, (int) ceil($words / 200)) . ' menit baca';
    };

    $coverImage = $insight->cover_image_url;
    $categoryName = $insight->display_category;
    $publishedDate = optional($insight->published_at)->translatedFormat('d F Y') ?? 'Belum dijadwalkan';
    $description = $insight->excerpt
        ?: \Illuminate\Support\Str::limit(strip_tags($insight->content ?? ''), 180)
        ?: 'Editorial Edulaw Project menyajikan analisis hukum yang relevan, jernih, dan mudah dipahami.';
    $primaryAuthor = $insight->authors
        ->filter(fn ($author) => $author->is_active !== false)
        ->sortBy(fn ($author) => $author->pivot?->author_order ?? 999)
        ->first();
    $authorName = $primaryAuthor?->name ?: $insight->creator?->name ?: $insight->reviewer?->name ?: 'Edulaw Project';
    $authorInstitution = collect([$primaryAuthor?->position, $primaryAuthor?->institution])->filter()->join(' · ') ?: 'Edulaw Project';
    $authorPhoto = $primaryAuthor?->photo_url;
    $authorProfileUrl = $primaryAuthor?->slug ? route('profiles.show', $primaryAuthor->slug) : null;
    $additionalAuthorsCount = max($insight->authors->count() - 1, 0);
    $authorInitials = \Illuminate\Support\Str::of($authorName)
        ->explode(' ')
        ->filter()
        ->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))
        ->take(2)
        ->implode('');
    $preparedFootnotes = app(\App\Services\InsightFootnoteService::class)->prepareForPublic($insight);
    $preparedArticle = \App\Support\ArticleContent::prepare($preparedFootnotes['html']);
    $articleFootnotes = $preparedFootnotes['footnotes'];
    $articleHeadings = collect($preparedArticle['headings'])
        ->where('level', 2)
        ->values();
    $relatedInsights = $relatedInsights ?? collect();
    $displayRelatedInsights = $relatedInsights
        ->filter(fn ($item) => filled($item?->title) && filled($item?->slug))
        ->unique('id')
        ->take(3)
        ->values();
@endphp

<main class="bg-transparent">
    <section class="relative isolate overflow-hidden bg-brand-navy text-white">
        @if ($coverImage)
            <img
                src="{{ $coverImage }}"
                alt="{{ $insight->title }}"
                class="absolute inset-0 z-0 h-full w-full object-cover"
                onerror="this.onerror=null;this.src='{{ asset('images/hero/hero-edulaw.jpg') }}';"
            >
        @else
            <div class="absolute inset-0 z-0 bg-linear-to-br from-brand-navy via-slate-900 to-brand-navy"></div>
        @endif

        <div class="absolute inset-0 z-0 bg-brand-navy/60"></div>
        <div class="absolute inset-0 z-0 bg-linear-to-b from-brand-navy/42 via-brand-navy/60 to-brand-navy/72"></div>
        <div class="absolute inset-0 z-0 bg-[radial-gradient(circle_at_center,rgba(255,255,255,0.10),transparent_48%)]"></div>
        <div class="absolute bottom-0 left-0 right-0 z-0 h-px bg-white/12"></div>

        <div class="relative z-10 mx-auto flex min-h-[360px] max-w-7xl flex-col items-center justify-center px-6 py-16 text-center sm:min-h-[400px] lg:min-h-[520px] lg:px-8 lg:py-24">
            <nav class="mb-7 flex flex-wrap items-center justify-center gap-2 text-xs font-semibold text-white/70" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="transition hover:text-white">
                    Beranda
                </a>
                <span class="text-white/36">/</span>
                <a href="{{ route('insights.index') }}" class="transition hover:text-white">
                    Editorial
                </a>
                <span class="text-white/36">/</span>
                <span class="text-white">
                    {{ $categoryName }}
                </span>
            </nav>

            <h1 class="max-w-6xl break-words text-balance text-3xl font-black leading-[1.08] tracking-tight text-white sm:text-4xl md:text-5xl lg:text-6xl lg:leading-[1.04]">
                {{ $insight->title }}
            </h1>

            <div class="mt-7 flex flex-wrap items-center justify-center gap-x-4 gap-y-3 text-sm font-semibold text-white/80">
                <span>{{ $publishedDate }}</span>

                <span class="hidden h-1 w-1 rounded-full bg-white/40 sm:block"></span>
                <span>{{ $readingTime($insight) }}</span>
            </div>
        </div>
    </section>

    <section class="border-b border-slate-200 bg-brand-paper/40 py-10 lg:py-16">
        <div class="mx-auto grid max-w-7xl gap-10 px-5 sm:px-6 lg:grid-cols-[minmax(0,780px)_minmax(280px,320px)] lg:items-start lg:justify-center lg:gap-10 lg:px-8 xl:gap-12">
            <article class="min-w-0 overflow-hidden">
                @if ($insight->excerpt)
                    <p class="article-lead">
                        {{ $insight->excerpt }}
                    </p>
                @endif

                <div class="article-content edulaw-readable insight-article-body prose prose-slate max-w-none
                    prose-headings:text-[#0f2a4a]
                    prose-h2:mt-10 prose-h2:mb-4 prose-h2:border-l-4 prose-h2:border-[#d99a21] prose-h2:pl-4 prose-h2:text-2xl prose-h2:font-extrabold
                    prose-h3:mt-8 prose-h3:mb-3 prose-h3:text-xl prose-h3:font-bold
                    prose-strong:text-[#0f2a4a]">
                    @if ($preparedArticle['html'])
                        {!! $preparedArticle['html'] !!}
                    @else
                        <p>{{ $description }}</p>
                    @endif
                </div>

                @if ($articleFootnotes->isNotEmpty())
                    <section class="insight-footnotes" aria-labelledby="insight-footnotes-heading">
                        <h2 id="insight-footnotes-heading">Catatan Kaki</h2>

                        <ol>
                            @foreach ($articleFootnotes as $item)
                                <li id="fn-{{ $item['number'] }}">
                                    <span>{{ $item['footnote']->content }}</span>
                                    <a
                                        href="#fnref-{{ $item['number'] }}"
                                        class="insight-footnote-backlink"
                                        aria-label="Kembali ke teks catatan kaki {{ $item['number'] }}"
                                    >↩</a>
                                </li>
                            @endforeach
                        </ol>
                    </section>
                @endif

                @if ($insight->tags->isNotEmpty())
                    <div class="mt-12 border-t border-slate-200 pt-6">
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-brand-navy/60">
                            Topik
                        </p>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($insight->tags as $tag)
                                <span class="edulaw-badge edulaw-badge-lg edulaw-badge-muted normal-case tracking-normal">
                                    {{ $tag->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </article>

            <aside class="insight-sidebar grid w-full grid-cols-1 gap-5 self-start lg:sticky lg:top-24 lg:block lg:space-y-5" aria-label="Informasi artikel">
                <section class="w-full rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" aria-labelledby="article-about-heading">
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#a8660a]">
                        Artikel Editorial
                    </p>
                    <h2 id="article-about-heading" class="mt-2 text-lg font-black text-brand-navy">Tentang Artikel</h2>

                    <div class="mt-4">
                        <span class="edulaw-badge edulaw-badge-muted normal-case tracking-normal">
                            {{ $categoryName }}
                        </span>
                    </div>

                    <div class="mt-5 border-t border-slate-100 pt-5">
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Penulis</p>

                        <div class="mt-3 flex items-center gap-4">
                            @if ($authorPhoto)
                                <img
                                    src="{{ $authorPhoto }}"
                                    alt="Foto profil {{ $authorName }}"
                                    class="h-14 w-14 shrink-0 rounded-2xl object-cover ring-1 ring-slate-200"
                                    loading="lazy"
                                >
                            @else
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand-navy text-sm font-black text-white">
                                    {{ $authorInitials }}
                                </div>
                            @endif

                            <div class="min-w-0">
                                <h3 class="font-black leading-snug text-brand-navy">
                                    @if ($authorProfileUrl)
                                        <a href="{{ $authorProfileUrl }}" class="underline-offset-4 hover:underline">
                                            {{ $authorName }}
                                        </a>
                                    @else
                                        {{ $authorName }}
                                    @endif
                                </h3>
                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    {{ $authorInstitution }}
                                </p>

                                @if ($additionalAuthorsCount > 0)
                                    <p class="mt-1 text-xs font-semibold text-slate-400">
                                        dan {{ $additionalAuthorsCount }} penulis lainnya
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>

                @if ($articleHeadings->isNotEmpty())
                    <nav class="article-toc hidden w-full rounded-2xl border border-brand-amber/40 bg-[#f8f5ee] p-6 shadow-sm lg:block" aria-labelledby="article-toc-heading">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-amber/20 text-brand-navy" aria-hidden="true">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                    <path d="M9 6h11M9 12h11M9 18h11M4 6h.01M4 12h.01M4 18h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#a8660a]">Navigasi</p>
                                <h2 id="article-toc-heading" class="mt-1 text-lg font-black text-brand-navy">Daftar Isi</h2>
                            </div>
                        </div>

                        <ol class="mt-5 space-y-1 border-l border-brand-amber/50">
                            @foreach ($articleHeadings as $heading)
                                <li class="pl-4">
                                    <a
                                        href="#{{ $heading['id'] }}"
                                        class="block rounded-r-lg py-2 pr-2 text-sm font-semibold leading-5 text-slate-600 transition hover:bg-white/80 hover:text-brand-navy"
                                    >
                                        {{ $heading['title'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                @endif

            </aside>
        </div>
    </section>

    <x-shared.cta-collaboration
        eyebrow="Kolaborasi Editorial"
        title="Punya topik hukum yang perlu dibahas lebih dalam?"
        body="Ajak Edulaw Project mengembangkan tulisan, diskusi, atau serial edukasi hukum yang relevan untuk pembaca Anda."
        :primary-url="route('collaboration.index')"
        primary-label="Ajukan Kolaborasi"
        :secondary-url="route('insights.index')"
        secondary-label="Lihat Editorial Lainnya"
    />

    @if ($displayRelatedInsights->isNotEmpty())
        <section class="border-t border-slate-200 bg-white py-14 lg:py-16" aria-labelledby="related-editorials-heading">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-[#a8660a]">
                            Baca Juga
                        </p>

                        <h2 id="related-editorials-heading" class="mt-3 border-l-4 border-brand-amber pl-4 text-3xl font-black tracking-tight text-brand-navy sm:text-4xl">
                            Editorial Terkait
                        </h2>
                        <p class="mt-4 max-w-2xl text-sm leading-6 text-slate-500 sm:text-base">
                            Lanjutkan membaca perspektif dan analisis hukum yang masih berkaitan dengan topik ini.
                        </p>
                    </div>

                    <a
                        href="{{ route('insights.index') }}"
                        class="inline-flex w-fit items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-brand-navy shadow-sm transition hover:border-brand-amber hover:bg-[#f8f5ee]"
                    >
                        Lihat Semua Editorial
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>

                <div class="mt-10 grid items-stretch gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($displayRelatedInsights as $item)
                        <article class="group flex h-full min-h-[25rem] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-brand-amber/60 hover:shadow-lg hover:shadow-slate-900/10">
                            <a href="{{ route('insights.show', $item->slug) }}" class="relative block aspect-[16/10] shrink-0 overflow-hidden bg-slate-100">
                                <img
                                    src="{{ $item->cover_image_url }}"
                                    alt="{{ $item->title }}"
                                    class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                    loading="lazy"
                                    onerror="this.onerror=null;this.src='{{ asset('images/hero/hero-edulaw.jpg') }}';"
                                >
                            </a>

                            <div class="flex flex-1 flex-col p-5 sm:p-6">
                                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#a8660a]">
                                    {{ $item->display_category }}
                                </p>

                                <h3 class="mt-3 line-clamp-2 min-h-[2.75rem] text-base font-black leading-snug text-brand-navy">
                                    <a href="{{ route('insights.show', $item->slug) }}">
                                        {{ $item->title }}
                                    </a>
                                </h3>

                                @if ($item->excerpt)
                                    <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-slate-500">
                                        {{ $item->excerpt }}
                                    </p>
                                @endif

                                <div class="mt-auto flex items-center gap-1.5 border-t border-slate-100 pt-5 text-xs font-semibold text-slate-400">
                                    <time datetime="{{ optional($item->published_at)?->toDateString() }}">
                                        {{ optional($item->published_at)->translatedFormat('d M Y') }}
                                    </time>
                                    <span class="mx-1">&middot;</span>
                                    {{ $readingTime($item) }}
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</main>
@endsection
