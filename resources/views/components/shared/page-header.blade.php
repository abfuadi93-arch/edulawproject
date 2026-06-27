@props([
    'title' => '',
    'description' => null,
    'eyebrow' => null,
    'breadcrumbs' => [],
    'backgroundImage' => null,
    'backgroundAlt' => '',
    'compact' => false,
    'gridClass' => null,
    'titleClass' => null,
    'contentClass' => null,
])

@php
    $hasBackground = filled($backgroundImage);
    $defaultGridClass = $compact
        ? 'gap-5 px-4 py-7 sm:w-full sm:px-6 lg:min-h-[220px] lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:px-8 lg:py-10'
        : 'gap-8 px-4 py-11 sm:w-full sm:px-6 lg:min-h-[300px] lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:px-8 lg:py-16';
    $defaultTitleClass = $compact
        ? 'text-3xl sm:text-4xl lg:text-[2.65rem]'
        : 'text-4xl sm:text-5xl lg:text-[3.25rem]';
@endphp

<section class="{{ $hasBackground ? 'relative isolate overflow-hidden border-b border-slate-200/70 bg-brand-navy text-white' : 'border-b border-slate-200/70 bg-white' }}">
    @if ($hasBackground)
        <img
            src="{{ $backgroundImage }}"
            alt="{{ $backgroundAlt ?: $title }}"
            class="absolute inset-0 z-0 h-full w-full object-cover object-center"
        >
        <div class="absolute inset-0 z-0 bg-linear-to-r from-[#06132a]/86 via-brand-navy/68 via-48% to-brand-navy/28"></div>
        <div class="absolute inset-0 z-0 bg-linear-to-t from-[#06132a]/58 via-[#06132a]/12 to-[#06132a]/24"></div>
        <div class="absolute inset-y-0 left-0 z-0 hidden w-[50%] bg-[#06132a]/18 lg:block"></div>
        <div class="absolute bottom-0 left-0 right-0 z-0 h-px bg-white/12"></div>
    @endif

    <div class="relative z-10 mx-auto grid w-screen max-w-7xl {{ $gridClass ?: $defaultGridClass }}">
        <div class="min-w-0">
            @if (! empty($breadcrumbs))
                <nav class="flex flex-wrap items-center gap-2 text-sm font-bold {{ $hasBackground ? 'text-white/68' : 'text-slate-500' }}">
                    @foreach ($breadcrumbs as $breadcrumb)
                        @php
                            $breadcrumbLabel = is_array($breadcrumb) ? ($breadcrumb['label'] ?? null) : null;
                            $breadcrumbUrl = is_array($breadcrumb) ? ($breadcrumb['url'] ?? null) : null;
                        @endphp

                        @if (! $breadcrumbLabel)
                            @continue
                        @endif

                        @if (! $loop->first)
                            <span class="{{ $hasBackground ? 'text-white/42' : 'text-slate-300' }}">/</span>
                        @endif

                        @if (! empty($breadcrumbUrl) && ! $loop->last)
                            <a href="{{ url($breadcrumbUrl) }}" class="transition {{ $hasBackground ? 'hover:text-white' : 'hover:text-brand-ink' }}">
                                {{ $breadcrumbLabel }}
                            </a>
                        @else
                            <span class="{{ $hasBackground ? 'text-white' : '' }}">{{ $breadcrumbLabel }}</span>
                        @endif
                    @endforeach
                </nav>
            @endif

            @if ($eyebrow)
                <p class="{{ $compact ? 'mt-4' : 'mt-6' }} edulaw-badge edulaw-badge-md {{ $hasBackground ? 'edulaw-badge-dark' : 'edulaw-badge-navy' }}">
                    {{ $eyebrow }}
                </p>
            @endif

            <h1 class="{{ $eyebrow ? 'mt-2' : (! empty($breadcrumbs) ? ($compact ? 'mt-4' : 'mt-7') : '') }} max-w-4xl font-black leading-[1.06] tracking-tight {{ $hasBackground ? 'text-white' : 'text-brand-ink' }} {{ $titleClass ?: $defaultTitleClass }}">
                {{ $title }}
            </h1>
        </div>

        @if ($description || ! $slot->isEmpty())
            <div class="min-w-0 lg:ml-auto lg:w-full">
                @if ($description)
                    <p class="max-w-[calc(100vw-2rem)] {{ $compact ? 'text-sm leading-7' : 'text-base leading-8' }} {{ $hasBackground ? 'text-white/84' : 'text-slate-600' }} sm:max-w-2xl lg:ml-auto">
                        {{ $description }}
                    </p>
                @endif

                @if (! $slot->isEmpty())
                    <div class="{{ $description ? ($compact ? 'mt-5' : 'mt-7') : '' }} {{ $contentClass ?: 'lg:ml-auto lg:max-w-2xl' }}">
                        {{ $slot }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</section>
