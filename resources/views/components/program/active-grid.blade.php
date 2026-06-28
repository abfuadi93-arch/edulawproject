@props([
    'programs' => collect(),
    'total' => null,
    'search' => '',
    'selectedSort' => 'terdekat',
    'selectedView' => 'grid',
])

@php
    $total = $total ?? ($programs instanceof \Illuminate\Pagination\AbstractPaginator ? $programs->total() : collect($programs)->count());
    $items = $programs instanceof \Illuminate\Pagination\AbstractPaginator ? $programs->getCollection() : collect($programs);
@endphp

<section class="min-w-0">
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
            'md:grid-cols-2' => $items->count() > 1 && $selectedView !== 'list',
        ])>
            @foreach ($items as $program)
                <x-program.program-card :program="$program" />
            @endforeach
        </div>
    @else
        <div class="rounded-[24px] border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
            <p class="text-sm font-black uppercase tracking-[0.18em] text-brand-navy">Belum ada program</p>
            <p class="mt-2 text-sm leading-6 text-slate-600">Coba ubah filter untuk melihat program lain yang tersedia.</p>
        </div>
    @endif

    <x-program.cta-program />
</section>
