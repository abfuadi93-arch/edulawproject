@props([
    'channel' => [],
])

@php
    $articles = collect($channel['articles'] ?? []);
    $label = $channel['label'] ?? 'Editorial';
    $description = $channel['description'] ?? 'Kumpulan artikel dan analisis dari kanal Editorial Edulaw.';
    $articleCount = (int) ($channel['article_count'] ?? $articles->count());
    $url = $channel['url'] ?? route('insights.index');
    $icon = $channel['icon'] ?? 'spark';
    $visualClass = match ($icon) {
        'book' => 'from-[#146B5F] to-[#0E4F47]',
        'document' => 'from-[#2B1E5F] to-[#49347D]',
        'spark' => 'from-[#6A3D05] to-[#9B6C1E]',
        default => 'from-[#061A3D] to-[#1E3763]',
    };
@endphp

<article class="group grid gap-5 rounded-2xl border border-slate-200 bg-white p-5 transition duration-[250ms] ease-out hover:-translate-y-0.5 hover:border-brand-navy/30 hover:shadow-[0_14px_34px_rgba(15,23,42,0.07)] sm:grid-cols-[4rem_minmax(0,1fr)]">
    <div class="grid h-14 w-14 place-items-center rounded-2xl bg-linear-to-br {{ $visualClass }} text-white">
        @switch($icon)
            @case('book')
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M6 4h9a3 3 0 0 1 3 3v13H8a3 3 0 0 0-3-3V5a1 1 0 0 1 1-1Zm2 0v13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                @break

            @case('document')
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M7 3h7l4 4v14H7V3Zm7 0v5h4M9.5 12h5M9.5 16h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                @break

            @case('spark')
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="m12 3 1.7 5.2H19l-4.3 3.1 1.6 5.2L12 13.3l-4.3 3.2 1.6-5.2L5 8.2h5.3L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                </svg>
                @break

            @default
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 20h14M7 9v8m5-8v8m5-8v8M4 7l8-4 8 4H4Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
        @endswitch
    </div>

    <div class="min-w-0">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <h3 class="text-xl font-bold leading-tight text-brand-ink transition group-hover:text-brand-navy">
                    {{ $label }}
                </h3>

                <p class="insight-clamp-2 mt-2 text-sm leading-6 text-slate-600">
                    {{ $description }}
                </p>
            </div>

            <span class="shrink-0 rounded-full bg-[#EAF2FF] px-3 py-1 text-[10px] font-black uppercase tracking-[0.1em] text-brand-navy">
                {{ number_format($articleCount, 0, ',', '.') }} artikel
            </span>
        </div>

        <div class="mt-4">
            @forelse ($articles->take(2) as $article)
                <x-insight.category-article :article="$article" />
            @empty
                <div class="border-t border-slate-100 py-3">
                    <p class="text-sm font-medium leading-6 text-slate-500">
                        Artikel sedang dipersiapkan.
                    </p>

                    <a href="{{ $url }}" class="group/link mt-2 inline-flex items-center gap-2 text-sm font-bold text-brand-navy underline-offset-4 transition hover:underline">
                        Ikuti kanal
                        <svg class="h-4 w-4 transition group-hover/link:translate-x-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            @endforelse
        </div>

        <a href="{{ $url }}#insight-archive" class="group/link mt-4 inline-flex min-h-10 items-center gap-2 text-sm font-bold text-brand-navy underline-offset-4 transition hover:underline">
            Lihat semua
            <svg class="h-4 w-4 transition group-hover/link:translate-x-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
    </div>
</article>
