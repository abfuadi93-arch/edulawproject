@props([
    'channels' => collect(),
])

@php
    $preferredChannels = collect([
        'Regulatory Update',
        'Edulaw Insight',
        'Legal 101',
        'Law & Governance',
    ]);

    $channels = collect($channels)
        ->filter(fn (array $channel): bool => $preferredChannels->contains($channel['label'] ?? ''))
        ->sortBy(fn (array $channel): int => $preferredChannels->search($channel['label'] ?? '') ?: 0)
        ->values();
@endphp

<section class="bg-white py-16">
    <div class="mx-auto max-w-[1400px] px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-3xl font-black leading-tight text-brand-ink">
                    Jelajahi Kanal Editorial
                </h2>
            </div>

            <a href="{{ route('insights.index', ['archive' => 'latest']) }}#insight-archive" class="group inline-flex min-h-10 items-center gap-2 text-sm font-bold text-brand-navy underline-offset-4 transition hover:underline">
                Lihat semua
                <svg class="h-4 w-4 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            @foreach ($channels as $channel)
                <x-insight.category-card :channel="$channel" />
            @endforeach
        </div>
    </div>
</section>
