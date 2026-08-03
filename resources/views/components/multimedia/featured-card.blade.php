@props([
    'item',
    'variant' => 'page',
])

@php
    $isHome = $variant === 'home';
    $date = $item->published_at?->locale('id')->translatedFormat('d M Y') ?: 'Kanal resmi Edulaw';
    $summary = trim(strip_tags((string) $item->description)) ?: 'Pembahasan hukum pilihan dari kanal resmi Edulaw Project.';
    $fallbackThumbnail = $item->youtube_thumbnail_fallback_url;
@endphp

@if ($isHome)
    <article data-home-multimedia data-home-multimedia-featured {{ $attributes->class('group min-w-0 overflow-hidden rounded-3xl border border-slate-200 bg-brand-navy shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-brand-navy/10') }}>
        <a href="{{ $item->media_url }}" target="_blank" rel="noopener noreferrer" aria-label="Tonton {{ $item->title }} di YouTube (membuka tab baru)" class="block focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-navy">
            <div class="relative h-72 overflow-hidden bg-linear-to-br from-brand-navy via-[#123d68] to-[#28659d] lg:h-[300px]">
                <div class="absolute inset-0 grid place-items-center text-white/55" aria-hidden="true">
                    <svg class="h-14 w-14" viewBox="0 0 24 24" fill="none"><path d="M8 5v14l11-7L8 5Z" stroke="currentColor" stroke-width="1.7"/></svg>
                </div>

                @if ($item->thumbnail_url)
                    <img
                        src="{{ $item->thumbnail_url }}"
                        data-fallback="{{ $fallbackThumbnail }}"
                        onerror="if (this.dataset.fallback) { this.src = this.dataset.fallback; this.dataset.fallback = ''; } else { this.remove(); }"
                        alt="{{ $item->title }}"
                        class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.025]"
                    >
                @endif

                <div class="absolute inset-x-0 bottom-0 h-4/5 bg-linear-to-t from-brand-navy/95 via-brand-navy/35 to-transparent"></div>
                <x-multimedia.platform-badge platform="youtube" :dark="true" class="absolute left-4 top-4" />

                <div class="absolute inset-x-0 bottom-0 p-5 sm:p-6">
                    <p class="text-xs font-bold text-white/70">{{ $date }}</p>
                    <h3 class="line-clamp-2 mt-2 text-xl font-black leading-tight text-white sm:text-2xl">{{ $item->title }}</h3>
                    <p class="mt-2 hidden line-clamp-2 text-sm leading-6 text-white/75 lg:block">{{ $summary }}</p>
                    <span class="mt-4 inline-flex items-center gap-2 text-sm font-black text-brand-amber">
                        Tonton Video
                        <svg class="h-4 w-4 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </div>
            </div>
        </a>
    </article>
@else
    <article data-featured-media {{ $attributes->class('group min-w-0 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-slate-900/10') }}>
        <a href="{{ $item->media_url }}" target="_blank" rel="noopener noreferrer" aria-label="Tonton {{ $item->title }} di YouTube (membuka tab baru)" class="grid focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-navy lg:grid-cols-[minmax(0,3fr)_minmax(320px,2fr)]">
            <div class="relative aspect-video overflow-hidden bg-linear-to-br from-brand-navy via-[#123d68] to-[#28659d]">
                <div class="absolute inset-0 grid place-items-center text-white/55" aria-hidden="true">
                    <svg class="h-14 w-14" viewBox="0 0 24 24" fill="none"><path d="M8 5v14l11-7L8 5Z" stroke="currentColor" stroke-width="1.7"/></svg>
                </div>

                @if ($item->thumbnail_url)
                    <img
                        src="{{ $item->thumbnail_url }}"
                        data-fallback="{{ $fallbackThumbnail }}"
                        onerror="if (this.dataset.fallback) { this.src = this.dataset.fallback; this.dataset.fallback = ''; } else { this.remove(); }"
                        alt="{{ $item->title }}"
                        class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.025]"
                    >
                @endif

                <div class="absolute inset-0 bg-brand-navy/5"></div>
                <x-multimedia.platform-badge platform="youtube" :dark="true" class="absolute left-4 top-4" />
                <span role="img" aria-label="Putar video {{ $item->title }}" class="absolute inset-0 m-auto flex h-14 w-14 items-center justify-center rounded-full bg-white/92 text-brand-navy shadow-xl transition group-hover:scale-105 group-hover:bg-brand-amber">
                    <svg class="ml-0.5 h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7L8 5Z"/></svg>
                </span>
            </div>

            <div class="flex min-w-0 flex-col justify-center p-5 sm:p-7 lg:p-8">
                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-brand-coral">Video Utama</p>
                <h3 class="line-clamp-3 mt-2 text-2xl font-black leading-tight text-brand-ink lg:text-[1.75rem]">{{ $item->title }}</h3>
                <p class="line-clamp-3 mt-3 text-sm leading-6 text-slate-600">{{ $summary }}</p>
                <p class="mt-4 text-xs font-bold text-slate-500">{{ $date }}</p>
                <span class="mt-5 inline-flex min-h-11 w-fit items-center gap-2 rounded-full bg-brand-amber px-5 py-2.5 text-sm font-black text-brand-ink transition group-hover:bg-brand-navy group-hover:text-white">
                    Tonton di YouTube
                    <svg class="h-4 w-4 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
            </div>
        </a>
    </article>
@endif
