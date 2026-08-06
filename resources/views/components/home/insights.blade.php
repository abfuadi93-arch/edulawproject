@props(['featuredInsight' => null, 'insights' => collect()])

@php
    $items = collect([$featuredInsight])->concat(collect($insights))->filter()->unique('id')->take(4)->values();
@endphp

<section id="edulaw-insight" class="scroll-mt-20 bg-white py-9 lg:py-12" aria-labelledby="home-insights-title">
    <div class="section-shell">
        <div class="flex items-end justify-between gap-5">
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-[0.18em] text-[#e57b66]">Edulaw Insight Terbaru</p>
                <h2 id="home-insights-title" class="mt-2 font-display text-2xl font-extrabold tracking-tight text-[#1f3c69] sm:text-3xl">Analisis Hukum yang Relevan</h2>
            </div>
            <a href="{{ route('insights.index') }}" class="text-xs font-extrabold text-[#1f3c69]">Semua Editorial →</a>
        </div>

        @if ($items->isNotEmpty())
            <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($items as $index => $item)
                    <article data-home-insight @if ($index === 0) data-home-insight-featured @else data-home-insight-compact @endif class="group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_14px_34px_-28px_rgba(15,23,42,.7)] transition hover:-translate-y-0.5 hover:shadow-lg">
                        <a href="{{ route('insights.show', $item->slug) }}" class="block h-full focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                            <div class="relative aspect-[16/9] overflow-hidden bg-[linear-gradient(135deg,#173b63,#4a8796)]">
                                @if ($item->cover_image_url)
                                    <img src="{{ $item->cover_image_url }}" alt="Sampul {{ $item->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" decoding="async" onerror="this.remove()">
                                @endif
                                <div class="absolute inset-0 bg-[#142f57]/15"></div>
                            </div>
                            <div class="p-4">
                                <p class="text-[10px] font-extrabold uppercase tracking-wider text-[#35a48d]">{{ $item->display_category }}</p>
                                <h3 class="mt-2 line-clamp-3 min-h-14 text-base font-extrabold leading-snug text-[#142f57]">{{ $item->title }}</h3>
                                <div class="mt-4 flex flex-wrap gap-x-2 gap-y-1 text-xs text-slate-400">
                                    @if ($item->published_at)<span>{{ $item->published_at->translatedFormat('d M Y') }}</span>@endif
                                    @if ($item->published_at && $item->reading_time)<span>·</span>@endif
                                    @if ($item->reading_time)<span>{{ $item->reading_time }} menit</span>@endif
                                </div>
                                <p class="mt-2 line-clamp-1 text-[11px] font-bold text-slate-500">Oleh: {{ $item->display_author }}</p>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        @else
            <div class="home-empty-state mt-7 py-4">
                <p class="text-sm leading-6 text-slate-600">Insight terbaru sedang disiapkan.</p>
                <a href="{{ route('insights.index') }}" class="btn-dark mt-4 min-h-11">Lihat Semua Insight</a>
            </div>
        @endif
    </div>
</section>
