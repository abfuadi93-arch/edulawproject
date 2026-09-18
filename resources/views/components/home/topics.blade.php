@props(['topics' => collect()])

<section id="topik-editorial" class="scroll-mt-20 bg-slate-50 py-6 sm:py-8" aria-labelledby="home-topics-title">
    <div class="section-shell">
        <div class="flex items-end justify-between gap-5">
            <div>
                <p class="home-section-eyebrow">Jelajahi Topik</p>
                <h2 id="home-topics-title" class="home-section-title">Temukan Analisis Berdasarkan Tema</h2>
            </div>
            <a href="{{ route('insights.index') }}" class="home-section-link hidden sm:inline-flex">Lihat Semua Topik →</a>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (collect($topics) as $index => $topic)
                <a href="{{ $topic['url'] }}" class="group flex gap-3.5 rounded-[10px] border border-slate-200 bg-white p-4 transition duration-200 hover:-translate-y-0.5 hover:border-brand-navy hover:shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                    <span @class(['grid size-10 shrink-0 place-items-center rounded-full', 'bg-blue-50 text-brand-navy' => $index < 3, 'bg-amber-50 text-amber-600' => $index === 3]) aria-hidden="true">
                        @switch($index)
                            @case(0)
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 21h18M5 18h14M6 9h12M12 3l9 5H3zM7 9v9M11 9v9M15 9v9"/></svg>
                                @break
                            @case(1)
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3v18M5 7h14M7 7l-4 7h8L7 7ZM17 7l-4 7h8l-4-7Z"/></svg>
                                @break
                            @case(2)
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6 3h9l3 3v15H6zM14 3v4h4M9 11h6M9 15h6"/></svg>
                                @break
                            @default
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 18h6M10 22h4M8.5 14.5A7 7 0 1 1 16 14c-1 1-1.5 1.5-1.5 3h-5c0-1.5-.5-2-1-2.5Z"/></svg>
                        @endswitch
                    </span>
                    <div class="flex min-w-0 flex-1 flex-col">
                        <h3 class="text-[15px] font-bold leading-snug text-brand-navy transition group-hover:text-brand-teal">{{ $topic['name'] }}</h3>
                        <p class="mt-2 text-sm font-bold text-brand-navy">{{ number_format($topic['count'], 0, ',', '.') }} tulisan</p>
                    </div>
                </a>
            @endforeach
        </div>

        <a href="{{ route('insights.index') }}" class="home-section-link mt-6 sm:hidden">Lihat Semua Topik →</a>
    </div>
</section>
