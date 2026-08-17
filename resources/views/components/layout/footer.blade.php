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

    $channelLinks = collect([
        ['label' => 'Program', 'route' => 'programs.index'],
        ['label' => 'Editorial', 'route' => 'insights.index'],
        ['label' => 'Riset & Publikasi', 'route' => 'publications.index'],
        ['label' => 'Opportunities', 'route' => 'opportunities.index'],
        ['label' => 'Multimedia', 'route' => 'multimedia.index'],
    ])->filter(fn (array $link): bool => Route::has($link['route']));

    $contributionLinks = collect([
        Route::has('collaboration.index') ? ['label' => 'Ajukan Kolaborasi', 'route' => 'collaboration.index'] : null,
        Route::has('about') ? ['label' => 'Tentang Edulaw', 'route' => 'about'] : null,
        Route::has('contact.index') ? ['label' => 'Kontak', 'route' => 'contact.index'] : null,
    ])->filter();

    $socialLinks = collect([
        ['label' => 'Instagram', 'url' => EdulawSite::resolveUrl($settings['social.instagram_url'] ?? null)],
        ['label' => 'YouTube', 'url' => EdulawSite::resolveUrl($settings['social.youtube_url'] ?? null)],
        ['label' => 'LinkedIn', 'url' => EdulawSite::resolveUrl($settings['social.linkedin_url'] ?? null)],
        ['label' => 'Email', 'url' => $emailUrl],
    ])->filter(fn (array $link): bool => filled($link['url']));

    $legalLinks = collect([
        ['label' => 'Kebijakan Privasi', 'route' => 'privacy'],
        ['label' => 'Syarat & Ketentuan', 'route' => 'terms'],
    ])->filter(fn (array $link): bool => Route::has($link['route']));
@endphp

<footer class="bg-[#1f3f6d] text-white">
    <div class="mx-auto grid max-w-7xl gap-9 px-4 pb-9 pt-11 sm:px-6 md:grid-cols-2 lg:grid-cols-[1.25fr_0.8fr_0.85fr_0.75fr_1fr] lg:px-8">
        <div>
            <a href="{{ route('home') }}" class="inline-flex focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#f0c55e]" aria-label="{{ $siteName }} — Beranda">
                @if ($footerLogo)
                    <img
                        src="{{ $footerLogo }}"
                        alt="{{ $siteName }}"
                        width="649"
                        height="240"
                        class="h-12 w-auto max-w-56 object-contain"
                        loading="lazy"
                        decoding="async"
                    >
                @else
                    <span class="text-xl font-extrabold text-white">{{ $siteName }}</span>
                @endif
            </a>

            @if ($siteDescription)
                <p class="mt-4 max-w-xs text-sm leading-6 text-slate-200">{{ $siteDescription }}</p>
            @endif

            @if ($tagline)
                <p class="mt-3 text-sm font-bold text-[#d9a24c]">{{ $tagline }}</p>
            @endif
        </div>

        <nav aria-label="Kanal utama">
            <h2 class="text-sm font-extrabold text-white">Kanal Utama</h2>
            <ul class="mt-5 grid gap-3">
                @foreach ($channelLinks as $link)
                    <li>
                        <a href="{{ route($link['route']) }}" class="text-xs font-medium text-slate-200 transition hover:text-[#f0c55e] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#f0c55e]">
                            {{ $link['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <nav aria-label="Kontribusi">
            <h2 class="text-sm font-extrabold text-white">Kontribusi</h2>
            <ul class="mt-5 grid gap-3">
                @foreach ($contributionLinks as $link)
                    <li>
                        <a href="{{ route($link['route']) }}" class="text-xs font-medium text-slate-200 transition hover:text-[#f0c55e] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#f0c55e]">
                            {{ $link['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div>
            <h2 class="text-sm font-extrabold text-white">Terhubung</h2>
            <div class="mt-5 grid gap-3" aria-label="Media sosial Edulaw Project">
                @foreach ($socialLinks as $link)
                    @if ($link['label'] === 'Email')
                        <!--email_off-->
                    @endif
                    <a
                        href="{{ $link['url'] }}"
                        @if ($link['label'] !== 'Email') target="_blank" rel="noopener noreferrer" @endif
                        class="flex items-center gap-2 text-xs font-medium text-slate-200 transition hover:text-[#f0c55e] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#f0c55e]"
                        aria-label="{{ $link['label'] }} Edulaw Project"
                    >
                        <span class="grid size-5 shrink-0 place-items-center rounded bg-white/10 text-[#f0c55e]">
                            @if ($link['label'] === 'Instagram')
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="3.5"/><path d="M17.5 6.5h.01"/></svg>
                            @elseif ($link['label'] === 'YouTube')
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.6 7.2a3 3 0 0 0-2.1-2.1C17.7 4.6 12 4.6 12 4.6s-5.7 0-7.5.5a3 3 0 0 0-2.1 2.1A31 31 0 0 0 1.9 12a31 31 0 0 0 .5 4.8 3 3 0 0 0 2.1 2.1c1.8.5 7.5.5 7.5.5s5.7 0 7.5-.5a3 3 0 0 0 2.1-2.1 31 31 0 0 0 .5-4.8 31 31 0 0 0-.5-4.8ZM10 15.4V8.6l5.8 3.4L10 15.4Z"/></svg>
                            @elseif ($link['label'] === 'LinkedIn')
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.7 20.5H3.2V9h3.5v11.5ZM5 7.4a2 2 0 1 1 0-4 2 2 0 0 1 0 4Zm15.8 13.1h-3.5v-5.6c0-1.3 0-3-1.8-3s-2.1 1.4-2.1 2.9v5.7H9.9V9h3.4v1.6h.1a3.7 3.7 0 0 1 3.3-1.8c3.6 0 4.2 2.4 4.2 5.4v6.3Z"/></svg>
                            @else
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M4 4h16v16H4z"/><path d="m22 6-10 7L2 6"/></svg>
                            @endif
                        </span>
                        {{ $link['label'] }}
                    </a>
                    @if ($link['label'] === 'Email')
                        <!--/email_off-->
                    @endif
                @endforeach
            </div>
        </div>

        <div>
            <h2 class="text-sm font-extrabold text-white">Hubungi Kami</h2>
            <div class="mt-5 grid gap-3 text-xs font-medium text-slate-200">
                @if ($emailUrl)
                    <!--email_off-->
                    <a href="{{ $emailUrl }}" class="break-all transition hover:text-[#f0c55e] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#f0c55e]">{{ $email }}</a>
                    <!--/email_off-->
                @endif
                @if ($whatsappUrl && $whatsappLabel)
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="transition hover:text-[#f0c55e] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#f0c55e]">{{ $whatsappLabel }}</a>
                @endif
                @if ($location)
                    <p>{{ $location }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="border-t border-white/10 bg-[#f8bd38]">
        <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-3 text-xs text-[#142f57] sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <p>© {{ now()->year }} {{ $siteName }}. Hak cipta dilindungi.</p>

                @if ($legalLinks->isNotEmpty())
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                        @foreach ($legalLinks as $link)
                            @if (! $loop->first)
                                <span class="text-[#142f57]/35" aria-hidden="true">|</span>
                            @endif
                            <a href="{{ route($link['route']) }}" class="font-medium hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#142f57]">
                                {{ $link['label'] }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <p class="text-[10px] font-extrabold md:text-right">#TemanBelajarHukumTerbaikmu.</p>
        </div>
    </div>
</footer>
