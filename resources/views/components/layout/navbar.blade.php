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
        ?? \App\Support\EdulawSite::assetUrl($logoValue, 'images/logo/edulaw-logo.png');

    $navItems = [
        [
            'label' => 'Program',
            'url' => '/program',
            'active' => request()->is('program*'),
        ],
        [
            'label' => 'Insight',
            'url' => '/insight',
            'active' => request()->is('insight*'),
        ],
        [
            'label' => 'Publikasi & Riset',
            'url' => '/riset-publikasi',
            'active' => request()->is('riset-publikasi*'),
        ],
        [
            'label' => 'Opportunities',
            'url' => '/opportunities',
            'active' => request()->is('opportunities*'),
        ],
        [
            'label' => 'Multimedia',
            'url' => '/multimedia',
            'active' => request()->is('multimedia*'),
        ],
    ];
@endphp

<header
    x-data="{ mobileMenu: false }"
    class="sticky top-0 z-50 border-b border-slate-200 bg-white"
>
    <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
        <div class="flex h-20 items-center justify-between gap-6">

            {{-- Brand --}}
            <a
                href="{{ url('/') }}"
                class="flex shrink-0 items-center gap-4"
                aria-label="{{ $siteName }}"
            >
                <img
                    src="{{ $logo }}"
                    alt="{{ $siteName }}"
                    class="h-10 w-auto"
                >

                <div class="hidden md:block">
                    <div class="text-sm font-black uppercase tracking-[0.18em] text-brand-navy">
                        {{ $siteName }}
                    </div>

                    <div class="text-xs font-medium text-slate-500">
                        {{ $navSubtitle }}
                    </div>
                </div>
            </a>

            {{-- Desktop Navigation --}}
            <nav class="hidden flex-1 items-center justify-center gap-7 lg:flex xl:gap-9">
                @foreach($navItems as $item)
                    <a
                        href="{{ url($item['url']) }}"
                        @if($item['active']) aria-current="page" @endif
                        class="group relative whitespace-nowrap text-sm font-semibold transition duration-300
                            {{ $item['active']
                                ? 'text-brand-navy'
                                : 'text-slate-600 hover:text-brand-navy' }}"
                    >
                        {{ $item['label'] }}

                        <span
                            class="absolute -bottom-7 left-0 h-0.5 bg-brand-amber transition-all duration-300
                                {{ $item['active']
                                    ? 'w-full'
                                    : 'w-0 group-hover:w-full' }}"
                        ></span>
                    </a>
                @endforeach
            </nav>

            {{-- Desktop Actions --}}
            <div class="hidden shrink-0 items-center gap-3 lg:flex">
                <a
                    href="{{ url('/search') }}"
                    aria-label="Cari"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 transition duration-300 hover:border-brand-navy hover:text-brand-navy"
                >
                    <svg
                        class="h-5 w-5"
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

                <a
                    href="{{ url('/kolaborasi') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-brand-navy px-5 py-3 text-sm font-bold text-white shadow-sm transition duration-300 hover:-translate-y-0.5 hover:bg-slate-900"
                >
                    Ajukan Kolaborasi
                </a>
            </div>

            {{-- Mobile Trigger --}}
            <button
                type="button"
                @click="mobileMenu = ! mobileMenu"
                class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 transition hover:border-brand-navy hover:text-brand-navy lg:hidden"
                :aria-expanded="mobileMenu.toString()"
                aria-label="Buka menu"
            >
                <svg
                    x-show="!mobileMenu"
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
                    x-show="mobileMenu"
                    x-cloak
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

    {{-- Mobile Menu --}}
    <div
        x-show="mobileMenu"
        x-transition.opacity.duration.200ms
        x-cloak
        class="border-t border-slate-200 bg-white lg:hidden"
    >
        <div class="mx-auto max-w-7xl px-5 py-4">
            <nav class="space-y-1">

                <a
                    href="{{ url('/') }}"
                    class="block rounded-xl px-4 py-3 text-sm font-semibold
                        {{ $isHome
                            ? 'bg-brand-navy text-white'
                            : 'text-slate-700 hover:bg-slate-100 hover:text-brand-navy' }}"
                >
                    Beranda
                </a>

                @foreach($navItems as $item)
                    <a
                        href="{{ url($item['url']) }}"
                        class="block rounded-xl px-4 py-3 text-sm font-semibold
                            {{ $item['active']
                                ? 'bg-brand-navy text-white'
                                : 'text-slate-700 hover:bg-slate-100 hover:text-brand-navy' }}"
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach

                <div class="my-4 border-t border-slate-200"></div>

                <a
                    href="{{ url('/search') }}"
                    class="flex items-center justify-between rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-brand-navy"
                >
                    <span>Cari</span>

                    <svg
                        class="h-4.5 w-4.5"
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

                <a
                    href="{{ url('/kolaborasi') }}"
                    class="mt-3 block rounded-xl bg-brand-navy px-4 py-3 text-center text-sm font-bold text-white transition hover:bg-slate-900"
                >
                    Ajukan Kolaborasi
                </a>

            </nav>
        </div>
    </div>
</header>
