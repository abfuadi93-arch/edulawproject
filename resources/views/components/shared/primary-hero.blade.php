@props([
    'title',
    'description' => null,
    'eyebrow' => null,
    'breadcrumbs' => [],
    'backgroundImage' => null,
    'backgroundAlt' => '',
    'backgroundPosition' => 'center',
    'highlights' => [],
    'stats' => [],
    'panelLabel' => null,
])

@php
    $highlightItems = collect($highlights)
        ->map(fn ($item): array => is_array($item) ? $item : ['label' => $item])
        ->filter(fn (array $item): bool => filled($item['label'] ?? null))
        ->take(3)
        ->values();
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

    <div class="mx-auto grid max-w-7xl gap-6 px-5 py-7 sm:px-6 sm:py-8 lg:min-h-[300px] lg:grid-cols-[minmax(0,7fr)_minmax(300px,3fr)] lg:items-center lg:gap-10 lg:px-8 lg:py-5">
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

            @if ($highlightItems->isNotEmpty())
                <div class="mt-4 grid divide-y divide-white/15 overflow-hidden rounded-xl border border-white/15 bg-white/5 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                    @foreach ($highlightItems as $highlight)
                        @php($icon = $highlight['icon'] ?? ['book', 'research', 'move'][$loop->index])
                        <div class="flex min-w-0 items-center gap-2 p-2.5 sm:h-full">
                            <span class="grid size-7 shrink-0 place-items-center rounded-lg border border-brand-amber/35 text-brand-amber">
                                @if ($icon === 'book')
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z" stroke="currentColor" stroke-width="1.7"/></svg>
                                @elseif ($icon === 'research')
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m20 20-4.2-4.2M10.8 17a6.2 6.2 0 1 1 0-12.4 6.2 6.2 0 0 1 0 12.4Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                                @else
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 18 18 5m0 0h-7m7 0v7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                @endif
                            </span>
                            <p class="text-xs font-extrabold leading-4 text-white/90 lg:line-clamp-2">{{ $highlight['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($statItems->isNotEmpty())
            <dl class="grid grid-cols-2 overflow-hidden rounded-[14px] border border-white/15 bg-white/10 backdrop-blur-sm" @if ($panelLabel) aria-label="{{ $panelLabel }}" @endif>
                @foreach ($statItems as $stat)
                    <div @class([
                        'min-w-0 border-white/15 p-4',
                        'border-l' => $loop->iteration % 2 === 0,
                        'border-t' => $loop->iteration > 2,
                        'col-span-2' => $statItems->count() === 3 && $loop->last,
                    ])>
                        <dd class="font-display text-3xl font-black tabular-nums text-brand-amber">{{ $stat['value'] ?? '0' }}</dd>
                        <dt class="mt-1.5 text-[10px] font-extrabold uppercase leading-4 tracking-[0.1em] text-white/70">{{ $stat['label'] }}</dt>
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
