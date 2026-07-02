@props([
    'featuredInsight' => null,
    'insights' => collect(),
])

@php
    $insightCollection = collect($insights);

    $featured = $featuredInsight ?: $insightCollection->first();

    $list = $insightCollection
        ->when($featured && isset($featured->id), fn ($collection) => $collection->where('id', '!=', $featured->id))
        ->take(3)
        ->values();

    $fallbackImage = 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1200&q=85';

    $smallFallbacks = [
        'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=800&q=85',
        'https://images.unsplash.com/photo-1505664194779-8beaceb93744?auto=format&fit=crop&w=800&q=85',
        'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=800&q=85',
    ];

    $categories = [
        'Semua',
        'Law 101',
        'Regulatory Update',
        'Constitution & Governance',
        'Legal Insight',
    ];

    $primaryAuthor = function ($insight) {
        return $insight && $insight->relationLoaded('authors')
            ? $insight->authors->sortBy(fn ($author) => $author->pivot?->author_order ?? 999)->first()
            : null;
    };

    $authorInitials = function (string $name): string {
        return \Illuminate\Support\Str::of($name)
            ->explode(' ')
            ->filter()
            ->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))
            ->take(2)
            ->implode('') ?: 'E';
    };
@endphp

<section class="bg-[#F3F6F8] py-7 lg:py-9">
    <div class="section-shell">
        {{-- Header --}}
        <div>
            <div class="flex items-center justify-between gap-4">
                <p class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.18em] text-brand-coral shadow-sm ring-1 ring-slate-200">
                    <span class="h-2 w-2 rounded-full bg-brand-coral"></span>
                    Insight Edulaw
                </p>

                <a
                    href="{{ route('insights.index') }}"
                    class="inline-flex items-center gap-2 text-sm font-extrabold text-brand-ink transition hover:text-brand-navy"
                >
                    Lihat Semua Insight
                    <svg class="h-4 w-4 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>

            <div class="mt-1.5 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="min-w-0">
                    <h2 class="text-2xl font-extrabold tracking-tight text-brand-ink sm:text-3xl lg:text-[2rem]">
                        Insight Terbaru
                    </h2>

                    <p class="mt-2 max-w-none text-sm leading-6 text-slate-600 lg:whitespace-nowrap">
                        Analisis hukum, pembaruan regulasi, dan isu kebijakan publik yang disajikan secara jernih.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                    @foreach ($categories as $category)
                        <a
                            href="{{ route('insights.index', ['category' => Str::slug($category)]) }}"
                            class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-extrabold text-slate-600 shadow-sm transition hover:border-brand-black hover:bg-brand-black hover:text-white"
                        >
                            {{ $category }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        @if ($featured)
            <div class="mt-5 grid gap-4 lg:grid-cols-[1.08fr_0.92fr]">
                {{-- Featured Insight --}}
                <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-brand-ink/10">
                    <a href="{{ route('insights.show', $featured->slug) }}" class="block h-full">
                        <div class="relative h-67.5 overflow-hidden bg-slate-100 sm:h-80 lg:h-87.5">
                            <img
                                src="{{ $featured->cover_image_url ?: $fallbackImage }}"
                                alt="{{ $featured->title }}"
                                class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                                loading="lazy"
                            >

                            <div class="absolute inset-0 bg-linear-to-t from-brand-navy/82 via-brand-navy/18 to-transparent"></div>

                            <div class="absolute left-4 top-4">
                                <span class="inline-flex items-center rounded-md bg-brand-coral px-3 py-1 text-[10px] font-black uppercase tracking-[0.13em] text-white shadow-sm">
                                    {{ $featured->display_category }}
                                </span>
                            </div>

                            <div class="absolute bottom-4 left-4 right-4">
                                <h3 class="max-w-2xl text-2xl font-black leading-tight tracking-tight text-white sm:text-3xl lg:text-[2rem]">
                                    {{ $featured->title }}
                                </h3>
                            </div>
                        </div>

                        <div class="flex min-h-52.5 flex-col p-4 sm:p-5">
                            <p class="line-clamp-3 text-[15px] leading-6 text-slate-600">
                                {{ $featured->excerpt }}
                            </p>

                            @php
                                $featuredAuthor = $primaryAuthor($featured);
                                $featuredAuthorName = $featuredAuthor?->name ?: $featured->display_author;
                            @endphp

                            <div class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-2 text-xs font-semibold text-slate-500">
                                <span>{{ optional($featured->published_at)->translatedFormat('d M Y') ?: '-' }}</span>
                                <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                <span>{{ $featured->reading_time ? $featured->reading_time.' min read' : '-' }}</span>
                                <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                <span class="inline-flex max-w-full items-center gap-2">
                                    @if ($featuredAuthor?->photo_url)
                                        <img
                                            src="{{ $featuredAuthor->photo_url }}"
                                            alt="Foto profil {{ $featuredAuthorName }}"
                                            class="h-6 w-6 shrink-0 rounded-full object-cover ring-1 ring-slate-200"
                                            loading="lazy"
                                        >
                                    @else
                                        <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-navy text-[9px] font-black text-white">
                                            {{ $authorInitials($featuredAuthorName) }}
                                        </span>
                                    @endif

                                    <span class="min-w-0">{{ $featuredAuthorName }}</span>
                                </span>
                            </div>

                            <div class="mt-auto flex items-center justify-between gap-4 border-t border-slate-100 pt-5">
                                <span class="text-sm font-black text-brand-ink">
                                    Baca Insight
                                </span>

                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-brand-black text-white transition group-hover:bg-brand-amber group-hover:text-brand-black">
                                    <svg class="h-4 w-4 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                </article>

                {{-- Right Side: 3 Cards --}}
                <div class="grid gap-4">
                    @foreach ($list as $item)
                        @php
                            $fallbackIndex = $loop->index % count($smallFallbacks);
                            $thumb = $item->cover_image_url ?: $smallFallbacks[$fallbackIndex];
                        @endphp

                        <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-brand-ink/10">
                            <a href="{{ route('insights.show', $item->slug) }}" class="grid min-h-37.5 grid-cols-[132px_1fr] sm:grid-cols-[170px_1fr]">
                                <div class="relative overflow-hidden bg-slate-100">
                                    <img
                                        src="{{ $thumb }}"
                                        alt="{{ $item->title }}"
                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                        loading="lazy"
                                    >

                                    <div class="absolute inset-0 bg-brand-navy/10"></div>
                                </div>

                                <div class="flex min-h-full flex-col p-3.5 sm:p-4">
                                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-brand-teal">
                                        {{ $item->display_category }}
                                    </p>

                                    <h3 class="mt-1.5 line-clamp-2 text-[15px] font-black leading-snug tracking-tight text-brand-ink transition group-hover:text-brand-navy sm:text-base">
                                        {{ $item->title }}
                                    </h3>

                                    <p class="mt-1.5 line-clamp-2 text-xs leading-5 text-slate-600 sm:text-sm">
                                        {{ $item->excerpt }}
                                    </p>

                                    <div class="mt-auto flex flex-wrap items-center gap-x-2 gap-y-1 pt-2 text-[11px] font-semibold text-slate-500">
                                        <span>{{ optional($item->published_at)->translatedFormat('d M Y') ?: '-' }}</span>
                                        <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                        <span>{{ $item->reading_time ? $item->reading_time.' min read' : '-' }}</span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    @endforeach

                    {{-- Keep right side visually filled until 3 cards --}}
                    @for ($i = $list->count(); $i < 3; $i++)
                        <div class="flex min-h-37.5 items-center rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4">
                            <div>
                                <p class="text-sm font-black text-brand-ink">
                                    Insight lainnya sedang disiapkan.
                                </p>
                                <p class="mt-1 text-sm leading-5 text-slate-500">
                                    Konten terbaru akan tampil otomatis setelah dipublikasikan.
                                </p>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        @else
            <div class="mt-5 flex min-h-55 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                <div>
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-brand-navy text-brand-amber">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M7 3h7l4 4v14H7V3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M14 3v5h5M10 13h6M10 17h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>

                    <h3 class="mt-4 text-lg font-black text-brand-ink">
                        Belum ada insight dipublikasikan.
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Insight yang sudah berstatus published akan tampil di sini.
                    </p>
                </div>
            </div>
        @endif
    </div>
</section>
