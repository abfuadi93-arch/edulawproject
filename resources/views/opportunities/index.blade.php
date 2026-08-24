@extends('layouts.app')

@section('title', 'Opportunities Hukum dan Pengembangan | Edulaw Project')
@section('meta_description', 'Temukan beasiswa, magang, fellowship, kompetisi, call for papers, dan peluang pengembangan hukum yang masih terbuka melalui Edulaw Project.')
@section('canonical_url', route('opportunities.index'))

@push('head')
    @php
        $schemaItems = collect([$featuredOpportunity])
            ->merge($opportunities->items())
            ->filter()
            ->unique('id')
            ->map(fn ($item): array => [
                'name' => $item->title,
                'url' => route('opportunities.show', $item->slug),
                'image' => $item->poster_url,
            ])
            ->values()
            ->all();
    @endphp
    @if ($schemaItems !== [])
        <x-structured-data :data="\App\Support\StructuredData::itemList($schemaItems, 'Opportunities Hukum dan Pengembangan')" />
    @endif
@endpush

@section('content')
@php
    $typeLabels = [
        'scholarship' => 'Beasiswa',
        'internship' => 'Magang',
        'competition' => 'Kompetisi',
        'call_for_paper' => 'Call for Papers',
        'fellowship' => 'Fellowship',
        'career' => 'Karier',
        'open_collaboration' => 'Kolaborasi',
        'volunteer' => 'Volunteer',
    ];
    $deadlineLabels = [
        '7_days' => '7 hari ke depan',
        '30_days' => '30 hari ke depan',
        'month' => 'Bulan ini',
        'all' => 'Semua deadline',
    ];
    $formatLabels = ['online' => 'Online', 'offline' => 'Offline', 'hybrid' => 'Hybrid'];
    $sortLabels = [
        'deadline' => 'Deadline Terdekat',
        'deadline_desc' => 'Deadline Terjauh',
        'latest' => 'Terbaru',
    ];
    $indexUrl = route('opportunities.index');
    $selectedView = request('view') === 'list' ? 'list' : 'grid';
    $queryFor = function (array $changes = [], array $remove = []) use ($indexUrl): string {
        $parameters = request()->except(array_merge(['page'], $remove));

        foreach ($changes as $key => $value) {
            if ($value === null || $value === '') {
                unset($parameters[$key]);
            } else {
                $parameters[$key] = $value;
            }
        }

        return $parameters === [] ? $indexUrl : route('opportunities.index', $parameters);
    };
    $hasAdvancedFilters = $filters['status'] === 'closed'
        || filled($filters['deadline'])
        || filled($filters['location']);
    $hasActiveFilters = filled($filters['q'])
        || filled($filters['type'])
        || filled($filters['format'])
        || $hasAdvancedFilters;
    $activeFilterCount = collect([
        $filters['status'] === 'closed' ? $filters['status'] : null,
        $filters['deadline'],
        $filters['location'],
    ])->filter()->count();
    $resultsTitle = $filters['status'] === 'closed'
        ? 'Peluang yang Sudah Ditutup'
        : 'Peluang yang Masih Dibuka';
@endphp

