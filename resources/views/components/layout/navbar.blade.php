@php
    $settings = $siteSettings ?? \App\Support\EdulawSite::settings();

    $siteName = $siteName
        ?? ($settings['site.name'] ?? 'Edulaw Project');

    $navSubtitle = $navSubtitle
        ?? ($settings['site.nav_subtitle'] ?? null)
        ?? ($settings['site.tagline'] ?? null)
        ?? 'Legal Education · Research · Policy';

    $isHome = $isHome ?? request()->routeIs('home') || request()->is('/');

    $logoValue = $logoValue
        ?? ($settings['site.logo'] ?? null)
        ?? ($settings['site.footer_logo'] ?? null);

    $logo = $logo
        ?? \App\Support\EdulawSite::assetUrl($logoValue, 'images/logo/edulaw-icon.png');

    $navItems = [
        [
            'label' => 'Program',
            'url' => route('programs.index'),
            'active' => request()->routeIs('programs.*'),
        ],
        [
            'label' => 'Editorial',
            'url' => route('insights.index'),
            'active' => request()->routeIs('insights.*'),
        ],
        [
            'label' => 'Riset & Publikasi',
            'url' => route('publications.index'),
            'active' => request()->routeIs('publications.*'),
        ],
        [
            'label' => 'Opportunities',
            'url' => route('opportunities.index'),
            'active' => request()->routeIs('opportunities.*'),
        ],
        [
            'label' => 'Multimedia',
            'url' => route('multimedia.index'),
            'active' => request()->routeIs('multimedia.*'),
        ],
        [
            'label' => 'Tentang',
            'url' => route('about'),
            'active' => request()->routeIs('about'),
        ],
    ];
@endphp

<header
    data-site-header
    class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/95 backdrop-blur-xl"
