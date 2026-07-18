@props(['block', 'index', 'categoryName', 'publishedDate'])

@php($items = collect($block['items'] ?? [])->take(3)->values())
<section class="flex h-full min-w-0 flex-col border-t-2 border-brand-navy pt-4">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <h3 class="text-balance font-display text-xl font-bold text-brand-ink sm:text-2xl">{{ $block['title'] }}</h3>
            @if (filled($block['description'] ?? null))
                <p class="mt-1.5 line-clamp-2 text-sm leading-6 text-slate-500">{{ $block['description'] }}</p>
            @endif
        </div>
        <a href="{{ $block['url'] }}" class="shrink-0 pt-1 text-xs font-bold text-brand-coral underline-offset-4 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">Lihat kategori</a>
    </div>

    @if ($items->isNotEmpty())
        @php($featured = $items->first())
        <article class="group mt-4">
            <a href="{{ route('insights.show', $featured->slug) }}" class="rounded-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">
                <div class="aspect-[16/8] overflow-hidden rounded-xl bg-slate-100">
                    <img src="{{ $featured->cover_image_url }}" alt="{{ $featured->title }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.02] motion-reduce:transition-none">
                </div>
                <p class="mt-3 text-[10px] font-bold uppercase tracking-[0.1em] text-brand-coral">{{ $categoryName($featured) }} · {{ $publishedDate($featured) }}</p>
                <h4 class="mt-1.5 line-clamp-3 text-lg font-bold leading-snug text-brand-ink transition group-hover:text-brand-navy">{{ $featured->title }}</h4>
            </a>
        </article>

        <div class="mt-4 divide-y divide-slate-200/80 border-t border-slate-200/80">
            @foreach ($items->skip(1) as $article)
                <a href="{{ route('insights.show', $article->slug) }}" class="block py-3 text-sm font-semibold leading-snug text-brand-ink transition hover:text-brand-navy focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                    <span class="line-clamp-2">{{ $article->title }}</span>
                </a>
            @endforeach
        </div>
    @else
        <p class="mt-4 text-sm text-slate-500">Tulisan kategori ini akan tampil setelah tersedia.</p>
    @endif
</section>
