@props([
    'channels' => collect(),
])

@php
    $channels = collect($channels);
@endphp

<section class="bg-white py-20 lg:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
            <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                Jelajahi Kanal Insight
            </p>

                <h2 class="mt-2 text-[2.25rem] font-black leading-tight text-brand-ink sm:text-[2.5rem]">
                    Pilih kanal dan baca perkembangan terbarunya.
                </h2>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                Jelajahi artikel terbaru dari setiap kanal sesuai minat Anda.
            </p>
        </div>

            <a href="{{ route('insights.index', ['archive' => 'latest']) }}#insight-archive" class="inline-flex min-h-11 items-center gap-2 text-sm font-black text-brand-navy transition hover:text-brand-ink">
                Semua kanal
                <span>→</span>
            </a>
        </div>

        <div class="insight-control-scroll -mx-4 flex snap-x gap-5 overflow-x-auto px-4 pb-4 sm:mx-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 sm:pb-0 xl:grid-cols-4">
            @foreach ($channels as $channel)
                <x-insight.category-card :channel="$channel" />
            @endforeach
        </div>
    </div>
</section>
