@props([
    'platform',
    'title',
    'description',
    'url' => null,
    'linkLabel' => null,
])

<div {{ $attributes->class('flex items-start gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6') }}>
    <div class="min-w-0 flex-1">
        <x-multimedia.platform-badge :platform="$platform" />

        <h3 class="mt-3 text-base font-black text-brand-ink">{{ $title }}</h3>
        <p class="mt-1 text-sm leading-6 text-slate-600">{{ $description }}</p>

        @if ($url && $linkLabel)
            <a
                href="{{ $url }}"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="{{ $linkLabel }} (membuka tab baru)"
                class="mt-3 inline-flex items-center gap-1.5 text-sm font-black text-brand-navy transition hover:text-brand-coral focus-visible:rounded focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy"
            >
                {{ $linkLabel }}
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        @endif
    </div>
</div>
