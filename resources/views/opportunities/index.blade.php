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
                'url' => $item->application_link,
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
        || filled($filters['format'])
        || filled($filters['deadline'])
        || filled($filters['location']);
    $hasActiveFilters = filled($filters['q'])
        || filled($filters['type'])
        || $hasAdvancedFilters;
    $activeFilterCount = collect([
        $filters['status'] === 'closed' ? $filters['status'] : null,
        $filters['format'],
        $filters['deadline'],
        $filters['location'],
    ])->filter()->count();
    $resultsTitle = $filters['status'] === 'closed'
        ? 'Peluang yang Sudah Ditutup'
        : 'Peluang yang Masih Dibuka';
@endphp

<main class="bg-[#f7f8fa] text-brand-ink">
    <section class="relative overflow-hidden border-b border-[#e3e7ec] bg-linear-to-br from-white via-[#fbfaf6] to-[#f1f4f7] lg:min-h-[240px]" aria-labelledby="opportunities-title">
        <div class="pointer-events-none absolute -right-16 -top-24 h-72 w-72 rounded-full border border-brand-amber/15" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-6 -top-14 h-48 w-48 rounded-full border border-brand-navy/8" aria-hidden="true"></div>

        <div class="relative mx-auto grid max-w-7xl gap-8 px-4 py-9 sm:px-6 sm:py-11 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end lg:px-8 lg:py-12">
            <div class="min-w-0">
                <nav class="flex items-center gap-2 text-xs font-bold text-slate-500" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}" class="transition hover:text-brand-navy focus-visible:rounded focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-navy">Beranda</a>
                    <span class="text-slate-300" aria-hidden="true">/</span>
                    <span class="text-brand-navy">Opportunities</span>
                </nav>

                <p class="mt-5 text-[10px] font-black uppercase tracking-[0.2em] text-[#a8660a]">Kanal Opportunities</p>
                <h1 id="opportunities-title" class="mt-2 text-4xl font-black leading-none tracking-[-0.035em] text-brand-ink sm:text-5xl">Opportunities</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Temukan beasiswa, magang, fellowship, kompetisi, call for papers, dan peluang pengembangan di bidang hukum.
                </p>
            </div>

            <dl class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:min-w-[28rem]" aria-label="Statistik Opportunities">
                <div class="rounded-2xl border border-white bg-white/90 px-4 py-3 shadow-sm shadow-slate-900/5">
                    <dd class="text-2xl font-black text-brand-navy">{{ number_format($statistics['total']) }}</dd>
                    <dt class="mt-0.5 text-[10px] font-black uppercase tracking-[0.12em] text-slate-500">Peluang</dt>
                </div>
                <div class="rounded-2xl border border-white bg-white/90 px-4 py-3 shadow-sm shadow-slate-900/5">
                    <dd class="text-2xl font-black text-brand-navy">{{ number_format($statistics['open']) }}</dd>
                    <dt class="mt-0.5 text-[10px] font-black uppercase tracking-[0.12em] text-slate-500">Masih Dibuka</dt>
                </div>
                @if ($statistics['nearest_deadline'])
                    <div class="col-span-2 rounded-2xl border border-white bg-white/90 px-4 py-3 shadow-sm shadow-slate-900/5 sm:col-span-1">
                        <dd class="text-xl font-black text-[#9a610c]">{{ $statistics['nearest_deadline'] }}</dd>
                        <dt class="mt-0.5 text-[10px] font-black uppercase tracking-[0.12em] text-slate-500">Deadline Terdekat</dt>
                    </div>
                @endif
            </dl>
        </div>
    </section>

    <section class="border-b border-slate-200/80 bg-[#f7f8fa]" x-data="{ filtersOpen: {{ $hasAdvancedFilters ? 'true' : 'false' }} }" aria-label="Pencarian dan filter opportunities">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <form method="GET" action="{{ $indexUrl }}" class="rounded-[1.25rem] border border-slate-200 bg-white p-3 shadow-[0_14px_40px_-34px_rgba(15,23,42,.55)] sm:p-4">
                @if ($filters['type'])
                    <input type="hidden" name="type" value="{{ $filters['type'] }}">
                @endif

                <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_auto_auto_auto]">
                    <label class="relative block min-w-0">
                        <span class="sr-only">Cari opportunities</span>
                        <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m21 21-4.35-4.35M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Cari beasiswa, magang, kompetisi, call for papers..." class="h-12 w-full rounded-xl border border-slate-200 bg-[#f8fafc] pl-12 pr-4 text-sm font-bold text-brand-ink outline-none transition placeholder:font-medium placeholder:text-slate-400 focus:border-brand-navy focus:bg-white focus:ring-4 focus:ring-brand-navy/10">
                    </label>

                    <button type="button" class="inline-flex h-12 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-black text-brand-navy transition hover:border-brand-navy/40 hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy" @click="filtersOpen = !filtersOpen" :aria-expanded="filtersOpen.toString()" aria-controls="opportunity-advanced-filters">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 6h16M7 12h10M10 18h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        Filter
                        @if ($activeFilterCount > 0)
                            <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-brand-navy px-1 text-[10px] text-white">{{ $activeFilterCount }}</span>
                        @endif
                    </button>

                    <label class="relative block">
                        <span class="sr-only">Urutkan opportunities</span>
                        <select name="sort" class="h-12 w-full appearance-none rounded-xl border border-slate-200 bg-white pl-4 pr-10 text-sm font-black text-brand-ink outline-none transition focus:border-brand-navy focus:ring-4 focus:ring-brand-navy/10 lg:w-52">
                            @foreach ($sortLabels as $value => $label)
                                <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m7 10 5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </label>

                    <button type="submit" class="h-12 rounded-xl bg-brand-navy px-6 text-sm font-black text-white transition hover:bg-brand-ink focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy">Temukan</button>
                </div>

                <div id="opportunity-advanced-filters" x-cloak x-show="filtersOpen" x-transition.opacity.duration.150ms class="mt-4 border-t border-slate-100 pt-4">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <label>
                            <span class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">Status</span>
                            <select name="status" class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-brand-ink outline-none focus:border-brand-navy focus:ring-4 focus:ring-brand-navy/10">
                                <option value="open" @selected($filters['status'] === 'open')>Masih Dibuka</option>
                                <option value="closed" @selected($filters['status'] === 'closed')>Sudah Ditutup</option>
                            </select>
                        </label>

                        @if ($availableFormats->isNotEmpty())
                            <label>
                                <span class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">Format</span>
                                <select name="format" class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-brand-ink outline-none focus:border-brand-navy focus:ring-4 focus:ring-brand-navy/10">
                                    <option value="">Semua format</option>
                                    @foreach ($availableFormats as $format)
                                        <option value="{{ $format }}" @selected($filters['format'] === $format)>{{ $formatLabels[$format] ?? Illuminate\Support\Str::headline($format) }}</option>
                                    @endforeach
                                </select>
                            </label>
                        @endif

                        <label>
                            <span class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">Deadline</span>
                            <select name="deadline" class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-brand-ink outline-none focus:border-brand-navy focus:ring-4 focus:ring-brand-navy/10">
                                <option value="">Semua deadline</option>
                                @foreach ($deadlineLabels as $value => $label)
                                    <option value="{{ $value }}" @selected($filters['deadline'] === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        @if ($availableLocations->isNotEmpty())
                            <label>
                                <span class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">Lokasi</span>
                                <select name="location" class="mt-2 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-brand-ink outline-none focus:border-brand-navy focus:ring-4 focus:ring-brand-navy/10">
                                    <option value="">Semua lokasi</option>
                                    @foreach ($availableLocations as $location)
                                        <option value="{{ $location }}" @selected($filters['location'] === $location)>{{ Illuminate\Support\Str::limit($location, 48) }}</option>
                                    @endforeach
                                </select>
                            </label>
                        @endif
                    </div>

                    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs leading-5 text-slate-500">Filter diterapkan melalui URL agar hasil dapat dibagikan.</p>
                        <a href="{{ $indexUrl }}" class="text-sm font-black text-brand-navy underline-offset-4 hover:underline focus-visible:rounded focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-navy">Hapus semua filter</a>
                    </div>
                </div>
            </form>

            <nav class="mt-4 flex gap-2 overflow-x-auto pb-1" aria-label="Filter cepat berdasarkan jenis peluang">
                <a href="{{ $queryFor([], ['type']) }}" class="shrink-0 rounded-full border px-4 py-2 text-xs font-black transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy {{ $filters['type'] ? 'border-slate-200 bg-white text-slate-600 hover:border-brand-navy/30 hover:text-brand-navy' : 'border-brand-navy bg-brand-navy text-white' }}">Semua</a>
                @foreach ($availableTypes as $type)
                    <a href="{{ $queryFor(['type' => $type]) }}" class="shrink-0 rounded-full border px-4 py-2 text-xs font-black transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy {{ $filters['type'] === $type ? 'border-brand-navy bg-brand-navy text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-brand-navy/30 hover:text-brand-navy' }}">{{ $typeLabels[$type] ?? Illuminate\Support\Str::headline($type) }}</a>
                @endforeach
            </nav>

            @if ($hasActiveFilters)
                <div class="mt-4 flex flex-wrap items-center gap-2" aria-label="Filter aktif">
                    <span class="mr-1 text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Filter aktif</span>
                    @if ($filters['q'])
                        <a href="{{ $queryFor([], ['q']) }}" class="rounded-full bg-[#edf2f7] px-3 py-1.5 text-xs font-bold text-brand-navy">“{{ Illuminate\Support\Str::limit($filters['q'], 28) }}” ×</a>
                    @endif
                    @if ($filters['type'])
                        <a href="{{ $queryFor([], ['type']) }}" class="rounded-full bg-[#edf2f7] px-3 py-1.5 text-xs font-bold text-brand-navy">{{ $typeLabels[$filters['type']] ?? $filters['type'] }} ×</a>
                    @endif
                    @if ($filters['status'] === 'closed')
                        <a href="{{ $queryFor([], ['status']) }}" class="rounded-full bg-[#edf2f7] px-3 py-1.5 text-xs font-bold text-brand-navy">Sudah Ditutup ×</a>
                    @endif
                    @if ($filters['format'])
                        <a href="{{ $queryFor([], ['format']) }}" class="rounded-full bg-[#edf2f7] px-3 py-1.5 text-xs font-bold text-brand-navy">{{ $formatLabels[$filters['format']] ?? $filters['format'] }} ×</a>
                    @endif
                    @if ($filters['deadline'])
                        <a href="{{ $queryFor([], ['deadline']) }}" class="rounded-full bg-[#edf2f7] px-3 py-1.5 text-xs font-bold text-brand-navy">{{ $deadlineLabels[$filters['deadline']] ?? $filters['deadline'] }} ×</a>
                    @endif
                    @if ($filters['location'])
                        <a href="{{ $queryFor([], ['location']) }}" class="rounded-full bg-[#edf2f7] px-3 py-1.5 text-xs font-bold text-brand-navy">{{ Illuminate\Support\Str::limit($filters['location'], 28) }} ×</a>
                    @endif
                    <a href="{{ $indexUrl }}" class="ml-1 text-xs font-black text-[#955c08] underline underline-offset-4">Hapus semua filter</a>
                </div>
            @endif
        </div>
    </section>

    @if ($featuredOpportunity)
        <section class="py-10 sm:py-12 lg:py-14" aria-labelledby="featured-opportunity-title">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-5 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#a8660a]">Rekomendasi Kurator</p>
                        <h2 id="featured-opportunity-title" class="mt-1 text-2xl font-black tracking-[-0.02em] text-brand-ink sm:text-3xl">Pilihan Edulaw</h2>
                    </div>
                    <span class="hidden text-xs font-bold text-slate-500 sm:block">Satu peluang yang layak diprioritaskan</span>
                </div>
                <x-opportunities.featured-card :opportunity="$featuredOpportunity" />
            </div>
        </section>
    @endif

    <section class="pb-12 {{ $featuredOpportunity ? 'pt-1' : 'pt-10 sm:pt-12' }} sm:pb-16 lg:pb-20" aria-labelledby="opportunity-results-title">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-2 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#a8660a]">Opportunity Finder</p>
                    <h2 id="opportunity-results-title" class="mt-1 text-2xl font-black tracking-[-0.02em] text-brand-ink sm:text-3xl">{{ $resultsTitle }}</h2>
                </div>
                <p class="text-sm font-bold text-slate-500"><strong class="text-brand-navy">{{ number_format($opportunities->total()) }}</strong> kesempatan ditemukan</p>
            </div>

            @if ($opportunities->isNotEmpty())
                <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3 lg:gap-6">
                    @foreach ($opportunities as $opportunity)
                        <x-opportunities.card :opportunity="$opportunity" />
                    @endforeach
                </div>

                <div class="mt-8 flex flex-col gap-5 border-t border-slate-200 pt-6 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                    <p class="font-bold">Menampilkan {{ $opportunities->firstItem() }}–{{ $opportunities->lastItem() }} dari {{ $opportunities->total() }} peluang</p>
                    {{ $opportunities->onEachSide(1)->links() }}
                </div>
            @else
                <div class="mt-6 rounded-[1.25rem] border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#edf2f7] text-brand-navy" aria-hidden="true">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none"><path d="m21 21-4.35-4.35M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </span>
                    <h3 class="mt-4 text-xl font-black text-brand-ink">Belum ada peluang yang sesuai</h3>
                    <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-600">{{ $hasActiveFilters ? 'Coba ubah kata kunci atau filter untuk memperluas hasil pencarian.' : 'Belum ada opportunity yang tersedia untuk daftar ini saat ini.' }}</p>
                    @if ($hasActiveFilters)
                        <a href="{{ $indexUrl }}" class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-navy px-5 text-sm font-black text-white transition hover:bg-brand-ink focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy">Hapus Filter</a>
                    @endif
                </div>
            @endif
        </div>
    </section>

    <x-shared.cta-section
        eyebrow="Kontribusi Komunitas"
        title="Punya informasi peluang?"
        body="Bagikan informasi beasiswa, kompetisi, call for papers, atau peluang lainnya kepada komunitas Edulaw."
        :primary-url="route('collaboration.index')"
        primary-label="Ajukan Opportunity"
        :secondary-url="route('contact.index')"
        secondary-label="Hubungi Edulaw"
        background-image=""
    />
</main>
@endsection
