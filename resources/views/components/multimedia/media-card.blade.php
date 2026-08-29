@props([
    'item',
    'variant' => 'grid',
])

@php
    $isHorizontal = $variant === 'horizontal';
    $type = \App\Models\Multimedia::normalizeType($item->type);
    $platform = match (true) {
        $item->platform === 'instagram' => 'instagram',
        in_array($type, ['gallery', 'documentation'], true) => 'google_photos',
        default => 'youtube',
    };
    $platformLabel = match ($platform) {
        'instagram' => 'Instagram',
        'google_photos' => 'Google Photos',
        default => 'YouTube',
    };
    $date = $item->published_at?->locale('id')->translatedFormat('d M Y') ?: $item->display_type;
    $meta = collect([$date, filled($item->duration) ? $item->duration : null])->filter()->join(' · ');
    $ctaLabel = match (true) {
        $platform === 'instagram' => 'Lihat di Instagram →',
        $platform === 'google_photos' => 'Buka Album →',
        $type === 'podcast' => 'Dengarkan Podcast →',
        default => 'Tonton Video →',
    };
    $fallbackThumbnail = $platform === 'youtube' ? $item->youtube_thumbnail_fallback_url : null;
@endphp

<article
    @if ($isHorizontal) data-home-multimedia @endif
    @if ($isHorizontal) data-home-multimedia-secondary @else data-secondary-media @endif
    {{ $attributes->class([
        'group min-w-0 overflow-hidden rounded-xl border border-[#e7ebf0] bg-white transition duration-300 hover:border-slate-300',
        'h-full' => ! $isHorizontal,
    ]) }}
>
    <a href="{{ $item->media_url }}" target="_blank" rel="noopener noreferrer" aria-label="Buka {{ $item->title }} di {{ $platformLabel }} (membuka tab baru)" @class(['focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy', 'flex h-full items-center gap-3 p-3' => $isHorizontal, 'flex h-full flex-col' => ! $isHorizontal])>
        <div @class(['relative shrink-0 overflow-hidden', 'aspect-video w-28 rounded-xl bg-linear-to-br from-brand-navy via-[#123d68] to-[#28659d]' => $isHorizontal, 'aspect-video w-full bg-white' => ! $isHorizontal])>
            <div @class(['absolute inset-0 grid place-items-center', 'text-white/55' => $isHorizontal, 'text-brand-navy/35' => ! $isHorizontal]) aria-hidden="true">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none"><path d="M8 5v14l11-7L8 5Z" stroke="currentColor" stroke-width="1.7"/></svg>
            </div>

            @if ($item->thumbnail_url)
                <x-responsive-image
                    :src="$item->thumbnail_url"
                    :alt="$item->title"
                    :widths="$isHorizontal ? [160, 240, 320] : [320, 480, 640]"
                    :sizes="$isHorizontal ? '112px' : '(min-width: 1024px) 33vw, 100vw'"
                    data-fallback="{{ $fallbackThumbnail }}"
                    onerror="if (this.dataset.fallback) { this.src = this.dataset.fallback; this.dataset.fallback = ''; } else { this.remove(); }"
                    @class(['absolute inset-0 h-full w-full transition duration-500 group-hover:scale-[1.03]', 'object-cover' => $isHorizontal, 'object-contain bg-white' => ! $isHorizontal])
                />
            @endif

            @unless ($isHorizontal)
                <x-multimedia.platform-badge :platform="$platform" :label="$platformLabel" :dark="true" class="absolute left-3 top-3" />
            @endunless
        </div>

        <div @class(['min-w-0 flex-1', 'py-1 pr-1' => $isHorizontal, 'flex w-full flex-1 items-start justify-between gap-3 p-4' => ! $isHorizontal])>
            <div class="min-w-0">
                @if ($isHorizontal)
                    <x-multimedia.platform-badge :platform="$platform" :label="$platformLabel" class="mb-1.5" />
                @endif
                <h3 class="line-clamp-2 text-base font-black leading-snug text-brand-ink transition group-hover:text-brand-navy">{{ $item->title }}</h3>
                <p class="mt-1.5 text-xs font-bold text-slate-500">{{ $meta }}</p>
                <p class="mt-2 text-[11px] font-extrabold text-brand-navy">{{ $ctaLabel }}</p>
            </div>
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-navy transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
    </a>
</article>
