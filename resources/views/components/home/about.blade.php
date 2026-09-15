@props(['stats' => collect()])

@php
    $settings = \App\Support\EdulawSite::settings();
    $siteName = $settings['site.name'] ?? 'Edulaw Project';
    $siteDescription = $settings['site.short_description'] ?? 'Platform literasi hukum untuk pembelajaran, riset, publikasi, dan kolaborasi publik.';
    $brandMark = \App\Support\EdulawSite::assetUrl($settings['site.logo'] ?? null, 'images/logo/edulaw-icon.png');
    $siteValues = collect(preg_split('/[.·]+/', (string) ($settings['site.tagline'] ?? 'Equal. Educative. Embrace.')))
        ->map(fn (string $value): string => trim($value))
        ->filter()
        ->take(3);
    $impactStats = collect($stats)->take(3)->values();
@endphp

<section id="tentang-edulaw" class="scroll-mt-20 bg-white py-6 sm:py-10" aria-labelledby="home-about-title">
    <div class="section-shell grid gap-8 lg:grid-cols-[minmax(0,1.12fr)_minmax(360px,.88fr)] lg:items-stretch">
        <article class="relative flex flex-col py-2 lg:py-6">
            @if ($brandMark)
                <x-responsive-image :src="$brandMark" alt="Identitas {{ $siteName }}" :widths="[64, 96]" sizes="56px" width="64" height="64" class="absolute right-0 top-1 hidden size-14 object-contain opacity-90 sm:block lg:top-5" />
            @endif
            <p class="home-section-eyebrow">Tentang Edulaw</p>
            <h2 id="home-about-title" class="mt-3 max-w-lg text-2xl font-extrabold leading-[1.18] tracking-[-0.02em] text-[#102f56] sm:pr-20 sm:text-3xl">Ruang belajar dan riset hukum untuk kepentingan publik.</h2>
            <p class="mt-4 max-w-2xl text-[15px] leading-7 text-slate-600">{{ $siteName }} — {{ $siteDescription }}</p>
            <div class="mt-auto flex flex-col items-start gap-4 pt-6 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
                @if ($siteValues->isNotEmpty())
                    <div class="flex flex-wrap gap-2" aria-label="Nilai Edulaw">
                        @foreach ($siteValues as $value)
                            <span class="rounded-full bg-[#fff0b8] px-3 py-1.5 text-xs font-extrabold uppercase tracking-[0.09em] text-[#875b12]">{{ $value }}</span>
                        @endforeach
                    </div>
                @endif
                <a href="{{ route('about') }}" class="inline-flex shrink-0 rounded-lg bg-[#173b68] px-4 py-3 text-xs font-extrabold text-white transition hover:bg-[#102f56] sm:ml-auto">Kenali Edulaw →</a>
            </div>
        </article>

        <article class="flex overflow-hidden rounded-xl bg-[linear-gradient(145deg,#102f56_0%,#173b68_100%)] text-white">
            <div class="flex w-full flex-col px-6 py-7 sm:px-8 sm:py-8">
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#f5c451]">Dampak Edulaw</p>
                <h3 class="mt-2 max-w-sm text-white">Pengetahuan yang terus bertumbuh.</h3>
                <p class="mt-3 max-w-md text-sm leading-6 text-slate-200">Kerja editorial, riset, dan kolaborasi yang terhubung dalam satu ekosistem pembelajaran hukum.</p>
                <dl class="mt-auto grid grid-cols-3 gap-4 border-t border-white/15 pt-6" aria-label="Statistik Edulaw Project">
                    @foreach ($impactStats as $stat)
                        <div class="min-w-0" data-home-stat="{{ $stat['label'] }}">
                            <dd class="font-display text-2xl font-extrabold tracking-tight text-[#f5c451] sm:text-3xl">{{ number_format($stat['value'], 0, ',', '.') }}</dd>
                            <dt class="mt-1.5 text-xs font-bold uppercase leading-4 tracking-[0.06em] text-slate-200">{{ $stat['label'] }}</dt>
                        </div>
                    @endforeach
                </dl>
            </div>
        </article>
    </div>
</section>
