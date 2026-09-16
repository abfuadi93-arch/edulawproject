@props(['hero' => null, 'values' => collect()])

@php
    $heroImage = $hero?->image_url ?? asset('images/hero/hero-edulaw.jpg');
    $heroAlt = $hero?->image_alt ?? 'Ruang diskusi hukum Edulaw Project';
    $heroEyebrow = $hero?->eyebrow ?? 'Equal · Educative · Embrace';
    $heroTitle = trim((string) ($hero?->title ?? 'Hukum yang Mencerahkan, Pengetahuan yang Berdampak.'));
    $heroDescription = $hero?->body ?? 'Edulaw Project menghubungkan pembelajaran hukum, analisis kebijakan, riset, dan kolaborasi publik agar pengetahuan dapat dipahami, digunakan, dan menghasilkan perubahan.';
    $heroPrimaryUrl = route('insights.index');
    $heroPrimaryLabel = 'Jelajahi Editorial';
    $heroSecondaryUrl = route('publications.index');
    $heroSecondaryLabel = 'Lihat Riset & Publikasi';

    $heroTitleParts = preg_split('/,\s*/', $heroTitle, 2) ?: [$heroTitle];
    $heroTitleLead = trim($heroTitleParts[0]).(count($heroTitleParts) > 1 ? ',' : '');
    $heroTitleAccent = trim($heroTitleParts[1] ?? '');

@endphp

<section class="relative isolate min-h-[600px] overflow-hidden bg-[#082344] py-3 text-white sm:min-h-[640px]" data-home-hero>
    <x-responsive-image
        :src="$heroImage"
        :alt="$heroAlt"
        :widths="[768, 960, 1280, 1600]"
        sizes="100vw"
        width="1600"
        height="900"
        class="absolute inset-0 -z-20 size-full object-cover object-center lg:object-[65%_center]"
        fetchpriority="high"
        loading="eager"
    />
    <div class="absolute inset-0 -z-10 bg-[linear-gradient(90deg,rgba(5,25,51,.98)_0%,rgba(8,36,70,.94)_44%,rgba(8,34,65,.64)_72%,rgba(8,34,65,.42)_100%)]"></div>
    <div class="absolute inset-x-0 bottom-0 -z-10 h-40 bg-linear-to-t from-[#061b36] to-transparent"></div>

    <div class="section-shell flex min-h-[600px] items-center pb-24 pt-16 sm:min-h-[640px] sm:pb-28 lg:py-20 lg:pb-28">
        <div class="max-w-[720px] lg:w-[58%]">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#f0c55e]">{{ $heroEyebrow }}</p>
            <h1 class="mt-5 font-display text-[2.5rem] font-bold leading-[1.06] tracking-[-0.035em] text-white sm:text-5xl lg:text-[58px]">
                {{ $heroTitleLead }}
                @if ($heroTitleAccent)
                    <br><span class="text-[#f5c451]">{{ $heroTitleAccent }}</span>
                @endif
            </h1>
            <p class="mt-6 max-w-2xl text-base leading-[1.7] text-slate-200 sm:text-[17px]">{{ $heroDescription }}</p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                <a href="{{ $heroPrimaryUrl }}" class="inline-flex min-h-12 items-center justify-center gap-3 rounded-lg bg-[#f5c451] px-6 py-3 text-sm font-bold text-[#102b50] transition duration-200 hover:bg-[#ffd36a] focus-visible:outline-white">
                    {{ $heroPrimaryLabel }} <span aria-hidden="true">→</span>
                </a>
                <a href="{{ $heroSecondaryUrl }}" class="inline-flex min-h-12 items-center justify-center gap-3 rounded-lg border border-white/35 bg-[#082344]/30 px-6 py-3 text-sm font-bold text-white transition duration-200 hover:border-white/60 hover:bg-white/10 focus-visible:outline-white">
                    {{ $heroSecondaryLabel }} <span aria-hidden="true">→</span>
                </a>
            </div>
        </div>
    </div>
</section>
