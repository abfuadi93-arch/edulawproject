@php
    use App\Support\EdulawSite;
    use Illuminate\Support\Facades\Route;

    $settings = $siteSettings ?? EdulawSite::settings();
    $siteName = filled($settings['site.name'] ?? null) ? $settings['site.name'] : 'Edulaw Project';
    $siteDescription = filled($settings['site.short_description'] ?? null) ? $settings['site.short_description'] : null;
    $tagline = filled($settings['site.tagline'] ?? null) ? $settings['site.tagline'] : null;
    $footerLogo = EdulawSite::assetUrl('images/logo/edulaw-logo-white.png')
        ?: EdulawSite::assetUrl($settings['site.footer_logo'] ?? null, 'images/logo/edulaw-logo.png');

    $email = filter_var($settings['contact.email'] ?? null, FILTER_VALIDATE_EMAIL) ?: null;
    $emailUrl = $email ? EdulawSite::resolveUrl('mailto:'.$email) : null;
    $whatsappLabel = filled($settings['contact.whatsapp_label'] ?? null) ? $settings['contact.whatsapp_label'] : null;
    $whatsappUrl = EdulawSite::resolveUrl($settings['contact.whatsapp_url'] ?? null);
    $location = filled($settings['contact.location'] ?? null) ? $settings['contact.location'] : null;

    $primaryLinks = collect([
        ['label' => 'Beranda', 'route' => 'home'],
        ['label' => 'Program Edulaw', 'route' => 'programs.index'],
        ['label' => 'Editorial', 'route' => 'insights.index'],
        ['label' => 'Riset & Publikasi', 'route' => 'publications.index'],
        ['label' => 'Opportunities', 'route' => 'opportunities.index'],
        ['label' => 'Multimedia', 'route' => 'multimedia.index'],
        ['label' => 'Tentang Edulaw', 'route' => 'about'],
    ])->filter(fn (array $link): bool => Route::has($link['route']));

    $editorialSubmissionRoute = collect([
        'editorial-submissions.create',
        'submissions.create',
        'insights.submit',
    ])->first(fn (string $routeName): bool => Route::has($routeName));

    $writerGuidelineRoute = collect([
        'writer-guidelines',
        'insights.guidelines',
    ])->first(fn (string $routeName): bool => Route::has($routeName));

    $contributionLinks = collect([
        $editorialSubmissionRoute ? ['label' => 'Kirim Tulisan', 'route' => $editorialSubmissionRoute] : null,
        $writerGuidelineRoute ? ['label' => 'Pedoman Penulis', 'route' => $writerGuidelineRoute] : null,
        Route::has('collaboration.index') ? ['label' => 'Ajukan Kolaborasi', 'route' => 'collaboration.index'] : null,
    ])->filter();

    $socialLinks = collect([
        ['label' => 'Instagram', 'url' => EdulawSite::resolveUrl($settings['social.instagram_url'] ?? null)],
        ['label' => 'LinkedIn', 'url' => EdulawSite::resolveUrl($settings['social.linkedin_url'] ?? null)],
    ])->filter(fn (array $link): bool => filled($link['url']));

    $legalLinks = collect([
        ['label' => 'Kebijakan Privasi', 'route' => 'privacy'],
        ['label' => 'Syarat & Ketentuan', 'route' => 'terms'],
    ])->filter(fn (array $link): bool => Route::has($link['route']));
@endphp

