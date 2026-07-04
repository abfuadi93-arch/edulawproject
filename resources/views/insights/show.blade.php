@extends('layouts.app')

@section('title', ($insight->seo_title ?: $insight->title) . ' - Edulaw Project')
@section('meta_description', $insight->seo_description ?: \Illuminate\Support\Str::limit(strip_tags($insight->excerpt ?: ($insight->content ?? '')), 160))
@section('og_title', $insight->seo_title ?: $insight->title . ' - Edulaw Project')
@section('og_description', $insight->seo_description ?: \Illuminate\Support\Str::limit(strip_tags($insight->excerpt ?: ($insight->content ?? '')), 160))
@section('og_type', 'article')
@section('og_image', edulaw_file_url($insight->og_image ?: $insight->cover_image, 'images/hero/hero-edulaw.jpg'))
@section('twitter_title', $insight->seo_title ?: $insight->title . ' - Edulaw Project')
@section('twitter_description', $insight->seo_description ?: \Illuminate\Support\Str::limit(strip_tags($insight->excerpt ?: ($insight->content ?? '')), 160))
@section('twitter_image', edulaw_file_url($insight->og_image ?: $insight->cover_image, 'images/hero/hero-edulaw.jpg'))

@push('styles')
    <style>
        .insight-article-body > p:first-of-type {
            margin-top: 0;
        }

        .insight-article-body,
        .insight-article-body * {
            max-width: 100%;
            overflow-wrap: anywhere;
        }

        .insight-article-body > p:first-of-type::first-letter {
            float: left;
            margin: 0.08em 0.12em 0 0;
            color: #1f3c69;
            font-family: 'Fira Sans', ui-sans-serif, system-ui, sans-serif;
            font-size: 4.7rem;
            font-weight: 900;
            line-height: 0.78;
        }

        .insight-article-body img {
            border-radius: 1rem;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
        }

        @media (max-width: 640px) {
            .insight-article-body > p:first-of-type::first-letter {
                font-size: 4rem;
            }
        }
    </style>
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

    $coverImage = edulaw_file_url($insight->cover_image);
    $categoryName = $insight->display_category;
    $publishedDate = optional($insight->published_at)->translatedFormat('d F Y') ?? 'Belum dijadwalkan';
    $description = $insight->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($insight->content ?? ''), 180);
    $primaryAuthor = $insight->authors
        ->filter(fn ($author) => $author->is_active !== false)
        ->sortBy(fn ($author) => $author->pivot?->author_order ?? 999)
        ->first();
    $authorName = $primaryAuthor?->name ?: $insight->creator?->name ?: $insight->reviewer?->name ?: 'Edulaw Project';
    $authorInstitution = collect([$primaryAuthor?->position, $primaryAuthor?->institution])->filter()->join(' · ') ?: 'Edulaw Project';
    $authorBio = $primaryAuthor?->bio;
    $authorPhoto = $primaryAuthor?->photo_url
        ?: edulaw_file_url($insight->creator?->avatar)
        ?: edulaw_file_url($insight->reviewer?->avatar);
    $authorProfileUrl = $primaryAuthor?->slug ? route('profiles.show', $primaryAuthor->slug) : null;
    $additionalAuthorsCount = max($insight->authors->count() - 1, 0);
    $authorInitials = \Illuminate\Support\Str::of($authorName)
        ->explode(' ')
        ->filter()
        ->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))
        ->take(2)
        ->implode('');
    $relatedInsights = $relatedInsights ?? collect();
@endphp

