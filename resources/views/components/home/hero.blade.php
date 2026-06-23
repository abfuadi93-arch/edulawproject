@props(['hero' => null, 'values' => collect()])

@php
    $heroMeta = (array) ($hero?->meta ?? []);
    $heroImage = $hero?->image_url ?? asset('images/hero/hero-edulaw.jpg');
    $heroAlt = $hero?->image_alt ?? 'Kegiatan literasi hukum Edulaw Project';
    $heroEyebrow = $hero?->eyebrow ?? 'Equal · Educative · Embrace';
    $heroTitle = $hero?->title ?? 'Membangun Literasi Hukum yang Mudah Diakses, Relevan, dan Berdampak';
    $heroDescription = $hero?->body ?? 'Edulaw Project menghadirkan edukasi, riset, program, multimedia, dan kanal pengembangan hukum dalam satu platform digital yang terintegrasi.';
    $primaryUrl = $hero?->resolved_url ?? url('/insight');
    $primaryLabel = $hero?->url_label ?? 'Jelajahi Insight';
    $secondaryUrl = \App\Models\SiteSetting::resolveUrl($heroMeta['secondary_url'] ?? null, url('/program'));
    $secondaryLabel = $heroMeta['secondary_label'] ?? 'Lihat Program';

    $fallbackValues = [
        [
            'title' => 'Belajar',
            'description' => 'Kuasai konsep hukum secara nyata.',
            'icon' => 'book-open',
            'accent' => 'bg-white/15 text-white',
        ],
        [
            'title' => 'Memahami',
            'description' => 'Pahami hukum untuk kehidupan publik.',
            'icon' => 'scale',
            'accent' => 'bg-brand-teal/20 text-brand-teal',
        ],
        [
            'title' => 'Berkembang',
            'description' => 'Kembangkan peran, ciptakan dampak.',
            'icon' => 'chart',
            'accent' => 'bg-brand-coral/20 text-brand-coral',
        ],
    ];

    $valueCards = ($values instanceof \Illuminate\Support\Collection && $values->isNotEmpty())
        ? $values->map(fn ($value) => [
            'title' => $value->title,
            'description' => $value->body,
            'icon' => $value->icon ?: 'chart',
            'accent' => $value->accent ?: 'bg-white/15 text-white',
        ])->all()
        : $fallbackValues;
@endphp

<section class="relative isolate overflow-hidden bg-brand-navy text-white shadow-xl shadow-brand-navy/15">
    {{-- Background image --}}
    <img
        src="{{ $heroImage }}"
        alt="{{ $heroAlt }}"
        class="absolute inset-0 z-0 h-full w-full object-cover object-center lg:object-[61%_center]"
    >

    {{-- Overlay --}}
    <div class="absolute inset-0 z-0 bg-linear-to-r from-[#04142d]/95 via-[#061f43]/82 via-49% to-[#061f43]/25"></div>
    <div class="absolute inset-0 z-0 bg-linear-to-t from-[#04142d]/75 via-[#04142d]/15 to-[#04142d]/20"></div>
    <div class="absolute inset-y-0 left-0 z-0 hidden w-[48%] bg-[#04142d]/25 lg:block"></div>

    {{-- Decorative soft glows --}}
    <div class="pointer-events-none absolute -left-24 top-24 z-0 h-72 w-72 rounded-full bg-brand-teal/15 blur-3xl"></div>
    <div class="pointer-events-none absolute -right-24 bottom-16 z-0 h-80 w-80 rounded-full bg-brand-amber/15 blur-3xl"></div>

     <div class="relative z-10 mx-auto flex min-h-152 max-w-7xl flex-col justify-end px-5 pb-8 pt-28 sm:px-6 lg:min-h-164 lg:px-8 lg:pb-8 lg:pt-32">
        <div class="max-w-188">
            <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-[11px] font-extrabold uppercase tracking-[0.18em] text-white/95 backdrop-blur-md">
                <span class="h-1.5 w-1.5 rounded-full bg-brand-amber"></span>
                {{ $heroEyebrow }}
            </div>

            <h1 class="mt-5 max-w-188 font-display text-[2.25rem] font-extrabold leading-[1.06] tracking-[-0.035em] text-white sm:text-5xl lg:text-[3.15rem] xl:text-[3.45rem]">
                {{ $heroTitle }}
            </h1>

            <p class="mt-5 max-w-156 text-sm leading-7 text-white/86 sm:text-base sm:leading-8">
                {{ $heroDescription }}
            </p>

            <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                <a
                    href="{{ $primaryUrl }}"
                    class="inline-flex min-h-11 items-center justify-center gap-3 rounded-xl bg-brand-amber px-5 py-2.5 text-sm font-extrabold text-brand-black shadow-lg shadow-brand-black/25 transition duration-300 hover:-translate-y-0.5 hover:bg-white hover:text-brand-black sm:px-6"
                >
                    {{ $primaryLabel }}
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>

                <a
                    href="{{ $secondaryUrl }}"
                    class="inline-flex min-h-11 items-center justify-center gap-3 rounded-xl border border-white/45 bg-white/5 px-5 py-2.5 text-sm font-extrabold text-white shadow-sm backdrop-blur-md transition duration-300 hover:-translate-y-0.5 hover:border-brand-amber hover:bg-white/15 sm:px-6"
                >
                    {{ $secondaryLabel }}
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        </div>

{{-- Value cards --}}
<div class="mt-10 grid w-full gap-4 sm:grid-cols-3 lg:mt-11">
    @foreach ($valueCards as $value)
        <div
            class="group flex min-h-34 w-full items-center gap-5 rounded-3xl border border-white/18 bg-white/10 px-5 py-6 shadow-2xl shadow-black/20 backdrop-blur-md transition-all duration-300 hover:-translate-y-1.5 hover:border-white/40 hover:bg-white/15 sm:px-6 lg:px-7"
        >
            <div
                class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl ring-1 ring-white/10 sm:h-16 sm:w-16 {{ $value['accent'] }}"
            >
                @if ($value['icon'] === 'book-open')
                    <svg class="h-7 w-7 sm:h-8 sm:w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M4 5.5A2.5 2.5 0 0 1 6.5 8H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                @elseif ($value['icon'] === 'scale')
                    <svg class="h-7 w-7 sm:h-8 sm:w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 3v18M5 6h14M7 6l-3 7h6L7 6Zm10 0-3 7h6l-3-7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                @else
                    <svg class="h-7 w-7 sm:h-8 sm:w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 19V5m0 14h16M8 16v-4m5 4V8m5 8v-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                @endif
            </div>

            <div class="min-w-0 flex-1">
                <h3 class="font-display text-base font-extrabold tracking-tight text-white sm:text-lg">
                    {{ $value['title'] }}
                </h3>

                <p class="mt-2 whitespace-nowrap text-sm leading-6 text-white/78">
                    {{ $value['description'] }}
                </p>
            </div>
        </div>
    @endforeach
</div>

    </div>
</section>
