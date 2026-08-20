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

<section {{ $attributes->merge(['class' => 'bg-brand-paper px-5 py-7 sm:px-6 lg:px-8 lg:py-10']) }}>
    <div class="relative isolate mx-auto max-w-7xl overflow-hidden rounded-[1.75rem] border border-brand-sky/20 bg-brand-navy text-white shadow-xl shadow-brand-navy/15 sm:rounded-[2rem]">
        @if ($backgroundImage)
            <div class="absolute inset-0 -z-10">
                <img
                    src="{{ $backgroundImage }}"
                    alt="{{ $backgroundAlt }}"
                    class="h-full w-full object-cover opacity-38"
                >
                <div class="absolute inset-0 bg-linear-to-r from-brand-navy via-brand-navy/88 to-brand-navy/60"></div>
                <div class="absolute inset-y-0 right-0 w-full bg-linear-to-l from-brand-amber/22 via-brand-teal/12 to-transparent sm:w-[68%]"></div>
            </div>
        @endif

        <svg class="pointer-events-none absolute -right-40 top-0 -z-5 h-full w-[48rem] text-brand-teal/12" viewBox="0 0 1000 620" fill="none" aria-hidden="true">
            <path d="M500-80 930 620H70L500-80Z" stroke="currentColor" stroke-width="2"/>
            <path d="m500 165 250 405H250l250-405Z" stroke="currentColor" stroke-width="2"/>
        </svg>

        <div class="grid items-center gap-6 px-6 py-9 sm:px-8 sm:py-11 lg:grid-cols-[1fr_auto] lg:px-12 lg:py-12">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-brand-amber">
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
                    class="group inline-flex min-h-12 items-center justify-center gap-2 rounded-full bg-brand-amber px-6 py-3 text-sm font-black text-brand-ink shadow-lg shadow-brand-ink/25 transition duration-300 hover:-translate-y-0.5 hover:bg-[#e7a72d] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber"
                >
                    {{ $primaryLabel }}
                    <svg class="h-4 w-4 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>

                @if ($hasSecondary)
                    <a
                        href="{{ $secondaryUrl }}"
                        class="group inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-brand-sky/35 bg-brand-ink/15 px-6 py-3 text-sm font-black text-white shadow-sm backdrop-blur transition duration-300 hover:-translate-y-0.5 hover:border-brand-amber hover:bg-white/10 hover:text-brand-amber focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber"
                    >
                        {{ $secondaryLabel }}
                        <svg class="h-4 w-4 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>
