<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.google-tag')

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @php
        $siteSettings = $siteSettings ?? [];
        $defaultTitle = $siteSettings['site.meta_title'] ?? 'Literasi dan Riset Hukum';
        $defaultDescription = $siteSettings['site.meta_description'] ?? 'Akses edukasi, riset, publikasi, dan analisis hukum yang relevan untuk membantu masyarakat memahami isu hukum secara jernih.';
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

        $formatTitle = function (string $title): string {
            $title = \Illuminate\Support\Str::squish(strip_tags($title));

            foreach ([' - Edulaw Project', ' - Program Edulaw', ' - Opportunities Edulaw'] as $suffix) {
                if (\Illuminate\Support\Str::endsWith($title, $suffix)) {
                    $title = \Illuminate\Support\Str::beforeLast($title, $suffix);
                    break;
                }
            }

            if ($title === '' || $title === 'Edulaw Project') {
                $title = 'Literasi dan Riset Hukum';
            }

            return \Illuminate\Support\Str::endsWith($title, '| Edulaw Project')
                ? $title
                : $title.' | Edulaw Project';
        };

        $pageTitle = $formatTitle($section('title', $defaultTitle));
        $metaDescription = \Illuminate\Support\Str::limit(
            \Illuminate\Support\Str::squish(strip_tags($section('meta_description', $defaultDescription))),
            160,
            '…',
        );
        $routeName = request()->route()?->getName();
        $paginationParameter = match ($routeName) {
            'insights.index',
            'insights.categories.show',
            'publications.index',
            'programs.archive',
            'opportunities.index' => 'page',
            'multimedia.index' => 'video_page',
            default => null,
        };
        $queryParameters = request()->query();
        $isIndexablePagination = $paginationParameter !== null
            && array_keys($queryParameters) === [$paginationParameter]
            && filter_var(
                $queryParameters[$paginationParameter] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 2]],
            ) !== false;
        $canonicalUrl = $isIndexablePagination
            ? request()->fullUrl()
            : $absoluteUrl($section('canonical_url', url()->current()));
        $canonicalUrl = \Illuminate\Support\Str::before($canonicalUrl, '#');

        if (! $isIndexablePagination) {
            $canonicalUrl = \Illuminate\Support\Str::before($canonicalUrl, '?');
        }
        $ogType = strip_tags($section('og_type', 'website'));
        $ogImage = $absoluteUrl($section('og_image', $defaultImage));
        $ogImageAlt = \Illuminate\Support\Str::limit(strip_tags($section('og_image_alt', $pageTitle)), 120);
        $robotsOverride = strip_tags($section('robots'));
        $robots = $robotsOverride !== ''
            ? $robotsOverride
            : ($queryParameters !== [] && ! $isIndexablePagination ? 'noindex,follow' : 'index,follow');
    @endphp

    <x-seo
        :title="$pageTitle"
        :description="$metaDescription"
        :canonical="$canonicalUrl"
        :image="$ogImage"
        :image-alt="$ogImageAlt"
        :type="$ogType"
        :robots="$robots"
    />
    <x-structured-data :data="\App\Support\StructuredData::organization()" />

    <meta name="theme-color" content="#1f3c69">

    {{-- Favicon placeholder --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    {{-- Self-hosted brand typography; inlined declarations avoid a font CSS request chain. --}}
    @fonts('lato')

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Lato', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('head')
    @stack('styles')
</head>

<body class="min-h-screen overflow-x-hidden bg-[#f4f8f7] text-brand-ink antialiased">
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
        <main id="main-content" class="site-surface-base flex-1" tabindex="-1">
            @yield('content')
        </main>

        {{-- Footer --}}
        <x-layout.footer />
    </div>

    @stack('scripts')
</body>
</html>
