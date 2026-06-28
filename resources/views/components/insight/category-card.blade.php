@props([
    'channel' => [],
])

@php
    $articles = collect($channel['articles'] ?? []);
    $label = $channel['label'] ?? 'Insight';
    $description = $channel['description'] ?? ($channel['category']?->description ?? 'Kumpulan artikel dan analisis dari kanal Insight Edulaw.');
    $articleCount = (int) ($channel['article_count'] ?? $articles->count());
    $url = $channel['url'] ?? route('insights.index');
    $icon = $channel['icon'] ?? 'spark';
    $visualClass = match ($icon) {
        'book' => 'from-[#146B5F] via-[#1E7A6B] to-[#0E4F47]',
        'document' => 'from-[#2B1E5F] via-[#49347D] to-[#1C153D]',
        'spark' => 'from-[#6A3D05] via-[#8A5A13] to-[#372104]',
        'people' => 'from-[#253B6E] via-[#355C96] to-[#10244A]',
        'tech' => 'from-[#0F3A4A] via-[#1A6B7C] to-[#0A2531]',
        'briefcase' => 'from-[#5D3B10] via-[#9A6B1F] to-[#2E1B05]',
        'globe' => 'from-[#172554] via-[#1D4E89] to-[#0F172A]',
        default => 'from-[#061A3D] via-brand-navy to-[#1B5A7A]',
    };
@endphp

<article class="group flex h-full min-h-[28.5rem] w-[84vw] max-w-[21rem] shrink-0 snap-start flex-col overflow-hidden rounded-[20px] border border-[#EAEAEA] bg-white shadow-[0_10px_30px_rgba(15,23,42,0.05)] transition duration-[250ms] hover:-translate-y-1 hover:border-brand-navy hover:shadow-[0_18px_45px_rgba(15,23,42,0.10)] sm:w-auto sm:max-w-none">
    <header class="p-6 pb-5">
        <div class="grid h-16 w-16 place-items-center rounded-2xl bg-linear-to-br {{ $visualClass }} text-white shadow-lg shadow-slate-900/10 transition duration-[250ms] group-hover:scale-[1.04]">
            @switch($icon)
                @case('book')
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M6 4h9a3 3 0 0 1 3 3v13H8a3 3 0 0 0-3-3V5a1 1 0 0 1 1-1Zm2 0v13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    @break

                @case('column')
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 20h14M7 9v8m5-8v8m5-8v8M4 7l8-4 8 4H4Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    @break

                @case('document')
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M7 3h7l4 4v14H7V3Zm7 0v5h4M9.5 12h5M9.5 16h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    @break

                @case('people')
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M16 11a4 4 0 1 0-8 0m8 0a4 4 0 0 1-8 0m8 0c1.8.5 3 1.8 3 3.6V17H5v-2.4c0-1.8 1.2-3.1 3-3.6m10-2.8a2.8 2.8 0 0 1 0 5.6M6 8.2a2.8 2.8 0 0 0 0 5.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    @break

                @case('tech')
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M8 9h8M8 15h8M9 3v3m6-3v3M9 18v3m6-3v3M3 9h3m-3 6h3m12-6h3m-3 6h3M7 6h10a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    @break

                @case('briefcase')
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2m-9 5h12M5 7h14a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    @break

                @case('globe')
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M3 12h18M12 3a15.5 15.5 0 0 1 0 18m0-18a15.5 15.5 0 0 0 0 18m0-18a9 9 0 1 1 0 18 9 9 0 0 1 0-18Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    @break

                @default
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="m12 3 1.7 5.2H19l-4.3 3.1 1.6 5.2L12 13.3l-4.3 3.2 1.6-5.2L5 8.2h5.3L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    </svg>
            @endswitch
        </div>

        <div class="mt-6 min-w-0">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-lg font-black leading-tight text-brand-ink transition duration-[250ms] group-hover:text-brand-navy">
                    {{ $label }}
                </h3>

                <span class="shrink-0 rounded-full bg-[#EAF2FF] px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.1em] text-brand-navy">
                    {{ number_format($articleCount, 0, ',', '.') }} artikel
                </span>
            </div>

            <p class="insight-clamp-2 mt-3 text-sm leading-6 text-slate-600">
                {{ $description }}
            </p>
        </div>
    </header>

    <div class="mx-5 h-px bg-slate-100"></div>

    <div class="flex flex-1 flex-col p-6 pt-5">
        <div class="space-y-3">
            @forelse ($articles->take(3) as $article)
                <x-insight.category-article :article="$article" />
            @empty
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-4">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-white text-brand-navy shadow-sm ring-1 ring-slate-200">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M7 4h10v16H7V4Zm3 4h4m-4 4h4m-4 4h2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>

                        <p class="text-sm font-semibold leading-6 text-slate-500">
                            Articles are currently being prepared.
                        </p>
                    </div>

                    <a href="{{ $url }}" class="mt-4 inline-flex min-h-10 items-center justify-center rounded-full border border-brand-navy/20 bg-white px-4 py-2 text-xs font-black text-brand-navy transition hover:border-brand-navy hover:bg-brand-navy hover:text-white">
                        Lihat Kanal
                    </a>
                </div>
            @endforelse
        </div>

        <a href="{{ $url }}#insight-archive" class="mt-auto inline-flex min-h-11 items-center justify-between gap-2 border-t border-slate-100 pt-5 text-sm font-black text-brand-navy transition hover:text-brand-ink">
            <span class="truncate">
                Lihat semua
            </span>
            <span class="shrink-0 transition group-hover:translate-x-1">→</span>
        </a>
    </div>
</article>
