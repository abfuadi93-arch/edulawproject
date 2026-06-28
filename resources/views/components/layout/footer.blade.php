@php
    $siteSettings = $siteSettings ?? [];
    $siteName = $siteSettings['site.name'] ?? 'Edulaw Project';
    $footerLogo = \App\Support\EdulawSite::assetUrl($siteSettings['site.footer_logo'] ?? null, 'images/logo/edulaw-logo.png');
    $siteDescription = $siteSettings['site.short_description'] ?? 'Platform literasi hukum digital yang menghadirkan edukasi, riset, program, multimedia, dan kanal pengembangan hukum.';
    $tagline = $siteSettings['site.tagline'] ?? 'Equal. Educative. Embrace.';
    $email = $siteSettings['contact.email'] ?? 'hello@edulawproject.id';
    $whatsappLabel = $siteSettings['contact.whatsapp_label'] ?? '0815-2992-7677';
    $whatsappUrl = \App\Support\EdulawSite::resolveUrl($siteSettings['contact.whatsapp_url'] ?? null, 'https://wa.me/6281529927677');
    $location = $siteSettings['contact.location'] ?? 'Jakarta, Indonesia';
    $instagramUrl = \App\Support\EdulawSite::resolveUrl($siteSettings['social.instagram_url'] ?? null, 'https://www.instagram.com/edulaw.project');
    $youtubeUrl = \App\Support\EdulawSite::resolveUrl($siteSettings['social.youtube_url'] ?? null, 'https://www.youtube.com/@EdulawProject');
    $linkedinUrl = \App\Support\EdulawSite::resolveUrl($siteSettings['social.linkedin_url'] ?? null, 'https://www.linkedin.com/company/edulaw-project/');
@endphp

