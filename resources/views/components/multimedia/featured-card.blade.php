@props([
    'item',
    'variant' => 'page',
])

@php
    $isHome = $variant === 'home';
    $date = $item->published_at?->locale('id')->translatedFormat('d M Y') ?: 'Kanal resmi Edulaw';
    $meta = collect([$date, filled($item->duration) ? $item->duration : null])->filter()->join(' · ');
    $summary = trim(strip_tags((string) $item->description)) ?: 'Pembahasan hukum pilihan dari kanal resmi Edulaw Project.';
    $fallbackThumbnail = $item->youtube_thumbnail_fallback_url;
@endphp

@if ($isHome)
    <article data-home-multimedia data-home-multimedia-featured {{ $attributes->class('group min-w-0 overflow-hidden rounded-xl bg-brand-navy shadow-[0_22px_54px_-36px_rgba(15,23,42,.65)]') }}>
        <a href="{{ $item->media_url }}" target="_blank" rel="noopener noreferrer" aria-label="Tonton {{ $item->title }} di YouTube (membuka tab baru)" class="block h-full focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-navy">
            <div class="relative aspect-video h-full overflow-hidden bg-linear-to-br from-brand-navy via-[#123d68] to-[#28659d] lg:aspect-auto lg:min-h-[340px]">
                <div class="absolute inset-0 grid place-items-center text-white/55" aria-hidden="true">
                    <svg class="h-14 w-14" viewBox="0 0 24 24" fill="none"><path d="M8 5v14l11-7L8 5Z" stroke="currentColor" stroke-width="1.7"/></svg>
                </div>

                @if ($item->thumbnail_url)
                    <x-responsive-image
                        :src="$item->thumbnail_url"
                        :alt="$item->title"
                        :widths="[640, 960, 1280]"
                        sizes="(min-width: 1024px) 845px, 100vw"
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
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.12em] text-[#f0c55e]">Video Pilihan</p>
                    <h3 class="mt-2 line-clamp-2 text-2xl font-black leading-[1.2] text-white drop-shadow-sm sm:text-3xl">{{ $item->title }}</h3>
                    <p class="mt-3 text-xs font-bold text-white/75">{{ $meta }}</p>
                    <span class="mt-4 inline-flex text-sm font-black text-white">Tonton Video →</span>
                </div>
            </div>
        </a>
    </article>
@else
    <article data-featured-media {{ $attributes->class('group min-w-0 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-slate-900/10') }}>
        <a href="{{ $item->media_url }}" target="_blank" rel="noopener noreferrer" aria-label="Tonton {{ $item->title }} di YouTube (membuka tab baru)" class="grid focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-navy lg:grid-cols-[minmax(0,3fr)_minmax(320px,2fr)]">
            <div class="relative flex min-h-[300px] items-center justify-center overflow-hidden bg-white sm:min-h-[340px] lg:min-h-[390px]">
                <div class="absolute inset-0 grid place-items-center text-brand-navy/35" aria-hidden="true">
                    <svg class="h-14 w-14" viewBox="0 0 24 24" fill="none"><path d="M8 5v14l11-7L8 5Z" stroke="currentColor" stroke-width="1.7"/></svg>
                </div>

                @if ($item->thumbnail_url)
                    <x-responsive-image
                        :src="$item->thumbnail_url"
                        :alt="$item->title"
                        :widths="[480, 640, 960]"
                        sizes="(min-width: 1024px) 60vw, 100vw"
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

            <div class="flex min-w-0 flex-col justify-center p-5 sm:p-7 lg:p-8">
                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-brand-coral">Video Utama</p>
                <h3 class="line-clamp-3 mt-2 text-2xl font-black leading-tight text-brand-ink lg:text-[1.75rem]">{{ $item->title }}</h3>
                <p class="line-clamp-3 mt-3 text-base leading-7 text-slate-600">{{ $summary }}</p>
                <p class="mt-4 text-xs font-bold text-slate-500">{{ $date }}</p>
                <span class="mt-5 inline-flex min-h-11 w-fit items-center gap-2 rounded-full bg-brand-amber px-5 py-2.5 text-sm font-black text-brand-ink transition group-hover:bg-brand-navy group-hover:text-white">
                    Tonton di YouTube
                    <svg class="h-4 w-4 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
            </div>
        </a>
    </article>
@endif
