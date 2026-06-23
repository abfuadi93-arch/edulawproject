<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @php
        $siteSettings = $siteSettings ?? [];
        $defaultTitle = $siteSettings['site.meta_title'] ?? 'Edulaw Project - Platform Literasi Hukum Digital';
        $defaultDescription = $siteSettings['site.meta_description'] ?? 'Edulaw Project adalah platform literasi hukum digital yang menghadirkan edukasi hukum, riset, program, multimedia, opportunities, dan ruang kolaborasi.';
    @endphp

    <title>@yield('title', $defaultTitle)</title>

    <meta
        name="description"
        content="@yield('meta_description', $defaultDescription)"
    >

    <meta name="theme-color" content="#1f3c69">

    {{-- Open Graph --}}
    <meta property="og:title" content="@yield('og_title', $defaultTitle)">
    <meta property="og:description" content="@yield('og_description', $defaultDescription)">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og_image', asset('images/og-edulaw.jpg'))">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('twitter_title', $defaultTitle)">
    <meta name="twitter:description" content="@yield('twitter_description', $defaultDescription)">
    <meta name="twitter:image" content="@yield('twitter_image', asset('images/og-edulaw.jpg'))">

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

    @stack('styles')
</head>

<body class="min-h-screen overflow-x-hidden bg-white text-brand-ink antialiased">
    <div class="flex min-h-screen flex-col">
        {{-- Navbar --}}
        <x-layout.navbar />

        {{-- Main content --}}
        <main class="flex-1">
            @yield('content')
        </main>

        {{-- Footer --}}
        <x-layout.footer />
    </div>

    @stack('scripts')
</body>
</html>
