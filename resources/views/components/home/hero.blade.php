@props(['hero' => null, 'values' => collect()])

@php
    $heroImage = $hero?->image_url ?? asset('images/hero/hero-edulaw.jpg');
    $heroAlt = $hero?->image_alt ?? 'Ruang diskusi hukum Edulaw Project';
    $heroEyebrow = $hero?->eyebrow ?? 'Equal · Educative · Embrace';
    $heroDescription = $hero?->body ?? 'Edulaw Project menghubungkan pembelajaran hukum, analisis kebijakan, riset, dan kolaborasi publik agar pengetahuan dapat dipahami, digunakan, dan menghasilkan perubahan.';

    $fallbackValues = [
        ['title' => 'Belajar', 'description' => 'Program, diskusi, dan pelatihan hukum.', 'symbol' => '▤'],
        ['title' => 'Memahami', 'description' => 'Editorial, riset, dan publikasi kontekstual.', 'symbol' => '⌕'],
        ['title' => 'Berkembang', 'description' => 'Peluang, kolaborasi, dan jejaring publik.', 'symbol' => '◎'],
    ];

    $valueCards = ($values instanceof \Illuminate\Support\Collection && $values->isNotEmpty())
        ? $values->take(3)->values()->map(fn ($value, $index) => [
            'title' => $value->title,
            'description' => $value->body,
            'symbol' => ['▤', '⌕', '◎'][$index] ?? '◎',
        ])->all()
        : $fallbackValues;
@endphp

<section class="relative isolate overflow-hidden bg-[#082344] text-white lg:min-h-[calc(100svh-5rem)]" data-home-hero>
    <img
        src="{{ $heroImage }}"
        alt="{{ $heroAlt }}"
        width="1600"
        height="900"
        class="absolute inset-0 -z-20 size-full object-cover object-center lg:object-[65%_center]"
        fetchpriority="high"
        decoding="async"
    >
    <div class="absolute inset-0 -z-10 bg-[linear-gradient(90deg,rgba(5,25,51,.99)_0%,rgba(8,36,70,.95)_42%,rgba(8,34,65,.66)_72%,rgba(8,34,65,.42)_100%)]"></div>
    <div class="absolute inset-y-0 left-0 -z-10 w-3/4 bg-[radial-gradient(circle_at_15%_45%,rgba(42,84,128,.44),transparent_58%)]"></div>
    <div class="absolute inset-x-0 bottom-0 -z-10 h-40 bg-linear-to-t from-[#061b36] to-transparent"></div>

    <div class="section-shell pb-8 pt-16 sm:pt-20 lg:flex lg:min-h-[calc(100svh-5rem)] lg:flex-col lg:justify-center lg:py-12">
        <div class="max-w-3xl">
            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#f0c55e]">{{ $heroEyebrow }}</p>
            <h1 class="mt-5 font-display text-4xl font-black leading-[1.08] tracking-tight text-white sm:text-5xl lg:text-[60px]">
                Hukum yang Mencerahkan,<br>
                <span class="text-[#f5c451]">Pengetahuan yang Berdampak.</span>
            </h1>
            <p class="mt-6 max-w-2xl text-base leading-7 text-slate-200">{{ $heroDescription }}</p>

            <div class="mt-7 flex flex-wrap gap-3">
                <a href="{{ route('insights.index') }}" class="inline-flex min-h-11 items-center gap-3 rounded-lg bg-white px-5 py-3 text-sm font-extrabold text-[#102b50] shadow-lg shadow-black/10 transition hover:bg-slate-100">
                    Jelajahi Editorial <span aria-hidden="true">→</span>
                </a>
                <a href="{{ route('programs.index') }}" class="inline-flex min-h-11 items-center gap-3 rounded-lg border border-white/25 bg-white/5 px-5 py-3 text-sm font-extrabold text-white backdrop-blur transition hover:bg-white/15">
                    Lihat Program <span aria-hidden="true">→</span>
                </a>
                <a href="{{ route('collaboration.index') }}" class="inline-flex min-h-11 items-center gap-3 rounded-lg border border-[#f5c451] bg-[#f5c451]/10 px-5 py-3 text-sm font-extrabold text-[#ffe38d] backdrop-blur transition hover:bg-[#f5c451] hover:text-[#102b50]">
                    Ajukan Kolaborasi <span aria-hidden="true">→</span>
                </a>
            </div>
        </div>

        <div class="mt-12 grid max-w-4xl border-y border-white/15 sm:grid-cols-3 lg:mt-16">
            @foreach ($valueCards as $value)
                <article class="flex gap-4 border-white/15 px-1 py-5 sm:border-r sm:px-5 sm:last:border-r-0">
                    <span class="grid size-10 shrink-0 place-items-center rounded-lg border border-white/20 bg-white/5 text-lg font-black text-[#f5c451]" aria-hidden="true">{{ $value['symbol'] }}</span>
                    <div>
                        <h2 class="text-base font-extrabold text-white">{{ $value['title'] }}</h2>
                        <p class="mt-1 text-xs leading-5 text-slate-300">{{ $value['description'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
