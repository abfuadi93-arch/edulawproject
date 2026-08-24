@props(['insights' => collect()])

@php
    $items = collect($insights)->filter()->unique('id')->take(4)->values();
@endphp

<section id="edulaw-insight" class="home-surface-paper scroll-mt-20 py-8 sm:py-9 lg:py-10" aria-labelledby="home-insights-title">
    <div class="section-shell">
        <div class="flex items-end justify-between gap-5">
            <div>
                <p class="home-section-eyebrow text-[#e57b66]">Edulaw Insight Terbaru</p>
                <h2 id="home-insights-title" class="home-section-title">Analisis Hukum yang Relevan</h2>
            </div>
            <a href="{{ route('insights.index') }}" class="home-section-link">Semua Editorial →</a>
        </div>

        @if ($items->isNotEmpty())
            <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($items as $index => $item)
                    <article data-home-insight @if ($index === 0) data-home-insight-latest @endif class="group overflow-hidden rounded-xl border border-[#e7ebf0] bg-white transition hover:border-slate-300">
                        <a href="{{ route('insights.show', $item->slug) }}" class="block h-full focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                            <div class="relative aspect-[16/9] overflow-hidden bg-[linear-gradient(135deg,#173b63,#4a8796)]">
                                @if ($item->cover_image_url)
                                    <x-responsive-image :src="$item->cover_image_url" alt="Sampul {{ $item->title }}" :widths="[320, 480, 640]" sizes="(min-width: 1024px) 290px, (min-width: 640px) 50vw, 100vw" width="520" height="292" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.025]" onerror="this.remove()" />
                                @endif
                            </div>
                            <div class="p-4">
                                <p class="home-card-kicker">{{ $item->display_category }}</p>
                                <h3 class="home-card-title line-clamp-3 min-h-[4.3rem]">{{ $item->title }}</h3>
                                <div class="home-card-meta mt-4 flex flex-wrap gap-x-2 gap-y-1">
                                    @if ($item->published_at)<span>{{ $item->published_at->translatedFormat('d M Y') }}</span>@endif
                                    @if ($item->published_at && $item->reading_time)<span>·</span>@endif
                                    @if ($item->reading_time)<span>{{ $item->reading_time }} menit</span>@endif
                                </div>
                                <p class="mt-2 line-clamp-1 text-xs font-bold text-slate-500">Oleh: {{ $item->display_author }}</p>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        @else
            <div class="home-empty-state mt-7 py-4">
                <p class="text-sm leading-6 text-slate-600">Insight terbaru sedang disiapkan.</p>
                <a href="{{ route('insights.index') }}" class="home-section-link mt-3">Lihat Semua Insight →</a>
            </div>
        @endif
    </div>
</section>