<footer class="border-t border-slate-200 bg-white text-brand-ink">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-12">
        <div class="grid gap-10 lg:grid-cols-[1.15fr_2.85fr]">
            {{-- Brand --}}
            <div>
                <a href="{{ url('/') }}" class="inline-flex items-center">
                    <img
                        src="{{ $footerLogo }}"
                        alt="{{ $siteName }}"
                        class="h-12 w-auto max-w-52 object-contain"
                    >
                </a>

                <p class="mt-5 max-w-sm text-sm leading-7 text-slate-600">
                    {{ $siteDescription }}
                </p>

                <p class="mt-5 inline-flex rounded-full border border-brand-silver bg-brand-paper px-3.5 py-1.5 text-xs font-bold text-brand-ink">
                    {{ $tagline }}
                </p>

            </div>

            <div>
                <div class="grid gap-8 sm:grid-cols-3">
                    {{-- Kanal Utama --}}
                    <div>
                        <h3 class="text-sm font-extrabold text-brand-ink">
                            Kanal Utama
                        </h3>

                        <ul class="mt-5 space-y-3 text-sm">
                            <li><a href="{{ url('/insight') }}" class="text-slate-600 transition hover:text-brand-ink">Insight</a></li>
                            <li><a href="{{ url('/publikasi') }}" class="text-slate-600 transition hover:text-brand-ink">Riset &amp; Publikasi</a></li>
                            <li><a href="{{ url('/program') }}" class="text-slate-600 transition hover:text-brand-ink">Program</a></li>
                            <li><a href="{{ url('/peluang') }}" class="text-slate-600 transition hover:text-brand-ink">Opportunities</a></li>
                            <li><a href="{{ url('/multimedia') }}" class="text-slate-600 transition hover:text-brand-ink">Multimedia</a></li>
                        </ul>
                    </div>

                    {{-- Kolaborasi --}}
                    <div>
                        <h3 class="text-sm font-extrabold text-brand-ink">
                            Kolaborasi
                        </h3>

                        <ul class="mt-5 space-y-3 text-sm">
                            <li><a href="{{ url('/kolaborasi') }}" class="text-slate-600 transition hover:text-brand-ink">Ajukan Kolaborasi</a></li>
                            <li><a href="{{ url('/tentang') }}" class="text-slate-600 transition hover:text-brand-ink">Tentang Edulaw</a></li>
                            <li><a href="{{ url('/kontak') }}" class="text-slate-600 transition hover:text-brand-ink">Kontak</a></li>
                        </ul>
                    </div>

                    {{-- Follow Us --}}
                    <div>
                        <h3 class="text-sm font-extrabold text-brand-ink">
                            Follow Us
                        </h3>

                        <ul class="mt-5 space-y-3.5 text-sm font-semibold text-slate-600">
                            <li>
                                <a
                                    href="{{ $instagramUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="group flex items-center gap-2.5 transition hover:text-brand-ink"
                                >
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-[#f58529] via-[#dd2a7b] to-[#8134af] text-white shadow-sm transition group-hover:-translate-y-0.5">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <rect x="4" y="4" width="16" height="16" rx="5" stroke="currentColor" stroke-width="1.8"/>
                                            <circle cx="12" cy="12" r="3.5" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M17.2 6.8h.01" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    Instagram
                                </a>
                            </li>

                            <li>
                                <a
                                    href="{{ $youtubeUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="group flex items-center gap-2.5 transition hover:text-brand-ink"
                                >
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#ff0033] text-white shadow-sm transition group-hover:-translate-y-0.5">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M21 12s0-3.5-.45-5a2.6 2.6 0 0 0-1.85-1.85C17.2 4.75 12 4.75 12 4.75s-5.2 0-6.7.4A2.6 2.6 0 0 0 3.45 7C3 8.5 3 12 3 12s0 3.5.45 5a2.6 2.6 0 0 0 1.85 1.85c1.5.4 6.7.4 6.7.4s5.2 0 6.7-.4A2.6 2.6 0 0 0 20.55 17C21 15.5 21 12 21 12Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                            <path d="m10 9 5 3-5 3V9Z" fill="currentColor"/>
                                        </svg>
                                    </span>
                                    YouTube
                                </a>
                            </li>

                            <li>
                                <a
                                    href="{{ $linkedinUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="group flex items-center gap-2.5 transition hover:text-brand-ink"
                                >
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#0a66c2] text-white shadow-sm transition group-hover:-translate-y-0.5">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M6.5 9.5V18M10.5 18v-5.1c0-2 1.2-3.4 3.2-3.4 1.9 0 3.3 1.2 3.3 3.6V18M6.5 6.5h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            <rect x="4" y="4" width="16" height="16" rx="3" stroke="currentColor" stroke-width="1.8"/>
                                        </svg>
                                    </span>
                                    LinkedIn
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Terhubung --}}
                <div class="mt-8 border-t border-slate-100 pt-6">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="flex min-w-0 items-start gap-2.5 text-sm">
                            <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-paper text-brand-navy">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M4 6h16v12H4V6Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    <path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <p class="font-extrabold text-brand-ink">Email</p>
                                <a href="mailto:{{ $email }}" class="mt-0.5 block truncate text-slate-600 transition hover:text-brand-navy">
                                    {{ $email }}
                                </a>
                            </div>
                        </div>

                        <div class="flex min-w-0 items-start gap-2.5 text-sm">
                            <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-paper text-brand-navy">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M7 4h10v16H7V4Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    <path d="M11 17h2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <p class="font-extrabold text-brand-ink">WhatsApp</p>
                                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="mt-0.5 block truncate text-slate-600 transition hover:text-brand-navy">
                                    {{ $whatsappLabel }}
                                </a>
                            </div>
                        </div>

                        <div class="flex min-w-0 items-start gap-2.5 text-sm">
                            <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-paper text-brand-navy">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 21s7-5.1 7-11a7 7 0 1 0-14 0c0 5.9 7 11 7 11Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    <circle cx="12" cy="10" r="2.2" stroke="currentColor" stroke-width="1.8"/>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <p class="font-extrabold text-brand-ink">Lokasi</p>
                                <p class="mt-0.5 truncate text-slate-600">
                                    {{ $location }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom --}}
        <div class="mt-8 flex flex-col gap-3 border-t border-slate-200 pt-5 text-sm font-medium text-slate-500 md:flex-row md:items-center md:justify-between">
            <p>
                © {{ now()->year }} {{ $siteName }}. All rights reserved.
            </p>

            <div class="flex flex-wrap gap-x-5 gap-y-2">
                <a href="{{ url('/kebijakan-privasi') }}" class="transition hover:text-brand-ink">
                    Kebijakan Privasi
                </a>
                <a href="{{ url('/syarat-ketentuan') }}" class="transition hover:text-brand-ink">
                    Syarat &amp; Ketentuan
                </a>
            </div>
        </div>
    </div>
</footer>
