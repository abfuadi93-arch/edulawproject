@props(['articles', 'categoryName', 'readingTime'])

@if ($articles->isNotEmpty())
    <section class="py-10 sm:py-12 lg:py-14">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-2 border-b border-slate-300 pb-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-brand-coral">Pilihan pembaca</p>
                    <h2 class="mt-2 font-display text-3xl font-bold text-brand-ink sm:text-4xl">Paling Banyak Dibaca</h2>
                </div>
                <p class="text-xs font-semibold text-slate-500 sm:pb-1">30 hari terakhir</p>
            </div>

            <ol class="divide-y divide-slate-200">
                @foreach ($articles->take(5) as $index => $article)
                    @php($visitCount = $article->getAttribute('visit_count'))
                    <li data-most-read-item>
                        <a href="{{ route('insights.show', $article->slug) }}" class="group grid grid-cols-[44px_minmax(0,1fr)] gap-x-4 gap-y-2 py-5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber sm:grid-cols-[64px_minmax(0,1fr)_minmax(160px,auto)] sm:items-center sm:gap-x-6 lg:py-6">
                            <span class="font-display text-4xl font-normal tabular-nums leading-none text-brand-navy/25 sm:text-[2.75rem]">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="min-w-0">
                                <span class="block text-[10px] font-bold uppercase tracking-[0.12em] text-brand-coral">{{ $categoryName($article) }}</span>
                                <span class="mt-2 block line-clamp-2 max-w-4xl text-lg font-bold leading-snug text-brand-ink transition group-hover:text-brand-navy sm:text-xl">{{ $article->title }}</span>
                            </span>
                            <span class="col-start-2 mt-1 text-xs font-semibold text-slate-500 sm:col-start-auto sm:mt-0 sm:text-right">
                                {{ $readingTime($article) }}
                                @if (is_numeric($visitCount) && (int) $visitCount > 0)
                                    · {{ number_format((int) $visitCount, 0, ',', '.') }} kali dibaca
                                @endif
                            </span>
                        </a>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>
@endif
