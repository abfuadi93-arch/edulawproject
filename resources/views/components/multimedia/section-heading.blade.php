@props([
    'platform',
    'eyebrow',
    'title',
    'description',
])

<div {{ $attributes->class('max-w-3xl') }}>
    <div class="flex items-center gap-2">
        <x-multimedia.platform-badge :platform="$platform" :label="$eyebrow" />
    </div>

    <h2 class="mt-2 text-2xl font-black tracking-tight text-brand-ink sm:text-3xl">
        {{ $title }}
    </h2>

    <p class="mt-2 text-base leading-7 text-slate-600">
        {{ $description }}
    </p>
</div>
