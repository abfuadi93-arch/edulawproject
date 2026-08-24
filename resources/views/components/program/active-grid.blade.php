@props([
    'programs' => collect(),
    'total' => null,
    'search' => '',
    'selectedSort' => 'terdekat',
    'selectedView' => 'grid',
    'archiveUrl' => null,
    'opportunitiesUrl' => null,
])

@php
    $isPaginator = is_object($programs) && method_exists($programs, 'getCollection');
    $total = $total ?? ($isPaginator ? $programs->total() : collect($programs)->count());
    $items = $isPaginator ? $programs->getCollection() : collect($programs);
    $archiveUrl = $archiveUrl ?? route('programs.archive');
    $opportunitiesUrl = $opportunitiesUrl ?? route('opportunities.index');
@endphp

<section class="min-w-0" aria-labelledby="program-active-title">
    <x-program.toolbar
        :total="$total"
        :search="$search"
        :selected-sort="$selectedSort"
        :selected-view="$selectedView"
    />

    @if ($items->isNotEmpty())
        <div @class([
            'grid gap-5',
            'md:grid-cols-1' => $items->count() === 1 || $selectedView === 'list',
            'sm:grid-cols-2 lg:grid-cols-4' => $items->count() > 1 && $selectedView !== 'list',
        ])>
            @foreach ($items as $program)
                <x-program.program-card :program="$program" />
            @endforeach
        </div>
    @else
        <div class="py-8 text-center sm:py-9" data-program-empty-state>
            <span class="mx-auto grid size-14 place-items-center rounded-full bg-[#eaf2ff] text-brand-navy" aria-hidden="true">
                <svg class="size-7" viewBox="0 0 24 24" fill="none">
                    <path d="M8 2v3m8-3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v13H4V6a1 1 0 0 1 1-1Zm10 10 4 4m0-4-4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <h2 class="mt-4 text-lg font-black tracking-normal text-brand-navy">Belum ada program aktif saat ini.</h2>
            <p class="mx-auto mt-1 max-w-2xl text-sm leading-6 text-slate-500">Jelajahi dokumentasi program terdahulu atau temukan peluang lain dari Edulaw.</p>
            <div class="mt-5 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ $archiveUrl }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-navy px-5 text-sm font-black text-white transition hover:bg-[#102B4B]">Lihat Program Arsip →</a>
                <a href="{{ $opportunitiesUrl }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-brand-navy/20 bg-white px-5 text-sm font-black text-brand-navy transition hover:border-brand-navy">Lihat Opportunities →</a>
            </div>
        </div>
    @endif
</section>
