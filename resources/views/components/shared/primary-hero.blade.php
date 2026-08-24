@props([
    'title',
    'description' => null,
    'eyebrow' => null,
    'breadcrumbs' => [],
    'backgroundImage' => null,
    'backgroundAlt' => '',
    'backgroundPosition' => 'center',
    'stats' => [],
    'panelLabel' => null,
])

@php
    $statItems = collect($stats)
        ->map(fn ($item): array => is_array($item) ? $item : [])
        ->filter(fn (array $item): bool => filled($item['label'] ?? null))
        ->take(4)
        ->values();
@endphp

<section class="relative isolate overflow-hidden bg-brand-navy text-white">
    @if ($backgroundImage)
        <img
            src="{{ $backgroundImage }}"
            alt="{{ $backgroundAlt ?: $title }}"
            class="absolute inset-0 -z-20 size-full object-cover opacity-30"
            style="object-position: {{ $backgroundPosition }};"
            fetchpriority="high"
            decoding="async"
        >
    @endif
    <div class="absolute inset-0 -z-10 bg-[linear-gradient(90deg,rgba(5,19,43,0.98)_0%,rgba(8,35,69,0.91)_58%,rgba(13,74,84,0.82)_100%)]"></div>

    <div class="mx-auto grid max-w-7xl gap-6 px-5 py-7 sm:px-6 sm:py-8 lg:min-h-[240px] lg:grid-cols-[minmax(0,7fr)_minmax(300px,3fr)] lg:items-center lg:gap-10 lg:px-8 lg:py-4">
        <div class="min-w-0">
            @if (! empty($breadcrumbs))
                <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-1.5 text-xs font-medium text-white/60">
                    @foreach ($breadcrumbs as $breadcrumb)
                        @php
                            $breadcrumbLabel = is_array($breadcrumb) ? ($breadcrumb['label'] ?? null) : null;
                            $breadcrumbUrl = is_array($breadcrumb) ? ($breadcrumb['url'] ?? null) : null;
                        @endphp

                        @if (! $breadcrumbLabel)
                            @continue
                        @endif

                        @if (! $loop->first)
                            <span aria-hidden="true">/</span>
                        @endif

                        @if ($breadcrumbUrl && ! $loop->last)
                            <a href="{{ url($breadcrumbUrl) }}" class="rounded-sm transition hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">
                                {{ $breadcrumbLabel }}
                            </a>
                        @else
                            <span class="text-white">{{ $breadcrumbLabel }}</span>
                        @endif
                    @endforeach
                </nav>
            @endif

            @if ($eyebrow)
                <p class="mt-2 text-[11px] font-extrabold uppercase tracking-[0.18em] text-brand-amber">{{ $eyebrow }}</p>
            @endif

            <h1 class="mt-1.5 max-w-4xl text-balance font-display text-3xl font-black leading-tight text-white sm:text-4xl lg:line-clamp-2">{{ $title }}</h1>

            @if ($description)
                <p class="mt-3 max-w-4xl text-pretty text-base font-bold leading-6 text-white/90 sm:text-lg sm:leading-7 lg:line-clamp-2">{{ $description }}</p>
            @endif

        </div>

        @if ($statItems->isNotEmpty())
            <dl class="grid grid-cols-2 overflow-hidden rounded-[14px] border border-white/15 bg-white/10 backdrop-blur-sm" @if ($panelLabel) aria-label="{{ $panelLabel }}" @endif>
                @foreach ($statItems as $stat)
                    <div @class([
                        'min-w-0 border-white/15 p-3',
                        'border-l' => $loop->iteration % 2 === 0,
                        'border-t' => $loop->iteration > 2,
                        'col-span-2' => $statItems->count() === 3 && $loop->last,
                    ])>
                        <dd class="font-display text-2xl font-black tabular-nums text-brand-amber">{{ $stat['value'] ?? '0' }}</dd>
                        <dt class="mt-1 text-[10px] font-extrabold uppercase leading-4 tracking-[0.1em] text-white/70">{{ $stat['label'] }}</dt>
                    </div>
                @endforeach
            </dl>
        @elseif (! $slot->isEmpty())
            <div class="min-w-0 overflow-hidden rounded-[14px] border border-white/15 bg-white/10 p-2 backdrop-blur-sm" @if ($panelLabel) aria-label="{{ $panelLabel }}" @endif>
                {{ $slot }}
            </div>
        @endif
    </div>
</section>
