@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'align' => 'left',
])

<div class="{{ $align === 'center' ? 'mx-auto max-w-3xl text-center' : 'max-w-3xl' }}">
    @if ($eyebrow)
        <p class="mb-3 text-sm font-semibold uppercase tracking-[0.2em] text-blue-700">
            {{ $eyebrow }}
        </p>
    @endif

    <h2 class="text-3xl font-bold tracking-tight text-brand-ink md:text-4xl">
        {{ $title }}
    </h2>

    @if ($description)
        <p class="mt-4 text-base leading-7 text-slate-600 md:text-lg">
            {{ $description }}
        </p>
    @endif
</div>