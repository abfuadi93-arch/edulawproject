@props(['hero' => null, 'values' => collect()])

@php
    $heroImage = $hero?->image_url ?? asset('images/hero/hero-edulaw.jpg');
    $heroAlt = $hero?->image_alt ?? 'Ruang diskusi hukum Edulaw Project';
    $heroEyebrow = $hero?->eyebrow ?? 'Equal · Educative · Embrace';
    $heroTitle = trim((string) ($hero?->title ?? 'Hukum yang Mencerahkan, Pengetahuan yang Berdampak.'));
    $heroDescription = $hero?->body ?? 'Edulaw Project menghubungkan pembelajaran hukum, analisis kebijakan, riset, dan kolaborasi publik agar pengetahuan dapat dipahami, digunakan, dan menghasilkan perubahan.';
    $heroMeta = (array) ($hero?->meta ?? []);
    $heroPrimaryUrl = $hero?->resolved_url ?? route('insights.index');
    $heroPrimaryLabel = $hero?->url_label ?? 'Jelajahi Editorial';
    $heroSecondaryUrl = \App\Support\EdulawSite::resolveUrl($heroMeta['secondary_url'] ?? null, route('programs.index'));
    $heroSecondaryLabel = $heroMeta['secondary_label'] ?? 'Lihat Program';
    $heroCollaborationUrl = \App\Support\EdulawSite::resolveUrl($heroMeta['tertiary_url'] ?? null, route('collaboration.index'));
    $heroCollaborationLabel = $heroMeta['tertiary_label'] ?? 'Ajukan Kolaborasi';

    $heroTitleParts = preg_split('/,\s*/', $heroTitle, 2) ?: [$heroTitle];
    $heroTitleLead = trim($heroTitleParts[0]).(count($heroTitleParts) > 1 ? ',' : '');
    $heroTitleAccent = trim($heroTitleParts[1] ?? '');

    $fallbackValues = [
        ['title' => 'Belajar', 'description' => 'Program, diskusi, dan pelatihan hukum.', 'symbol' => '01'],
        ['title' => 'Memahami', 'description' => 'Editorial, riset, dan publikasi kontekstual.', 'symbol' => '02'],
        ['title' => 'Berkembang', 'description' => 'Peluang, kolaborasi, dan jejaring publik.', 'symbol' => '03'],
    ];

    $dynamicValues = collect($values)->filter()->take(3)->values();
    $valueCards = $dynamicValues->isNotEmpty()
        ? $dynamicValues->map(fn ($value, int $index): array => [
            'title' => $value->title,
            'description' => $value->body,
            'symbol' => str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
        ])
        : collect($fallbackValues);
@endphp

<section class="relative isolate overflow-hidden bg-[#082344] text-white" data-home-hero>
    <x-responsive-image
        :src="$heroImage"
        :alt="$heroAlt"
        :widths="[768, 960, 1280, 1600]"
        sizes="100vw"
        width="1600"
        height="900"
        class="absolute inset-0 -z-20 size-full object-cover object-center lg:object-[65%_center]"
        fetchpriority="high"
        :loading="null"
    />
    <div class="absolute inset-0 -z-10 bg-[linear-gradient(90deg,rgba(5,25,51,.98)_0%,rgba(8,36,70,.94)_45%,rgba(8,34,65,.68)_72%,rgba(8,34,65,.48)_100%)]"></div>
    <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_14%_48%,rgba(37,183,160,.18),transparent_44%),radial-gradient(circle_at_86%_12%,rgba(245,185,67,.10),transparent_30%)]"></div>
    <div class="absolute inset-x-0 bottom-0 -z-10 h-40 bg-linear-to-t from-[#061b36] to-transparent"></div>

    <div class="section-shell pb-8 pt-14 sm:pt-16 lg:pb-10 lg:pt-20">
        <div class="max-w-3xl">
            <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-[#f0c55e]">{{ $heroEyebrow }}</p>
            <h1 class="mt-5 font-display text-4xl font-black leading-[1.08] tracking-tight text-white sm:text-5xl lg:text-[60px]">
                {{ $heroTitleLead }}
                @if ($heroTitleAccent)
                    <br><span class="text-[#f5c451]">{{ $heroTitleAccent }}</span>
                @endif
            </h1>
            <p class="mt-5 max-w-2xl text-base leading-7 text-slate-200 sm:text-[17px]">{{ $heroDescription }}</p>

            <div class="mt-7 flex flex-wrap gap-3">
                <a href="{{ $heroPrimaryUrl }}" class="inline-flex min-h-12 items-center justify-center gap-3 rounded-xl bg-white px-5 py-3 text-sm font-extrabold text-[#102b50] transition hover:bg-[#f5c451]">
                    {{ $heroPrimaryLabel }} <span aria-hidden="true">→</span>
                </a>
                <a href="{{ $heroSecondaryUrl }}" class="inline-flex min-h-12 items-center justify-center gap-3 rounded-xl border border-white/35 bg-white/5 px-5 py-3 text-sm font-extrabold text-white backdrop-blur transition hover:bg-white/15">
                    {{ $heroSecondaryLabel }} <span aria-hidden="true">→</span>
                </a>
                <a href="{{ $heroCollaborationUrl }}" class="inline-flex min-h-12 items-center justify-center gap-3 rounded-xl border border-[#f5c451] bg-[#f5c451]/10 px-5 py-3 text-sm font-extrabold text-[#ffe38d] backdrop-blur transition hover:bg-[#f5c451] hover:text-[#102b50]">
                    {{ $heroCollaborationLabel }} <span aria-hidden="true">→</span>
                </a>
            </div>
        </div>

        <div class="mt-12 grid max-w-5xl border-y border-white/15 sm:grid-cols-3 lg:mt-14">
            @foreach ($valueCards as $value)
                <article data-home-pillar class="flex gap-4 py-5 sm:border-r sm:px-5 sm:last:border-r-0">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg border border-[#f5c451]/40 bg-[#f5c451]/10 text-[11px] font-black tracking-wider text-[#f5c451]" aria-hidden="true">{{ $value['symbol'] }}</span>
                    <div>
                        <h2 class="text-base font-extrabold text-white">{{ $value['title'] }}</h2>
                        <p class="mt-1 text-xs leading-5 text-slate-300 sm:text-[13px]">{{ $value['description'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
