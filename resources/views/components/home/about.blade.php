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
    $impactStats = collect($stats)->take(6)->values();
@endphp

<section id="tentang-edulaw" class="home-section home-surface-mist scroll-mt-20" aria-labelledby="home-about-title">
    <div class="section-shell grid gap-5 lg:grid-cols-[minmax(0,.95fr)_minmax(0,1.05fr)]">
        <article class="relative overflow-hidden rounded-xl border border-[#e7ebf0] bg-white p-6 sm:p-8 lg:p-9">
            @if ($brandMark)
                <x-responsive-image :src="$brandMark" alt="Identitas {{ $siteName }}" :widths="[64, 96]" sizes="56px" width="64" height="64" class="absolute right-6 top-6 hidden size-14 object-contain opacity-90 sm:block" />
            @endif
            <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#b77928]">Tentang Edulaw</p>
            <h2 id="home-about-title" class="mt-3 max-w-lg text-2xl font-extrabold leading-[1.18] tracking-[-0.02em] text-[#102f56] sm:text-3xl">Ruang belajar dan riset hukum untuk kepentingan publik.</h2>
            <p class="mt-4 max-w-xl text-sm leading-7 text-slate-600">{{ $siteName }} — {{ $siteDescription }}</p>
            @if ($siteValues->isNotEmpty())
                <div class="mt-5 flex flex-wrap gap-2" aria-label="Nilai Edulaw">
                    @foreach ($siteValues as $value)
                        <span class="rounded-full bg-[#fff0b8] px-3 py-1.5 text-[11px] font-extrabold uppercase tracking-[0.09em] text-[#875b12]">{{ $value }}</span>
                    @endforeach
                </div>
            @endif
            <a href="{{ route('about') }}" class="mt-6 inline-flex rounded-lg bg-[#173b68] px-4 py-3 text-xs font-extrabold text-white transition hover:bg-[#102f56]">Kenali Edulaw →</a>
        </article>

        <article class="overflow-hidden rounded-xl bg-[linear-gradient(145deg,#0c386b_0%,#155e68_100%)] text-white">
            <div class="px-6 pb-4 pt-6 sm:px-8 sm:pt-8">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#f5c451]">Dampak Edulaw</p>
                <h2 class="mt-2 text-xl font-extrabold text-white sm:text-2xl">Pengetahuan yang terus bertumbuh.</h2>
            </div>
            <dl class="grid grid-cols-2 border-l border-t border-white/10 sm:grid-cols-3" aria-label="Statistik Edulaw Project">
                @foreach ($impactStats as $stat)
                    <div class="flex min-h-24 flex-col justify-center border-b border-r border-white/10 p-5 sm:p-6" data-home-stat="{{ $stat['label'] }}">
                        <dd class="font-display text-3xl font-extrabold tracking-tight text-[#f5c451]">{{ number_format($stat['value'], 0, ',', '.') }}</dd>
                        <dt class="mt-2 text-[11px] font-extrabold uppercase tracking-[0.09em] text-slate-200">{{ $stat['label'] }}</dt>
                    </div>
                @endforeach
            </dl>
        </article>
    </div>
</section>
