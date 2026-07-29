<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @php
        $siteSettings = $siteSettings ?? [];
        $defaultTitle = $siteSettings['site.meta_title'] ?? 'Edulaw Project - Platform Literasi Hukum Digital';
        $defaultDescription = $siteSettings['site.meta_description'] ?? 'Edulaw Project adalah platform literasi hukum digital yang menghadirkan edukasi hukum, riset, program, multimedia, opportunities, dan ruang kolaborasi.';
        $defaultImage = asset('images/hero/hero-edulaw.jpg');
        $section = fn (string $name, ?string $fallback = null): string => trim($__env->yieldContent($name, $fallback ?? ''));
        $absoluteUrl = function (?string $value): string {
            $value = trim((string) $value);

            if ($value === '') {
                return url('/');
            }

            return \Illuminate\Support\Str::startsWith($value, ['http://', 'https://'])
                ? $value
                : url($value);
        };

        $pageTitle = strip_tags($section('title', $defaultTitle));
        $metaDescription = \Illuminate\Support\Str::limit(strip_tags($section('meta_description', $defaultDescription)), 220);
        $canonicalUrl = $absoluteUrl($section('canonical_url', url()->current()));
        $ogTitle = strip_tags($section('og_title', $pageTitle));
        $ogDescription = \Illuminate\Support\Str::limit(strip_tags($section('og_description', $metaDescription)), 220);
        $ogType = strip_tags($section('og_type', 'website'));
        $ogUrl = $absoluteUrl($section('og_url', $canonicalUrl));
        $ogImage = $absoluteUrl($section('og_image', $defaultImage));
        $ogImageAlt = \Illuminate\Support\Str::limit(strip_tags($section('og_image_alt', $ogTitle)), 120);
        $twitterTitle = strip_tags($section('twitter_title', $ogTitle));
        $twitterDescription = \Illuminate\Support\Str::limit(strip_tags($section('twitter_description', $ogDescription)), 220);
        $twitterImage = $absoluteUrl($section('twitter_image', $ogImage));
        $twitterUrl = $absoluteUrl($section('twitter_url', $ogUrl));
    @endphp

    <title>{{ $pageTitle }}</title>

    <meta
        name="description"
        content="{{ $metaDescription }}"
    >

    <meta name="theme-color" content="#1f3c69">
    <link rel="canonical" href="{{ $canonicalUrl }}">

    {{-- Open Graph --}}
    <meta property="og:site_name" content="Edulaw Project">
    <meta property="og:locale" content="id_ID">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:url" content="{{ $ogUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:secure_url" content="{{ $ogImage }}">
    <meta property="og:image:alt" content="{{ $ogImageAlt }}">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $twitterTitle }}">
    <meta name="twitter:description" content="{{ $twitterDescription }}">
    <meta name="twitter:image" content="{{ $twitterImage }}">
    <meta name="twitter:url" content="{{ $twitterUrl }}">

    {{-- Favicon placeholder --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    {{-- Brand typography --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet"
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7017591389930348"
        crossorigin="anonymous"></script>

    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('head')
    @stack('styles')
</head>

<body class="min-h-screen overflow-x-hidden bg-white text-brand-ink antialiased">
    <a
        href="#main-content"
        class="fixed left-4 top-4 z-[100] -translate-y-24 rounded-lg bg-brand-navy px-4 py-3 text-sm font-bold text-white shadow-lg transition focus:translate-y-0"
    >
        Lewati ke konten utama
    </a>

    <div class="flex min-h-screen flex-col">
        {{-- Navbar --}}
        <x-layout.navbar />

        {{-- Main content --}}
        <main id="main-content" class="flex-1" tabindex="-1">
            @yield('content')
        </main>

        {{-- Footer --}}
        <x-layout.footer />
    </div>

    @stack('scripts')
</body>
</html>