>
    <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between gap-4 sm:h-[72px] sm:gap-6">

            {{-- BRAND --}}
            <a
                href="{{ route('home') }}"
                class="group flex min-w-0 shrink-0 items-center gap-3"
                aria-label="{{ $siteName }}"
            >
                <div class="flex h-9 w-9 shrink-0 items-center justify-center sm:h-10 sm:w-10">
                    <img
                        src="{{ $logo }}"
                        alt="{{ $siteName }}"
                        width="378"
                        height="512"
                        class="h-9 w-auto object-contain transition duration-300 group-hover:scale-[1.03] sm:h-10"
                        decoding="async"
                    >
                </div>

                <div class="min-w-0 leading-none">
                    <div class="whitespace-nowrap text-[12px] font-extrabold uppercase tracking-[0.16em] text-brand-navy sm:text-sm sm:tracking-[0.17em]">
                        {{ $siteName }}
                    </div>

                    <div
                        class="mt-1 hidden whitespace-nowrap text-xs font-medium tracking-[0.02em] text-slate-500 sm:block"
                    >
                        {{ $navSubtitle }}
                    </div>
                </div>
            </a>


            {{-- DESKTOP NAV --}}
            <nav
                class="hidden min-w-0 flex-1 items-center justify-center xl:flex"
                aria-label="Navigasi utama"
            >
                    @foreach ($navItems as $item)
                        <a href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif
                           class="relative whitespace-nowrap rounded-lg px-3 py-2 text-[13px] font-bold transition hover:bg-slate-50 hover:text-brand-navy {{ $item['active'] ? 'bg-slate-50 text-brand-navy' : 'text-slate-600' }}">
                            {{ $item['label'] }}
                            @if ($item['active'])
                                <span class="absolute inset-x-3 -bottom-[17px] h-0.5 rounded-full bg-brand-amber" aria-hidden="true"></span>
                            @endif
                        </a>
                    @endforeach
            </nav>


            {{-- DESKTOP ACTIONS --}}
            <div class="hidden shrink-0 items-center gap-2.5 xl:flex">

                {{-- SEARCH ICON --}}
                <a
                    href="{{ route('search.index') }}"
                    aria-label="Cari artikel, topik, atau publikasi"
                    title="Cari"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg
                           border border-slate-200 bg-white text-slate-500
                           transition duration-200
                           hover:border-slate-300 hover:bg-slate-50 hover:text-brand-navy"
                >
                    <svg
                        class="h-[18px] w-[18px]"
                        viewBox="0 0 24 24"
                        fill="none"
                        aria-hidden="true"
                    >
                        <path
                            d="m21 21-4.35-4.35M18.5 11a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                        />
                    </svg>
                </a>

                {{-- CTA --}}
                <a
                    href="{{ route('collaboration.index') }}"
                    class="inline-flex h-10 items-center justify-center rounded-lg
                           bg-brand-navy px-4 text-[13px] font-bold text-white
                           shadow-sm transition duration-300
                           hover:-translate-y-px hover:bg-slate-900 hover:shadow-sm"
                >
                    Ajukan Kolaborasi
                </a>
            </div>


            <div class="flex shrink-0 items-center gap-1.5 xl:hidden">
                <a
                    href="{{ route('search.index') }}"
                    aria-label="Cari artikel, topik, atau publikasi"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full text-slate-600 transition hover:bg-slate-100 hover:text-brand-navy"
                >
                    <svg class="h-[19px] w-[19px]" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="m21 21-4.35-4.35M18.5 11a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </a>

                {{-- MOBILE MENU BUTTON --}}
                <button
                    type="button"
                    data-mobile-menu-button
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-full border border-slate-200 bg-white px-3 text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 hover:text-brand-navy"
                    aria-expanded="false"
                    aria-label="Buka menu"
                    aria-controls="mobile-navigation"
                >
                    <span class="hidden text-[11px] font-extrabold uppercase tracking-[0.12em] sm:inline" data-mobile-menu-label>Menu</span>
                <svg
                    data-mobile-menu-open-icon
                        class="h-[18px] w-[18px]"
                    viewBox="0 0 24 24"
                    fill="none"
                    aria-hidden="true"
                >
                    <path
                        d="M4 7H20M4 12H20M4 17H20"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                    />
                </svg>

                <svg
                    data-mobile-menu-close-icon
                    hidden
                        class="hidden h-[18px] w-[18px]"
                    viewBox="0 0 24 24"
                    fill="none"
                    aria-hidden="true"
                >
                    <path
                        d="M6 6L18 18M18 6L6 18"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                    />
                </svg>
                </button>
            </div>

        </div>
    </div>


    {{-- MOBILE NAVIGATION --}}
    <div
        id="mobile-navigation"
        data-mobile-navigation
        hidden
        role="dialog"
        aria-modal="true"
        aria-labelledby="mobile-navigation-title"
        class="absolute inset-x-0 top-full h-[calc(100dvh-4rem)] overflow-y-auto overscroll-contain border-t border-slate-200 bg-[#f8f6f1] sm:h-[calc(100dvh-4.5rem)] xl:hidden"
    >
        <div class="mx-auto flex min-h-full max-w-7xl flex-col px-5 py-6 sm:px-6 sm:py-8 lg:px-8">
            <div class="grid gap-7 lg:grid-cols-[minmax(0,1fr)_18rem] lg:gap-12">
                <div>
                    <div class="flex items-end justify-between gap-4 border-b border-slate-300 pb-4">
                        <div>
                            <p class="text-[10px] font-extrabold uppercase tracking-[0.2em] text-brand-caramel">Jelajahi Edulaw</p>
                            <h2 id="mobile-navigation-title" class="mt-1 text-2xl font-extrabold tracking-[-0.03em] text-brand-navy sm:text-3xl">Menu utama</h2>
                        </div>
                        <span class="hidden text-xs font-semibold text-slate-500 sm:block">Equal · Educative · Embrace</span>
                    </div>

                    <a
                        data-mobile-first-link
                        href="{{ route('search.index') }}"
                        class="group mt-5 flex min-h-14 items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 text-slate-500 shadow-sm transition hover:border-slate-300 hover:text-brand-navy"
                    >
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m21 21-4.35-4.35M18.5 11a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                        <span class="min-w-0 flex-1 truncate text-sm font-semibold">Cari artikel, program, atau publikasi</span>
                        <span class="text-lg transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
                    </a>

                    @php
                        $mobileNavItems = collect([[
                            'label' => 'Beranda',
                            'url' => route('home'),
                            'active' => $isHome,
                        ]])->concat($navItems);
                    @endphp

                    <nav class="mt-6 grid border-t border-slate-300 sm:grid-cols-2 sm:gap-x-8" aria-label="Navigasi mobile">
                        @foreach ($mobileNavItems as $item)
                            <a href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif
                               class="group flex min-h-[58px] items-center gap-4 border-b border-slate-300 py-3 transition {{ $item['active'] ? 'text-brand-navy' : 'text-slate-700 hover:text-brand-navy' }}">
                                <span class="w-5 shrink-0 text-[10px] font-extrabold tabular-nums tracking-[0.08em] {{ $item['active'] ? 'text-brand-caramel' : 'text-slate-400' }}">
                                    {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                <span class="min-w-0 flex-1 text-base font-extrabold tracking-[-0.01em] sm:text-lg">{{ $item['label'] }}</span>
                                <span class="text-lg {{ $item['active'] ? 'text-brand-caramel' : 'text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-brand-caramel' }}" aria-hidden="true">→</span>
                            </a>
                        @endforeach
                    </nav>
                </div>

                <aside class="self-start rounded-2xl bg-brand-navy p-5 text-white shadow-lg shadow-brand-navy/10 sm:p-6 lg:mt-[60px]" aria-label="Kolaborasi Edulaw">
                    <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-brand-amber">Ruang Kolaborasi</p>
                    <p class="mt-3 text-xl font-extrabold leading-snug tracking-[-0.02em]">Tumbuhkan literasi hukum bersama Edulaw.</p>
                    <p class="mt-2 text-sm leading-6 text-slate-300">Terbuka untuk kampus, komunitas, lembaga, dan mitra strategis.</p>

                <a
                        href="{{ route('collaboration.index') }}"
                        class="mt-5 inline-flex min-h-11 w-full items-center justify-between rounded-xl bg-brand-amber px-4 text-sm font-extrabold text-brand-navy transition hover:bg-amber-300"
                >
                        <span>Ajukan Kolaborasi</span>
                        <span aria-hidden="true">→</span>
                </a>

                    <a href="{{ route('about') }}" class="mt-4 inline-flex text-xs font-bold text-slate-300 underline decoration-slate-500 underline-offset-4 transition hover:text-white">Tentang Edulaw Project</a>
                </aside>
            </div>

            <div class="mt-auto flex flex-wrap items-center justify-between gap-2 border-t border-slate-300 pt-5 text-[11px] font-semibold text-slate-500">
                <span>{{ $navSubtitle }}</span>
                <span>{{ now()->year }} · Edulaw Project</span>
            </div>
        </div>
    </div>
</header>