<footer class="relative overflow-hidden bg-brand-navy text-white">
    <svg class="pointer-events-none absolute -right-32 top-0 h-full w-[62rem] text-brand-teal/12" viewBox="0 0 1000 620" fill="none" aria-hidden="true">
        <path d="M500-80 930 620H70L500-80Z" stroke="currentColor" stroke-width="2"/>
        <path d="m500 165 250 405H250l250-405Z" stroke="currentColor" stroke-width="2"/>
        <path d="m500 405 115 185H385l115-185Z" stroke="currentColor" stroke-width="2"/>
    </svg>

    <div class="relative z-10 mx-auto grid max-w-7xl gap-9 px-5 py-10 sm:grid-cols-2 sm:px-6 lg:grid-cols-[1.5fr_.9fr_1fr_1.1fr] lg:gap-12 lg:px-8 lg:py-14">
            <div>
                <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">
                    @if ($footerLogo)
                        <img
                            src="{{ $footerLogo }}"
                            alt="{{ $siteName }}"
                            width="649"
                            height="240"
                            class="h-11 w-auto max-w-52 object-contain"
                            loading="lazy"
                            decoding="async"
                        >
                    @else
                        <span class="font-display text-xl font-extrabold text-white">{{ $siteName }}</span>
                    @endif
                </a>

                @if ($siteDescription)
                    <p class="mt-5 max-w-sm text-sm leading-7 text-white/72">{{ $siteDescription }}</p>
                @endif

                @if ($tagline)
                    <p class="mt-4 text-xs font-extrabold tracking-[0.08em] text-brand-amber">{{ $tagline }}</p>
                @endif

                @if ($socialLinks->isNotEmpty())
                    <div class="mt-6 flex flex-wrap gap-2.5" aria-label="Media sosial Edulaw Project">
                        @foreach ($socialLinks as $link)
                            <a
                                href="{{ $link['url'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="{{ $link['label'] }} Edulaw Project"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/20 bg-white/5 text-white transition hover:-translate-y-0.5 hover:border-brand-amber hover:bg-brand-amber hover:text-brand-ink focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber"
                            >
                                @if ($link['label'] === 'Instagram')
                                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <rect x="4" y="4" width="16" height="16" rx="5" stroke="currentColor" stroke-width="1.8"/>
                                        <circle cx="12" cy="12" r="3.5" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M17.2 6.8h.01" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                                    </svg>
                                @else
                                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M7 10v8M7 7h.01M11 18v-4.8c0-2 1.2-3.2 3-3.2s3 1.2 3 3.4V18" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                                        <rect x="3" y="3" width="18" height="18" rx="4" stroke="currentColor" stroke-width="1.8"/>
                                    </svg>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif

                <a href="#main-content" class="mt-7 inline-flex min-h-11 items-center gap-2 rounded-lg border border-white/35 px-4 py-2.5 text-xs font-extrabold uppercase tracking-[0.08em] text-white transition hover:border-brand-amber hover:text-brand-amber focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                    <span aria-hidden="true">↑</span>
                    Kembali ke Atas
                </a>
            </div>

            <nav aria-label="Navigasi footer">
                <h2 class="text-sm font-extrabold text-white">Peta Situs</h2>
                <ul class="mt-4 space-y-1.5 text-sm">
                    @foreach ($primaryLinks as $link)
                        <li>
                            <a href="{{ route($link['route']) }}" class="inline-flex min-h-9 items-center text-white/70 transition hover:text-brand-amber focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div>
                <h2 class="text-sm font-extrabold text-white">Kontribusi</h2>
                <ul class="mt-4 space-y-1.5 text-sm">
                    @foreach ($contributionLinks as $link)
                        <li>
                            <a href="{{ route($link['route']) }}" class="inline-flex min-h-9 items-center text-white/70 transition hover:text-brand-amber focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                @if ($legalLinks->isNotEmpty())
                    <h2 class="mt-6 text-sm font-extrabold text-white">Legal</h2>
                    <ul class="mt-3 space-y-1.5 text-sm">
                        @foreach ($legalLinks as $link)
                            <li>
                                <a href="{{ route($link['route']) }}" class="inline-flex min-h-9 items-center text-white/70 transition hover:text-brand-amber focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                                    {{ $link['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div>
                <h2 class="text-sm font-extrabold text-white">Hubungi Kami</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    @if ($emailUrl)
                        <li><a href="{{ $emailUrl }}" class="inline-flex min-h-9 items-center break-all text-white/70 transition hover:text-brand-amber focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">{{ $email }}</a></li>
                    @endif
                    @if ($whatsappUrl && $whatsappLabel)
                        <li>
                            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-9 items-center gap-1 text-white/70 transition hover:text-brand-amber focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                                WhatsApp: {{ $whatsappLabel }} <span aria-hidden="true">↗</span>
                            </a>
                        </li>
                    @endif
                    @if ($location)
                        <li class="pt-1 leading-6 text-white/60">{{ $location }}</li>
                    @endif
                </ul>
            </div>
    </div>

    <div class="relative z-10 bg-brand-amber px-5 py-3 text-center text-xs font-bold text-brand-ink">
        © {{ now()->year }} {{ $siteName }}. Hak cipta dilindungi.
    </div>
</footer>
