@props([
    'title' => '',
    'description' => null,
    'eyebrow' => null,
    'breadcrumbs' => [],
    'backgroundImage' => null,
    'backgroundAlt' => '',
    'compact' => false,
    'containerClass' => null,
    'gridClass' => null,
    'titleClass' => null,
    'titleWidthClass' => 'max-w-4xl',
    'contentClass' => null,
    'descriptionClass' => null,
    'accentLine' => false,
    'overlayOpacity' => 0.72,
    'channelHeader' => false,
    'breakTitleAfterColon' => false,
])

@php
    $hasBackground = filled($backgroundImage);
    $isDarkHero = true;
    $defaultGridClass = $compact
        ? 'gap-5 px-4 py-7 sm:w-full sm:px-6 lg:min-h-[240px] lg:grid-cols-2 lg:items-center lg:px-8 lg:py-8'
        : 'gap-8 px-4 py-11 sm:w-full sm:px-6 lg:min-h-[300px] lg:grid-cols-2 lg:items-center lg:px-8 lg:py-16';
    $defaultTitleClass = $compact
        ? 'text-3xl sm:text-4xl lg:text-[2.45rem]'
        : 'text-4xl sm:text-5xl lg:text-[3.25rem]';
    $defaultContainerClass = 'relative z-10 mx-auto grid w-screen max-w-7xl '.($gridClass ?: $defaultGridClass);
    $titleParts = $breakTitleAfterColon && str_contains($title, ':')
        ? explode(':', $title, 2)
        : null;
@endphp

@once
    <style>
        @media (min-width: 1024px) {
            .edulaw-page-header-right,
            .edulaw-page-header-right .edulaw-page-header-description {
                text-align: right !important;
            }

            .edulaw-page-header-right .edulaw-page-header-description,
            .edulaw-page-header-right .edulaw-page-header-content {
                margin-left: auto !important;
            }

            .edulaw-page-header-right .edulaw-page-header-content {
                align-items: flex-end !important;
                text-align: right !important;
            }

            .edulaw-page-header-right .edulaw-page-header-content > .flex {
                justify-content: flex-end !important;
            }
        }
    </style>
@endonce

<section class="relative isolate overflow-hidden text-white" style="background-color: #06132a;">
    @if ($hasBackground)
        <img
            src="{{ $backgroundImage }}"
            alt="{{ $backgroundAlt ?: $title }}"
            class="absolute inset-0 z-0 h-full w-full object-cover object-center"
        >
        <div class="absolute inset-0 z-0" style="background-color: rgba(6, 19, 42, {{ $overlayOpacity }});"></div>
    @endif
    <div class="absolute bottom-0 left-0 right-0 z-0 h-px bg-white/12"></div>

    <div class="{{ $containerClass ?: $defaultContainerClass }}">
        <div class="min-w-0 justify-self-start text-left">
            @if (! empty($breadcrumbs))
                <nav @class([
                    'flex flex-wrap items-center',
                    'gap-1.5 text-[11px] font-medium text-white/55' => $channelHeader,
                    'gap-2 '.($compact ? 'text-xs' : 'text-sm').' font-bold '.($isDarkHero ? 'text-white/68' : 'text-slate-500') => ! $channelHeader,
                ]) aria-label="Breadcrumb">
                    @foreach ($breadcrumbs as $breadcrumb)
                        @php
                            $breadcrumbLabel = is_array($breadcrumb) ? ($breadcrumb['label'] ?? null) : null;
                            $breadcrumbUrl = is_array($breadcrumb) ? ($breadcrumb['url'] ?? null) : null;
                        @endphp

                        @if (! $breadcrumbLabel)
                            @continue
                        @endif

                        @if (! $loop->first)
                            <span class="{{ $channelHeader ? '' : ($isDarkHero ? 'text-white/42' : 'text-slate-300') }}" aria-hidden="true">/</span>
                        @endif

                        @if (! empty($breadcrumbUrl) && ! $loop->last)
                            <a href="{{ url($breadcrumbUrl) }}" class="{{ $channelHeader ? 'rounded-sm transition hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber' : 'transition '.($isDarkHero ? 'hover:text-white' : 'hover:text-brand-ink') }}">
                                {{ $breadcrumbLabel }}
                            </a>
                        @else
                            <span class="{{ $isDarkHero ? 'text-white' : '' }}">{{ $breadcrumbLabel }}</span>
                        @endif
                    @endforeach
                </nav>
            @endif

            @if ($eyebrow)
                @if ($channelHeader)
                    <p class="mt-2 text-[10px] font-bold uppercase tracking-[0.2em] text-brand-amber">{{ $eyebrow }}</p>
                @else
                    <p class="{{ $compact ? 'mt-2' : 'mt-6' }} edulaw-badge edulaw-badge-md {{ $isDarkHero ? 'edulaw-badge-dark' : 'edulaw-badge-navy' }}">
                        {{ $eyebrow }}
                    </p>
                @endif
            @endif

            <h1 class="{{ $eyebrow ? ($channelHeader ? 'mt-1' : 'mt-2') : (! empty($breadcrumbs) ? ($compact ? 'mt-4' : 'mt-7') : '') }} {{ $titleWidthClass }} font-black leading-[1.06] tracking-tight {{ $isDarkHero ? 'text-white' : 'text-brand-ink' }} {{ $titleClass ?: $defaultTitleClass }}">
                @if ($titleParts)
                    <span class="block">{{ trim($titleParts[0]) }}:</span>
                    <span class="block">{{ trim($titleParts[1]) }}</span>
                @else
                    {{ $title }}
                @endif
            </h1>

            @if ($channelHeader && $description)
                <p class="mt-1 max-w-2xl text-pretty text-sm leading-6 text-white/78">
                    {{ $description }}
                </p>
            @endif
        </div>

        @if ((! $channelHeader && $description) || ! $slot->isEmpty())
            <div class="edulaw-page-header-right min-w-0 lg:ml-auto lg:w-full lg:justify-self-end lg:text-right">
                @if (! $channelHeader && $description)
                    <p class="edulaw-page-header-description {{ $descriptionClass ?: 'max-w-[calc(100vw-2rem)] '.($compact ? 'text-sm leading-6' : 'text-base leading-8').' '.($isDarkHero ? 'text-white/84' : 'text-slate-600').' sm:max-w-2xl lg:ml-auto lg:text-right' }}">
                        {{ $description }}
                    </p>

                    @if ($accentLine)
                        <span class="mt-5 block h-1 w-14 rounded-full bg-brand-amber lg:ml-auto"></span>
                    @endif
                @endif

                @if (! $slot->isEmpty())
                    <div class="edulaw-page-header-content {{ ! $channelHeader && $description ? ($compact ? 'mt-4' : 'mt-7') : '' }} {{ $contentClass ?: 'lg:ml-auto lg:flex lg:max-w-2xl lg:flex-col lg:items-end lg:text-right' }}">
                        {{ $slot }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</section>