<main class="overflow-x-clip bg-[#f7f8fa] text-brand-ink">
    <x-shared.primary-hero
        title="Opportunities"
        eyebrow="Kanal Opportunities"
        description="Temukan beasiswa, magang, fellowship, kompetisi, call for papers, dan peluang pengembangan di bidang hukum."
        background-image="https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=1800&q=85"
        background-alt="Kolaborasi dan pengembangan kapasitas melalui Opportunities Edulaw"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Opportunities'],
        ]"
        :stats="collect([
            ['value' => number_format($statistics['total']), 'label' => 'Total Peluang'],
            ['value' => number_format($statistics['open']), 'label' => 'Masih Dibuka'],
            $statistics['nearest_deadline'] ? ['value' => $statistics['nearest_deadline'], 'label' => 'Deadline Terdekat'] : null,
        ])->filter()->values()->all()"
        panel-label="Statistik Opportunities"
    />

    @if ($featuredOpportunity)
        <section class="bg-white py-9 sm:py-10 lg:py-11" aria-labelledby="featured-opportunity-title">
            <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
                <p class="mb-4 text-[11px] font-black uppercase tracking-[0.15em] text-brand-navy"><span class="mr-1 text-brand-amber" aria-hidden="true">★</span> Pilihan Edulaw</p>
                <x-opportunities.featured-card :opportunity="$featuredOpportunity" />
            </div>
        </section>
    @endif

    <section id="opportunity-finder" class="py-9 sm:py-10 lg:py-11" aria-labelledby="opportunity-results-title" data-opportunity-filters>
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.15em] text-brand-navy">Opportunity Finder</p>
                    <h2 id="opportunity-results-title" class="mt-1 font-display text-2xl font-black text-brand-navy sm:text-3xl">{{ $resultsTitle }}</h2>
                    <p class="mt-1.5 max-w-3xl text-base leading-7 text-slate-600">Cari peluang berdasarkan kata kunci, kategori, format, lokasi, dan deadline.</p>
                </div>
                <p class="text-sm font-bold text-slate-500"><strong class="text-brand-navy">{{ number_format($opportunities->total()) }}</strong> kesempatan ditemukan</p>
            </div>

            <form method="GET" action="{{ $indexUrl }}#opportunity-finder" class="mt-5 rounded-[14px] bg-white p-3">
                <input type="hidden" name="view" value="{{ $selectedView }}">
                @if ($filters['type'])
                    <input type="hidden" name="type" value="{{ $filters['type'] }}">
                @endif

                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-[minmax(240px,1fr)_170px_180px_auto_auto_auto]">
                    <label class="relative block min-w-0 sm:col-span-2 lg:col-span-1">
                        <span class="sr-only">Cari opportunities</span>
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m21 21-4.3-4.3M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Cari beasiswa, magang, kompetisi..." class="h-11 w-full rounded-lg border border-slate-200 bg-[#f8fafc] pl-10 pr-3 text-sm font-medium text-brand-ink outline-none placeholder:text-slate-400 focus:border-brand-navy focus:bg-white focus:ring-2 focus:ring-brand-navy/10">
                    </label>

                    <label>
                        <span class="sr-only">Format opportunities</span>
                        <select name="format" class="h-11 w-full rounded-lg border border-slate-200 bg-[#f8fafc] px-3 text-sm font-bold text-brand-navy outline-none focus:border-brand-navy focus:ring-2 focus:ring-brand-navy/10">
                            <option value="">Semua Format</option>
                            @foreach ($availableFormats as $format)
                                <option value="{{ $format }}" @selected($filters['format'] === $format)>{{ $formatLabels[$format] ?? Illuminate\Support\Str::headline($format) }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span class="sr-only">Urutkan opportunities</span>
                        <select name="sort" class="h-11 w-full rounded-lg border border-slate-200 bg-[#f8fafc] px-3 text-sm font-bold text-brand-navy outline-none focus:border-brand-navy focus:ring-2 focus:ring-brand-navy/10">
                            @foreach ($sortLabels as $value => $label)
                                <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <button type="button" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-[#f8fafc] px-4 text-sm font-black text-brand-navy" data-opportunity-filters-toggle aria-expanded="{{ $hasAdvancedFilters ? 'true' : 'false' }}" aria-controls="opportunity-advanced-filters">
                        Filter Lanjutan
                        @if ($activeFilterCount > 0)
                            <span class="grid h-5 min-w-5 place-items-center rounded-full bg-brand-navy px-1 text-[11px] text-white">{{ $activeFilterCount }}</span>
                        @endif
                    </button>

                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-navy px-5 text-sm font-black text-white transition hover:bg-brand-ink">Terapkan</button>

                    <div class="flex h-11 items-center rounded-lg border border-slate-200 bg-[#f8fafc] p-1" aria-label="Pilihan tampilan opportunities">
                        <a href="{{ $queryFor(['view' => 'grid']) }}#opportunity-finder" aria-label="Tampilan grid" aria-current="{{ $selectedView === 'grid' ? 'true' : 'false' }}" class="grid h-9 w-9 place-items-center rounded-md {{ $selectedView === 'grid' ? 'bg-brand-navy text-white' : 'text-slate-500 hover:text-brand-navy' }}">
                            <svg viewBox="0 0 20 20" class="h-4 w-4" fill="currentColor" aria-hidden="true"><path d="M2.5 2.5h6v6h-6v-6Zm9 0h6v6h-6v-6Zm-9 9h6v6h-6v-6Zm9 0h6v6h-6v-6Z"/></svg>
                        </a>
                        <a href="{{ $queryFor(['view' => 'list']) }}#opportunity-finder" aria-label="Tampilan daftar" aria-current="{{ $selectedView === 'list' ? 'true' : 'false' }}" class="grid h-9 w-9 place-items-center rounded-md {{ $selectedView === 'list' ? 'bg-brand-navy text-white' : 'text-slate-500 hover:text-brand-navy' }}">
                            <svg viewBox="0 0 20 20" class="h-4 w-4" fill="currentColor" aria-hidden="true"><path d="M2.5 3.5h3v3h-3v-3Zm5 0h10v3h-10v-3Zm-5 5h3v3h-3v-3Zm5 0h10v3h-10v-3Zm-5 5h3v3h-3v-3Zm5 0h10v3h-10v-3Z"/></svg>
                        </a>
                    </div>
                </div>

                <div id="opportunity-advanced-filters" data-opportunity-filters-panel @if (! $hasAdvancedFilters) hidden @endif class="mt-3 border-t border-slate-100 pt-3">
                    <div class="grid gap-3 sm:grid-cols-3">
                        <label>
                            <span class="text-[11px] font-black uppercase tracking-[0.11em] text-slate-500">Status</span>
                            <select name="status" class="mt-2 h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm font-bold text-brand-ink outline-none focus:border-brand-navy">
                                <option value="open" @selected($filters['status'] === 'open')>Masih Dibuka</option>
                                <option value="closed" @selected($filters['status'] === 'closed')>Sudah Ditutup</option>
                            </select>
                        </label>
                        <label>
                            <span class="text-[11px] font-black uppercase tracking-[0.11em] text-slate-500">Deadline</span>
                            <select name="deadline" class="mt-2 h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm font-bold text-brand-ink outline-none focus:border-brand-navy">
                                <option value="">Semua deadline</option>
                                @foreach ($deadlineLabels as $value => $label)
                                    <option value="{{ $value }}" @selected($filters['deadline'] === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span class="text-[11px] font-black uppercase tracking-[0.11em] text-slate-500">Lokasi</span>
                            <select name="location" class="mt-2 h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm font-bold text-brand-ink outline-none focus:border-brand-navy">
                                <option value="">Semua lokasi</option>
                                @foreach ($availableLocations as $location)
                                    <option value="{{ $location }}" @selected($filters['location'] === $location)>{{ Illuminate\Support\Str::limit($location, 48) }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <div class="mt-3 flex justify-end">
                        <a href="{{ $indexUrl }}#opportunity-finder" class="text-sm font-black text-brand-navy underline decoration-brand-amber decoration-2 underline-offset-4">Hapus semua filter</a>
                    </div>
                </div>
            </form>

            <nav class="mt-2 flex gap-2 overflow-x-auto pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" aria-label="Jenis opportunities">
                <a href="{{ $queryFor([], ['type']) }}#opportunity-finder" class="inline-flex min-h-8 shrink-0 items-center rounded-full px-3 text-xs font-bold {{ blank($filters['type']) ? 'bg-brand-navy text-white' : 'bg-white text-brand-navy' }}">Semua</a>
                @foreach ($availableTypes as $type)
                    <a href="{{ $queryFor(['type' => $type]) }}#opportunity-finder" class="inline-flex min-h-8 shrink-0 items-center rounded-full px-3 text-xs font-bold {{ $filters['type'] === $type ? 'bg-brand-navy text-white' : 'bg-white text-brand-navy' }}">{{ $typeLabels[$type] ?? Illuminate\Support\Str::headline($type) }}</a>
                @endforeach
            </nav>

            <div class="mt-7 flex flex-wrap items-center justify-between gap-3 text-sm text-slate-500">
                <p>Diurutkan berdasarkan <strong class="text-brand-navy">{{ Illuminate\Support\Str::lower($sortLabels[$filters['sort']] ?? 'Deadline Terdekat') }}</strong></p>
                @if ($opportunities->lastPage() > 1)
                    <p>Halaman {{ $opportunities->currentPage() }} dari {{ $opportunities->lastPage() }}</p>
                @endif
            </div>

            @if ($opportunities->isNotEmpty())
                <div class="mt-4 grid grid-cols-1 gap-5 {{ $selectedView === 'grid' ? 'sm:grid-cols-2 lg:grid-cols-3' : '' }}">
                    @foreach ($opportunities as $opportunity)
                        <x-opportunities.card :opportunity="$opportunity" :view="$selectedView" />
                    @endforeach
                </div>

                <div class="mt-8 flex flex-col gap-5 border-t border-slate-200 pt-6 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                    <p class="font-bold">Menampilkan {{ $opportunities->firstItem() }}–{{ $opportunities->lastItem() }} dari {{ $opportunities->total() }} peluang</p>
                </div>
                <x-shared.pagination :paginator="$opportunities" fragment="opportunity-finder" label="Navigasi halaman opportunities" />
            @else
                <div class="mt-6 rounded-[14px] border border-dashed border-slate-300 bg-white px-6 py-10 text-center">
                    <h3 class="font-display text-xl font-black text-brand-navy">Belum ada peluang yang sesuai</h3>
                    <p class="mx-auto mt-2 max-w-lg text-base leading-7 text-slate-600">{{ $hasActiveFilters ? 'Coba ubah kata kunci atau filter untuk memperluas hasil pencarian.' : 'Belum ada opportunity yang tersedia untuk daftar ini saat ini.' }}</p>
                    @if ($hasActiveFilters)
                        <a href="{{ $indexUrl }}#opportunity-finder" class="mt-4 inline-flex min-h-11 items-center rounded-lg bg-brand-navy px-5 text-sm font-black text-white">Hapus Filter</a>
                    @endif
                </div>
            @endif
        </div>
    </section>

    <section class="bg-white py-8 sm:py-9" aria-labelledby="opportunity-curation-heading">
        <div class="section-shell">
            <article class="rounded-[14px] bg-[#f7f8fa] p-5 sm:grid sm:grid-cols-[0.42fr_0.58fr] sm:items-center sm:gap-6 sm:p-6">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.15em] text-brand-teal">Kurasi Edulaw</p>
                    <h2 id="opportunity-curation-heading" class="mt-2 font-display text-2xl font-black text-brand-navy">Informasi peluang yang lebih mudah dipindai.</h2>
                </div>
                <p class="mt-3 text-base leading-7 text-slate-600 sm:mt-0">Setiap peluang diringkas melalui kategori, format, lokasi, dan deadline agar pembaca dapat mengambil keputusan dengan cepat.</p>
            </article>
        </div>
    </section>

    <x-shared.cta-section
        heading-id="opportunity-contribution-heading"
        eyebrow="Bagikan Peluang"
        title="Punya informasi peluang yang relevan?"
        body="Organisasi, kampus, komunitas, dan mitra dapat mengirimkan informasi beasiswa, kompetisi, karier, magang, fellowship, atau call for papers."
        :primary-url="route('collaboration.index')"
        primary-label="Kirim Informasi Peluang"
        :secondary-url="route('contact.index')"
        secondary-label="Hubungi Edulaw"
    />
</main>
@endsection
