@props(['stats' => collect()])

@php
    $settings = \App\Support\EdulawSite::settings();
    $siteName = $settings['site.name'] ?? 'Edulaw Project';
    $logo = \App\Support\EdulawSite::assetUrl(
        $settings['site.footer_logo'] ?? null,
        'images/logo/edulaw-logo.png',
    );
    $hasAboutPage = \Illuminate\Support\Facades\Route::has('about');
@endphp

<section id="tentang-edulaw" class="home-section scroll-mt-24 bg-brand-paper" aria-labelledby="home-about-title">
    <div class="section-shell">
        <div class="home-card grid items-center gap-6 bg-white p-5 sm:p-6 lg:grid-cols-[1fr_18rem] lg:p-8">
            <div class="max-w-3xl">
                <p class="home-section-eyebrow">
                    Tentang Edulaw
                </p>

                <h2 id="home-about-title" class="home-section-title">
                    Ruang belajar dan riset hukum untuk kepentingan publik.
                </h2>

                <p class="home-section-description">
                    {{ $siteName }} merupakan platform edukasi dan riset hukum yang menghubungkan literasi konstitusi, analisis regulasi, pembelajaran publik, serta riset berbasis data. Edulaw menghadirkan pengetahuan hukum yang jernih, kontekstual, dan dapat digunakan secara bertanggung jawab untuk kepentingan publik.
                </p>

            </div>

            @if ($logo)
                <div class="flex min-h-36 items-center justify-center rounded-2xl border border-slate-200 bg-white p-6">
                    <img
                        src="{{ $logo }}"
                        alt="{{ $siteName }}"
                        width="649"
                        height="240"
                        class="max-h-20 w-auto max-w-full object-contain"
                        loading="lazy"
                        decoding="async"
                    >
                </div>
            @endif

            @if ($hasAboutPage || $stats->count() >= 2)
                <div class="flex flex-col gap-4 border-t border-slate-200 pt-5 lg:col-span-2 lg:flex-row lg:items-center">
                    @if ($hasAboutPage)
                        <a
                            href="{{ route('about') }}"
                            class="btn-dark min-h-11 shrink-0"
                        >
                            Kenali Edulaw
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    @endif

                    @if ($stats->count() >= 2)
                        <dl @class([
                            'grid flex-1 auto-rows-fr gap-3 sm:grid-cols-2 lg:ml-auto',
                            'lg:grid-cols-2' => $stats->count() === 2,
                            'lg:grid-cols-3' => $stats->count() === 3,
                            'lg:grid-cols-4' => $stats->count() >= 4,
                        ]) aria-label="Statistik kredibilitas Edulaw Project">
                            @foreach ($stats as $stat)
                                <div class="flex min-h-24 flex-col justify-center rounded-xl bg-brand-paper px-5 py-3" data-home-stat="{{ $stat['label'] }}">
                                    <dt class="order-2 mt-1 text-xs font-bold leading-5 text-slate-600">
                                        {{ $stat['label'] }}
                                    </dt>
                                    <dd class="order-1 font-display text-2xl font-extrabold text-brand-navy">
                                        {{ number_format($stat['value'], 0, ',', '.') }}
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif
                </div>
            @endif
        </div>
    </div>
</section>
