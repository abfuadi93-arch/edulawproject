@props(['topics' => collect()])

<section id="topik-editorial" class="home-surface-mist scroll-mt-20 py-7 sm:py-8 lg:py-9" aria-labelledby="home-topics-title">
    <div class="section-shell">
        <div class="flex items-end justify-between gap-5">
            <div>
                <p class="home-section-eyebrow text-[#b18332]">Jelajahi Topik</p>
                <h2 id="home-topics-title" class="home-section-title">Temukan Analisis Berdasarkan Tema</h2>
            </div>
            <a href="{{ route('insights.index') }}" class="home-section-link hidden sm:inline-flex">Arsip Editorial →</a>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (collect($topics) as $topic)
                <a href="{{ $topic['url'] }}" class="group flex min-h-40 flex-col rounded-xl border border-slate-200 bg-white p-4 transition hover:-translate-y-0.5 hover:border-brand-amber hover:shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-brand-teal">Topik · {{ number_format($topic['count'], 0, ',', '.') }} tulisan</p>
                    <h3 class="mt-2 text-lg font-black text-brand-navy transition group-hover:text-brand-teal">{{ $topic['name'] }}</h3>
                    <p class="mt-2 line-clamp-1 text-sm leading-6 text-slate-600">{{ $topic['description'] }}</p>
                    <p class="mt-auto pt-4 text-sm font-bold text-slate-500">{{ number_format($topic['count'], 0, ',', '.') }} tulisan →</p>
                </a>
            @endforeach
        </div>
    </div>
</section>
