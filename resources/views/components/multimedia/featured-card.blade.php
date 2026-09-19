@props([
    'item',
    'variant' => 'page',
])

@php
    $isHome = $variant === 'home';
    $date = $item->published_at?->locale('id')->translatedFormat('d M Y') ?: 'Kanal resmi Edulaw';
    $meta = collect([$date, filled($item->duration) ? $item->duration : null])->filter()->join(' · ');
    $summary = trim(strip_tags((string) $item->description));
    $fallbackThumbnail = $item->youtube_thumbnail_fallback_url;
    $itemUrl = $item->public_url;
    $opensExternally = $item->opens_externally;
@endphp

@if ($isHome)
    <article data-home-multimedia data-home-multimedia-featured {{ $attributes->class('group min-w-0 overflow-hidden rounded-xl bg-brand-navy') }}>
        <a href="{{ $itemUrl }}" @if ($opensExternally) target="_blank" rel="noopener noreferrer" @endif aria-label="Tonton {{ $item->title }} {{ $opensExternally ? 'di YouTube (membuka tab baru)' : '' }}" class="block focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-navy">
            <div class="relative aspect-video min-h-[320px] overflow-hidden bg-linear-to-br from-brand-navy via-[#123d68] to-[#28659d] sm:min-h-0">
                <div class="absolute inset-0 grid place-items-center text-white/55" aria-hidden="true">
                    <svg class="h-14 w-14" viewBox="0 0 24 24" fill="none"><path d="M8 5v14l11-7L8 5Z" stroke="currentColor" stroke-width="1.7"/></svg>
                </div>

                @if ($item->thumbnail_url)
                    <x-responsive-image
                        :src="$item->thumbnail_url"
                        :alt="$item->title"
                        :widths="[640, 960, 1280]"
                        sizes="(min-width: 1280px) 588px, (min-width: 1024px) 48vw, 100vw"
                        data-fallback="{{ $fallbackThumbnail }}"
                        onerror="if (this.dataset.fallback) { this.src = this.dataset.fallback; this.dataset.fallback = ''; } else { this.remove(); }"
                        class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.025]"
                    />
                @endif

                <div class="absolute inset-0 bg-slate-950/10"></div>
                <div class="absolute inset-0 bg-linear-to-t from-slate-950/95 via-slate-900/65 via-55% to-transparent"></div>
                <x-multimedia.platform-badge platform="youtube" :dark="true" class="absolute left-4 top-4" />

                <span role="img" aria-label="Putar video {{ $item->title }}" class="absolute left-1/2 top-[34%] flex h-14 w-14 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full border border-white/30 bg-white/92 text-brand-navy shadow-xl backdrop-blur transition group-hover:scale-105 group-hover:bg-brand-amber">
                    <svg class="ml-0.5 h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7L8 5Z"/></svg>
                </span>

                <div class="absolute inset-x-0 bottom-0 p-6 sm:p-8">
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-[#f0c55e]">Video Pilihan</p>
                    <h3 class="type-role-card mt-2 line-clamp-2 text-2xl font-bold leading-[1.2] text-white drop-shadow-sm sm:text-3xl">{{ $item->title }}</h3>
                    <p class="mt-3 type-role-meta font-normal text-white/75">{{ $meta }}</p>
                    <span class="mt-4 inline-flex text-sm font-bold text-white">Tonton Video →</span>
                </div>
            </div>
        </a>
    </article>
@else
    <article data-featured-media data-channel-feature-card {{ $attributes->class('channel-feature-card group min-w-0 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-slate-900/10') }}>
        <a href="{{ $itemUrl }}" @if ($opensExternally) target="_blank" rel="noopener noreferrer" @endif aria-label="Tonton {{ $item->title }} {{ $opensExternally ? 'di YouTube (membuka tab baru)' : '' }}" class="grid h-full focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-navy lg:grid-cols-[minmax(0,40fr)_minmax(0,60fr)]">
            <div class="relative flex min-h-[300px] items-center justify-center overflow-hidden bg-white sm:min-h-[340px] lg:aspect-video lg:h-auto lg:min-h-0 lg:self-center">
                <div class="absolute inset-0 grid place-items-center text-brand-navy/35" aria-hidden="true">
                    <svg class="h-14 w-14" viewBox="0 0 24 24" fill="none"><path d="M8 5v14l11-7L8 5Z" stroke="currentColor" stroke-width="1.7"/></svg>
                </div>

                @if ($item->thumbnail_url)
                    <x-responsive-image
                        :src="$item->thumbnail_url"
                        :alt="$item->title"
                        :widths="[480, 640, 960]"
                        sizes="(min-width: 1024px) 53vw, 100vw"
                        data-fallback="{{ $fallbackThumbnail }}"
                        onerror="if (this.dataset.fallback) { this.src = this.dataset.fallback; this.dataset.fallback = ''; } else { this.remove(); }"
                        class="relative z-10 h-full w-full object-contain transition duration-700 group-hover:scale-[1.025]"
                    />
                @endif

                <div class="absolute inset-0 z-10 bg-linear-to-t from-slate-950/10 to-transparent"></div>
                <x-multimedia.platform-badge platform="youtube" class="absolute left-4 top-4 z-20" />
                <span role="img" aria-label="Putar video {{ $item->title }}" class="absolute inset-0 z-20 m-auto flex h-16 w-16 items-center justify-center rounded-full bg-white/95 text-brand-navy shadow-xl transition group-hover:scale-105 group-hover:bg-brand-amber">
                    <svg class="ml-0.5 h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7L8 5Z"/></svg>
                </span>
            </div>

            <div class="flex min-w-0 flex-col justify-center channel-feature-content">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="channel-feature-badge bg-[#EAF2FF] text-brand-navy">YouTube</span>
                    <span class="channel-feature-badge bg-[#DFF7EF] text-[#087B65]">Featured</span>
                </div>
                <h2 class="type-role-feature channel-feature-title">{{ $item->title }}</h2>
                @if ($summary !== '')
                    <p class="channel-feature-summary">{{ $summary }}</p>
                @endif
                <p class="channel-feature-meta">{{ $date }}</p>
                <div class="channel-feature-actions">
                    <span class="channel-feature-primary-action">
                        Tonton Video
                        <svg class="h-4 w-4 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </div>
            </div>
        </a>
    </article>
@endif
