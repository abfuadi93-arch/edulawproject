@props([
    'eyebrow' => 'Kolaborasi',
    'title' => 'Bangun ruang literasi hukum bersama Edulaw Project.',
    'body' => 'Edulaw Project terbuka untuk kerja sama program edukasi hukum, diskusi publik, riset, publikasi, pelatihan, dan pengembangan ekosistem literasi hukum.',
    'primaryUrl' => null,
    'primaryLabel' => 'Ajukan Kerja Sama',
    'secondaryUrl' => null,
    'secondaryLabel' => null,
    'backgroundImage' => 'https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=1600&q=85',
    'backgroundAlt' => '',
    'titleClass' => '',
])

@php
    $primaryUrl = $primaryUrl ?? route('collaboration.index');
    $hasSecondary = filled($secondaryUrl) && filled($secondaryLabel);
@endphp

<section {{ $attributes->merge(['class' => 'relative isolate overflow-hidden bg-[#07111F] text-white']) }}>
    @if ($backgroundImage)
        <div class="absolute inset-0 -z-10">
            <img
                src="{{ $backgroundImage }}"
                alt="{{ $backgroundAlt }}"
                class="h-full w-full object-cover opacity-38"
            >
            <div class="absolute inset-0 bg-linear-to-r from-[#050B14]/92 via-[#07111F]/78 to-[#0B1628]/48"></div>
            <div class="absolute inset-y-0 right-0 w-full bg-linear-to-l from-[#B87316]/28 via-[#1F3C69]/18 to-transparent sm:w-[68%]"></div>
        </div>
    @endif

    <div class="section-shell grid items-center gap-6 py-10 sm:py-12 lg:grid-cols-[1fr_auto] lg:py-14">
        <div>
            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-[#C9891F]">
                {{ $eyebrow }}
            </p>

            <h2 class="mt-2 max-w-3xl text-2xl font-black leading-tight tracking-normal text-white sm:text-3xl lg:text-[2.35rem] {{ $titleClass }}">
                {{ $title }}
            </h2>

            <p class="mt-3 max-w-3xl text-sm leading-7 text-white/75 sm:text-base">
                {{ $body }}
            </p>
        </div>

        <div class="flex flex-col gap-3 lg:min-w-64 lg:items-stretch lg:justify-end">
            <a
                href="{{ $primaryUrl }}"
                class="group inline-flex min-h-12 items-center justify-center gap-2 rounded-full bg-[#B87316] px-6 py-3 text-sm font-black text-white shadow-lg shadow-black/25 transition duration-300 hover:-translate-y-0.5 hover:bg-[#D99A25] hover:text-white"
            >
                {{ $primaryLabel }}
                <svg class="h-4 w-4 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>

            @if ($hasSecondary)
                <a
                    href="{{ $secondaryUrl }}"
                    class="group inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-white/25 bg-black/15 px-6 py-3 text-sm font-black text-white shadow-sm backdrop-blur transition duration-300 hover:-translate-y-0.5 hover:border-[#C9891F] hover:bg-white/10"
                >
                    {{ $secondaryLabel }}
                    <svg class="h-4 w-4 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            @endif
        </div>
    </div>
</section>