<main class="bg-white">
    <section class="relative isolate overflow-hidden bg-brand-navy text-white">
        @if ($coverImage)
            <img
                src="{{ $coverImage }}"
                alt="{{ $insight->title }}"
                class="absolute inset-0 z-0 h-full w-full object-cover"
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

    <section class="border-b border-slate-200 bg-white py-10 lg:py-14">
        <div class="mx-auto grid max-w-6xl gap-10 px-6 lg:grid-cols-[minmax(0,760px)_280px] lg:px-8">
            <article class="min-w-0 overflow-hidden">
                <div class="mb-8 flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-3 text-xs font-bold text-slate-500">
                        <span class="edulaw-badge edulaw-badge-navy normal-case tracking-normal">
                            Artikel Editorial
                        </span>
                        <span>{{ $publishedDate }}</span>
                        <span class="hidden h-1 w-1 rounded-full bg-slate-300 sm:block"></span>
                        <span>{{ $readingTime($insight) }}</span>
                    </div>

                    <div class="flex w-full items-center justify-between gap-2 sm:w-auto sm:justify-start">
                        <span class="mr-1 hidden text-xs font-black uppercase tracking-[0.18em] text-slate-400 sm:inline">
                            Bagikan
                        </span>

                        <button
                            type="button"
                            onclick="navigator.clipboard.writeText(window.location.href)"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-brand-navy transition hover:border-brand-navy hover:bg-slate-50"
                            aria-label="Salin tautan artikel"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M10 13a5 5 0 0 0 7.07 0l2.12-2.12a5 5 0 0 0-7.07-7.07L11 4.93" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M14 11a5 5 0 0 0-7.07 0L4.81 13.12a5 5 0 0 0 7.07 7.07L13 19.07" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>

                        <a
                            href="https://wa.me/?text={{ urlencode($insight->title . ' ' . request()->fullUrl()) }}"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex h-9 items-center justify-center rounded-full bg-brand-navy px-4 text-xs font-black text-white transition hover:bg-brand-navy/90"
                        >
                            WhatsApp
                        </a>
                    </div>
                </div>

                <div class="insight-article-body prose prose-slate max-w-none prose-headings:font-black prose-headings:tracking-tight prose-headings:text-brand-navy prose-h2:mt-12 prose-h2:text-3xl prose-h3:text-2xl prose-p:text-[17px] prose-p:leading-[1.95] prose-p:text-slate-700 prose-a:font-semibold prose-a:text-brand-navy prose-strong:text-brand-navy prose-em:text-slate-700 prose-blockquote:border-l-4 prose-blockquote:border-brand-amber prose-blockquote:bg-slate-50 prose-blockquote:px-5 prose-blockquote:py-3 prose-blockquote:not-italic">
                    @if ($insight->content)
                        {!! $insight->content !!}
                    @else
                        <p>{{ $description }}</p>
                    @endif
                </div>

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

            <aside class="space-y-5 self-start lg:sticky lg:top-28">
                <div class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-brand-teal">
                        Penulis
                    </p>

                    <div class="mt-5 flex items-center gap-4">
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

                    @if ($authorBio)
                        <p class="mt-4 text-sm leading-relaxed text-slate-500">
                            {{ \Illuminate\Support\Str::limit(strip_tags($authorBio), 130) }}
                        </p>
                    @else
                        <p class="mt-4 text-sm leading-relaxed text-slate-500">
                            Editorial ini disusun untuk memperkuat literasi hukum yang mudah dipahami, relevan, dan bertanggung jawab.
                        </p>
                    @endif
                </div>

                @if ($relatedInsights->isNotEmpty())
                    <div class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-brand-navy/60">
                            Baca Juga
                        </p>

                        <div class="mt-5 space-y-4">
                            @foreach ($relatedInsights->take(3) as $relatedInsight)
                                <a href="{{ route('insights.show', $relatedInsight->slug) }}" class="group block border-b border-slate-100 pb-4 last:border-b-0 last:pb-0">
                                    <p class="line-clamp-2 text-sm font-black leading-snug text-brand-navy transition group-hover:text-brand-teal">
                                        {{ $relatedInsight->title }}
                                    </p>
                                    <p class="mt-2 text-[11px] font-semibold text-slate-400">
                                        {{ optional($relatedInsight->published_at)->translatedFormat('d M Y') }}
                                        <span class="mx-1">&middot;</span>
                                        {{ $readingTime($relatedInsight) }}
                                    </p>
                                </a>
                            @endforeach
                        </div>
                    </div>
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

    @if ($relatedInsights->isNotEmpty())
        <section class="bg-white py-12 lg:py-14">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                            Baca Juga
                        </p>

                        <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-brand-ink sm:text-4xl">
                            Editorial Terkait
                        </h2>
                    </div>

                    <a
                        href="{{ route('insights.index') }}"
                        class="inline-flex w-fit items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-brand-ink shadow-sm transition hover:border-brand-silver hover:bg-brand-paper"
                    >
                        Lihat Semua Editorial
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>

                <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($relatedInsights as $item)
                        <article class="group flex h-full flex-col overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/10">
                            <a href="{{ route('insights.show', $item->slug) }}" class="block overflow-hidden bg-slate-100">
                                @if (edulaw_file_url($item->cover_image))
                                    <img
                                        src="{{ edulaw_file_url($item->cover_image) }}"
                                        alt="{{ $item->title }}"
                                        class="aspect-[16/10] w-full object-cover transition duration-500 group-hover:scale-105"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="aspect-[16/10] w-full bg-linear-to-br from-brand-navy to-slate-800"></div>
                                @endif
                            </a>

                            <div class="flex flex-1 flex-col p-5">
                                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-teal">
                                    {{ $item->display_category }}
                                </p>

                                <h3 class="mt-3 line-clamp-2 text-base font-black leading-snug text-brand-navy">
                                    <a href="{{ route('insights.show', $item->slug) }}">
                                        {{ $item->title }}
                                    </a>
                                </h3>

                                @if ($item->excerpt)
                                    <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-slate-500">
                                        {{ $item->excerpt }}
                                    </p>
                                @endif

                                <div class="mt-auto pt-5 text-xs font-semibold text-slate-400">
                                    {{ optional($item->published_at)->translatedFormat('d M Y') }}
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
