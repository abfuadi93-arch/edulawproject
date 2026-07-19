@props(['block', 'index', 'categoryName', 'publishedDate'])

@php($items = collect($block['items'] ?? [])->take(3)->values())
@if ($items->isNotEmpty())
    @php($featured = $items->first())

    <article class="flex h-full min-w-0 flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm shadow-brand-ink/5" data-editorial-category-block="{{ $block['title'] }}">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand-coral">Kategori</p>
                <h3 class="mt-1 text-balance font-display text-xl font-bold text-brand-ink sm:text-2xl">{{ $block['title'] }}</h3>
                @if (filled($block['description'] ?? null))
                    <p class="mt-1.5 line-clamp-1 text-sm leading-6 text-slate-500">{{ $block['description'] }}</p>
                @endif
            </div>
            <a href="{{ $block['url'] }}" class="inline-flex min-h-11 shrink-0 items-center rounded-full border border-brand-amber/40 px-3 text-xs font-bold text-brand-navy transition duration-200 hover:border-brand-amber hover:bg-brand-amber-soft focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">Lihat Kategori</a>
        </div>

        <div class="group mt-4" data-category-featured="{{ $featured->id }}">
            <a href="{{ route('insights.show', $featured->slug) }}" class="rounded-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">
                <div class="relative h-40 overflow-hidden rounded-xl bg-slate-100 sm:h-44">
                    @if (edulaw_file_exists($featured->cover_image))
                        <img src="{{ $featured->cover_image_url }}" alt="{{ $featured->title }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.02] motion-reduce:transition-none">
                    @else
                        <div class="absolute inset-0 bg-linear-to-br from-brand-navy via-[#244972] to-[#0f172a]"></div>
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_18%,rgba(255,255,255,0.16),transparent_28%)]"></div>
                    @endif
                </div>
                <p class="mt-3 text-[10px] font-bold uppercase tracking-[0.1em] text-brand-coral">{{ $publishedDate($featured) }}</p>
                <h4 class="mt-1.5 line-clamp-2 text-lg font-bold leading-snug text-brand-ink transition group-hover:text-brand-navy">{{ $featured->title }}</h4>
            </a>
        </div>

        <div class="mt-4 divide-y divide-slate-200/80 border-t border-slate-200/80">
            @foreach ($items->skip(1) as $article)
                <a href="{{ route('insights.show', $article->slug) }}" class="group block py-3 text-sm font-semibold leading-snug text-brand-ink transition hover:text-brand-navy focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber" data-category-list-item="{{ $article->id }}">
                    <span class="mb-1 block text-[10px] font-bold uppercase tracking-[0.1em] text-slate-400">{{ $publishedDate($article) }}</span>
                    <span class="line-clamp-2">{{ $article->title }}</span>
                </a>
            @endforeach
        </div>
    </article>
@endif
