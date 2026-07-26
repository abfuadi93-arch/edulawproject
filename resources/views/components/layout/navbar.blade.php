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
    ];
@endphp

<header
    x-data="{
        mobileMenu: false,
        toggleMenu() {
            this.mobileMenu ? this.closeMenu() : this.openMenu();
        },
        openMenu() {
            this.mobileMenu = true;
            this.$nextTick(() => this.$refs.firstMobileLink?.focus());
        },
        closeMenu(restoreFocus = true) {
            this.mobileMenu = false;
            if (restoreFocus) {
                this.$nextTick(() => this.$refs.menuButton?.focus());
            }
        },
    }"
    @keydown.escape.window="if (mobileMenu) closeMenu()"
    @resize.window="if (window.innerWidth >= 1024) mobileMenu = false"
    @click.outside="mobileMenu = false"
    class="sticky top-0 z-50 border-b border-slate-200 bg-white"
>
    <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
        <div class="flex h-20 items-center justify-between gap-6">

            {{-- Brand --}}
            <a
                href="{{ route('home') }}"
                class="flex shrink-0 items-center gap-4"
                aria-label="{{ $siteName }}"
            >
                <img
                    src="{{ $logo }}"
                    alt="{{ $siteName }}"
                    width="378"
                    height="512"
                    class="h-10 w-auto"
                    decoding="async"
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
            <nav class="hidden flex-1 items-center justify-center gap-7 lg:flex xl:gap-9" aria-label="Navigasi utama">
                @foreach($navItems as $item)
                    <a
                        href="{{ $item['url'] }}"
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
                    href="{{ route('search.index') }}"
                    aria-label="Cari"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 transition duration-300 hover:border-brand-navy hover:text-brand-navy"
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
                    href="{{ route('collaboration.index') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-lg bg-brand-navy px-5 py-2.5 text-sm font-bold text-white shadow-sm transition duration-300 hover:-translate-y-0.5 hover:bg-slate-900"
                >
                    Ajukan Kolaborasi
                </a>
            </div>

            {{-- Mobile Trigger --}}
            <button
                type="button"
                x-ref="menuButton"
                @click="toggleMenu()"
                class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 transition hover:border-brand-navy hover:text-brand-navy lg:hidden"
                aria-expanded="false"
                :aria-expanded="mobileMenu.toString()"
                aria-label="Buka menu"
                :aria-label="mobileMenu ? 'Tutup menu' : 'Buka menu'"
                aria-controls="mobile-navigation"
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
        id="mobile-navigation"
        x-show="mobileMenu"
        x-transition.opacity.duration.200ms
        x-cloak
        class="border-t border-slate-200 bg-white lg:hidden"
    >
        <div class="mx-auto max-w-7xl px-5 py-4">
            <nav class="space-y-1" aria-label="Navigasi mobile">

                <a
                    x-ref="firstMobileLink"
                    href="{{ route('home') }}"
                    @if ($isHome) aria-current="page" @endif
                    class="block rounded-xl px-4 py-3 text-sm font-semibold
                        {{ $isHome
                            ? 'bg-brand-navy text-white'
                            : 'text-slate-700 hover:bg-slate-100 hover:text-brand-navy' }}"
                >
                    Beranda
                </a>

                @foreach($navItems as $item)
                    <a
                        href="{{ $item['url'] }}"
                        @if($item['active']) aria-current="page" @endif
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
                    href="{{ route('search.index') }}"
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
                    href="{{ route('collaboration.index') }}"
                    class="mt-3 block rounded-xl bg-brand-navy px-4 py-3 text-center text-sm font-bold text-white transition hover:bg-slate-900"
                >
                    Ajukan Kolaborasi
                </a>

            </nav>
        </div>
    </div>
</header>
