@props(['block'])

@php
    $featured = collect($block['items'] ?? [])->first();
    $hasImage = filled($featured?->cover_image) && edulaw_file_exists($featured?->cover_image);
@endphp

@if ($featured)
    <article class="group min-w-0" data-editorial-category-block="{{ $block['title'] }}">
        <a href="{{ $block['url'] }}" class="block focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-brand-amber">
            <div class="relative aspect-[16/10] overflow-hidden rounded-[13px] bg-brand-navy" data-category-featured="{{ $featured->id }}">
                @if ($hasImage)
                    <img src="{{ $featured->cover_image_url }}" alt="{{ $block['title'] }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.025] motion-reduce:transition-none">
                @else
                    <div class="absolute inset-0 bg-linear-to-br from-brand-navy via-[#244972] to-[#0f766e]"></div>
                @endif
            </div>
            <div class="pt-4">
                <div class="flex items-start justify-between gap-3">
                    <h3 class="font-display text-lg font-black leading-tight text-brand-navy">{{ $block['title'] }}</h3>
                    <span class="shrink-0 text-[11px] font-extrabold uppercase tracking-[0.08em] text-slate-500">{{ number_format((int) ($block['article_count'] ?? 0), 0, ',', '.') }} artikel</span>
                </div>
                @if (filled($block['description'] ?? null))
                    <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">{{ $block['description'] }}</p>
                @endif
                <span class="mt-3 inline-flex text-xs font-extrabold text-brand-navy">Jelajahi kategori <span class="ml-1" aria-hidden="true">→</span></span>
            </div>
        </a>
    </article>
@endif
