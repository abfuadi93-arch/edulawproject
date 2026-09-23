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

    $resourceLinks = collect([
        Route::has('about') ? ['label' => 'Tentang Edulaw', 'route' => 'about'] : null,
        Route::has('collaboration.index') ? ['label' => 'Ajukan Kolaborasi', 'route' => 'collaboration.index'] : null,
        Route::has('editorial-standards') ? ['label' => 'Standar Editorial', 'route' => 'editorial-standards'] : null,
        Route::has('corrections-policy') ? ['label' => 'Kebijakan Koreksi', 'route' => 'corrections-policy'] : null,
        Route::has('contact.index') ? ['label' => 'Kontak', 'route' => 'contact.index'] : null,
    ])->filter();

    $socialLinks = collect([
        ['label' => 'Instagram', 'url' => EdulawSite::resolveUrl($settings['social.instagram_url'] ?? null)],
        ['label' => 'YouTube', 'url' => EdulawSite::resolveUrl($settings['social.youtube_url'] ?? null)],
        ['label' => 'LinkedIn', 'url' => EdulawSite::resolveUrl($settings['social.linkedin_url'] ?? null)],
    ])->filter(fn (array $link): bool => filled($link['url']));

    $legalLinks = collect([
        ['label' => 'Standar Editorial', 'route' => 'editorial-standards'],
        ['label' => 'Kebijakan Koreksi', 'route' => 'corrections-policy'],
        ['label' => 'Kebijakan Privasi', 'route' => 'privacy'],
        ['label' => 'Syarat & Ketentuan', 'route' => 'terms'],
    ])->filter(fn (array $link): bool => Route::has($link['route']));
@endphp

<footer class="border-t border-white/10 bg-brand-navy text-white">
    <div class="mx-auto grid max-w-7xl grid-cols-1 items-start gap-8 px-5 py-8 sm:grid-cols-2 sm:px-6 lg:grid-cols-[minmax(0,4fr)_repeat(4,minmax(0,3fr))] lg:gap-6 lg:px-8 lg:py-10">
        <div class="min-w-0 sm:col-span-2 lg:col-span-1">
            <a href="{{ route('home') }}" class="inline-flex focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber" aria-label="{{ $siteName }} — Beranda">
                @if ($footerLogo)
                    <img src="{{ $footerLogo }}" alt="{{ $siteName }}" width="649" height="240" class="h-12 w-auto max-w-56 object-contain" loading="lazy" decoding="async">
                @else
                    <span class="text-xl font-bold text-white">{{ $siteName }}</span>
                @endif
            </a>

            @if ($siteDescription)
                <p class="mt-4 max-w-sm text-sm leading-6 text-slate-200">{{ $siteDescription }}</p>
            @endif

            @if ($tagline)
                <p class="mt-4 text-sm font-bold text-brand-amber">{{ $tagline }}</p>
            @endif
        </div>

        <nav class="min-w-0" aria-label="Navigasi footer">
            <h2 class="text-base font-bold text-brand-amber">Navigasi</h2>
            <ul class="mt-4 grid gap-2.5">
                @foreach ($channelLinks as $link)
                    <li><a href="{{ route($link['route']) }}" class="text-sm font-medium text-slate-200 transition hover:text-brand-amber focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">{{ $link['label'] }}</a></li>
                @endforeach
            </ul>
        </nav>

        <nav class="min-w-0" aria-label="Sumber daya footer">
            <h2 class="text-base font-bold text-brand-amber">Sumber Daya</h2>
            <ul class="mt-4 grid gap-2.5">
                @foreach ($resourceLinks as $link)
                    <li><a href="{{ route($link['route']) }}" class="text-sm font-medium text-slate-200 transition hover:text-brand-amber focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">{{ $link['label'] }}</a></li>
                @endforeach
            </ul>
        </nav>

        <div class="min-w-0">
            <h2 class="text-base font-bold text-brand-amber">Kontak</h2>
            <ul class="mt-4 grid gap-2.5">
                @if ($emailUrl)
                    <li><!--email_off--><a href="{{ $emailUrl }}" class="break-words text-sm text-slate-200 transition hover:text-brand-amber focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">{{ $email }}</a><!--/email_off--></li>
                @endif
                @if ($whatsappUrl && $whatsappLabel)
                    <li><a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-slate-200 transition hover:text-brand-amber">{{ $whatsappLabel }}</a></li>
                @endif
                @if ($location)<li><p class="text-sm leading-6 text-slate-300">{{ $location }}</p></li>@endif
            </ul>
        </div>

        <div class="min-w-0">
            <h2 class="text-base font-bold text-brand-amber">Ikuti Kami</h2>
            <div class="mt-4 grid grid-cols-1 gap-2.5" aria-label="Media sosial Edulaw Project">
                @foreach ($socialLinks as $link)
                    <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2.5 text-sm font-medium text-slate-200 transition hover:text-brand-amber focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber" aria-label="{{ $link['label'] }} Edulaw Project">
                        <span class="grid size-6 shrink-0 place-items-center rounded-md bg-white/10 text-brand-amber" aria-hidden="true">
                            @if ($link['label'] === 'Instagram')
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="3.5"/><path d="M17.5 6.5h.01"/></svg>
                            @elseif ($link['label'] === 'YouTube')
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M21.6 7.2a3 3 0 0 0-2.1-2.1C17.7 4.6 12 4.6 12 4.6s-5.7 0-7.5.5a3 3 0 0 0-2.1 2.1A31 31 0 0 0 1.9 12a31 31 0 0 0 .5 4.8 3 3 0 0 0 2.1 2.1c1.8.5 7.5.5 7.5.5s5.7 0 7.5-.5a3 3 0 0 0 2.1-2.1 31 31 0 0 0 .5-4.8 31 31 0 0 0-.5-4.8ZM10 15.4V8.6l5.8 3.4L10 15.4Z"/></svg>
                            @elseif ($link['label'] === 'LinkedIn')
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M6.7 20.5H3.2V9h3.5v11.5ZM5 7.4a2 2 0 1 1 0-4 2 2 0 0 1 0 4Zm15.8 13.1h-3.5v-5.6c0-1.3 0-3-1.8-3s-2.1 1.4-2.1 2.9v5.7H9.9V9h3.4v1.6h.1a3.7 3.7 0 0 1 3.3-1.8c3.6 0 4.2 2.4 4.2 5.4v6.3Z"/></svg>
                            @else
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 4h16v16H4z"/><path d="m22 6-10 7L2 6"/></svg>
                            @endif
                        </span>
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="mx-auto flex max-w-7xl flex-col gap-4 px-5 py-5 text-[13px] leading-5 text-slate-300 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
            <p>© {{ now()->year }} {{ $siteName }}. Hak cipta dilindungi.</p>
            @if ($legalLinks->isNotEmpty())
                <nav aria-label="Kebijakan situs" class="flex flex-wrap items-center gap-x-5 gap-y-2">
                    @foreach ($legalLinks as $link)
                        <a href="{{ route($link['route']) }}" class="font-medium transition hover:text-white">{{ $link['label'] }}</a>
                    @endforeach
                </nav>
            @endif
        </div>
    </div>
</footer>
