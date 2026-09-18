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
        <div class="flex h-[72px] items-center justify-between gap-6">

            {{-- BRAND --}}
            <a
                href="{{ route('home') }}"
                class="group flex min-w-0 shrink-0 items-center gap-3"
                aria-label="{{ $siteName }}"
            >
                <div class="flex h-10 w-10 shrink-0 items-center justify-center">
                    <img
                        src="{{ $logo }}"
                        alt="{{ $siteName }}"
                        width="378"
                        height="512"
                        class="h-10 w-auto object-contain transition duration-300 group-hover:scale-[1.03]"
                        decoding="async"
                    >
                </div>

                <div class="min-w-0 leading-none">
                    <div
                        class="whitespace-nowrap text-[13px] font-extrabold uppercase tracking-[0.17em] text-brand-navy sm:text-sm"
                    >
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
                class="hidden min-w-0 flex-1 items-center justify-center lg:flex"
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
            <div class="hidden shrink-0 items-center gap-2.5 lg:flex">

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


            {{-- MOBILE MENU BUTTON --}}
            <button
                type="button"
                data-mobile-menu-button
                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg
                       border border-slate-200 bg-white text-slate-700
                       transition hover:border-slate-300 hover:bg-slate-50 hover:text-brand-navy lg:hidden"
                aria-expanded="false"
                aria-label="Buka menu"
                aria-controls="mobile-navigation"
            >
                <svg
                    data-mobile-menu-open-icon
                    class="h-5 w-5"
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
                    class="h-5 w-5"
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


    {{-- MOBILE NAVIGATION --}}
    <div
        id="mobile-navigation"
        data-mobile-navigation
        hidden
        class="border-t border-slate-200 bg-white shadow-lg lg:hidden"
    >
        <div class="mx-auto max-w-7xl px-5 py-4 sm:px-6">

            <nav class="space-y-1" aria-label="Navigasi mobile">
                <a data-mobile-first-link href="{{ route('home') }}" @if ($isHome) aria-current="page" @endif
                   class="block rounded-lg px-3.5 py-2.5 text-sm font-bold {{ $isHome ? 'bg-brand-navy text-white' : 'text-slate-700 hover:bg-slate-50' }}">Beranda</a>
                @foreach ($navItems as $item)
                    <a href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif
                       class="flex items-center justify-between rounded-lg px-3.5 py-2.5 text-sm font-bold {{ $item['active'] ? 'bg-brand-navy text-white' : 'text-slate-700 hover:bg-slate-50 hover:text-brand-navy' }}">
                        <span>{{ $item['label'] }}</span>
                        @if ($item['active'])<span class="h-1.5 w-1.5 rounded-full bg-brand-amber" aria-hidden="true"></span>@endif
                    </a>
                @endforeach
            </nav>


            {{-- MOBILE ACTIONS --}}
            <div class="mt-4 grid grid-cols-1 gap-2 border-t border-slate-100 pt-4 sm:grid-cols-2">

                <a
                    href="{{ route('search.index') }}"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-lg
                           border border-slate-200 bg-white px-4 text-sm font-bold
                           text-slate-700 transition
                           hover:border-slate-300 hover:bg-slate-50 hover:text-brand-navy"
                >
                    <svg
                        class="h-4 w-4"
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

                    Cari
                </a>

                <a
                    href="{{ route('collaboration.index') }}"
                    class="inline-flex h-11 items-center justify-center rounded-lg
                           bg-brand-navy px-4 text-sm font-bold text-white
                           transition hover:bg-slate-900"
                >
                    Ajukan Kolaborasi
                </a>

            </div>
        </div>
    </div>
</header>
