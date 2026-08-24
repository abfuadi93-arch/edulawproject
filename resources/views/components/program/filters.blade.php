@props([
    'statusOptions' => [],
    'categoryOptions' => [],
    'formatOptions' => [],
    'levelOptions' => [],
    'selectedStatuses' => [],
    'selectedCategories' => [],
    'selectedFormats' => [],
    'selectedLevels' => [],
    'search' => '',
    'selectedSort' => 'terdekat',
    'selectedView' => 'grid',
])

@php
    $indexUrl = Route::has('programs.index') ? route('programs.index') : url('/program');
    $selectedStatus = collect($selectedStatuses)->first();
    $selectedCategory = collect($selectedCategories)->first();
    $selectedFormat = collect($selectedFormats)->first();
    $selectedLevel = collect($selectedLevels)->first();
@endphp

<form action="{{ $indexUrl }}" method="GET" class="rounded-xl border border-[#dce5e3] bg-white/90 p-3" data-program-filter-bar>
    <input type="hidden" name="sort" value="{{ $selectedSort }}">
    <input type="hidden" name="view" value="{{ $selectedView }}">

    <div class="grid min-w-0 gap-2 sm:grid-cols-2 lg:grid-cols-[minmax(190px,1.4fr)_repeat(4,minmax(125px,.72fr))_auto_auto]">
        <label class="relative min-w-0 sm:col-span-2 lg:col-span-1">
            <span class="sr-only">Cari program</span>
            <input
                type="search"
                name="q"
                value="{{ $search }}"
                placeholder="Cari program..."
                class="h-11 w-full min-w-0 rounded-lg border border-slate-200 bg-white pl-3 pr-10 text-sm font-semibold text-brand-ink outline-none transition placeholder:text-slate-400 focus:border-brand-navy focus:ring-2 focus:ring-brand-navy/10"
            >
            <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-brand-navy" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </label>

        <label class="min-w-0">
            <span class="sr-only">Status program</span>
            <select name="status" class="h-11 w-full min-w-0 rounded-lg border border-slate-200 bg-white px-3 text-sm font-bold text-brand-navy outline-none focus:border-brand-navy focus:ring-2 focus:ring-brand-navy/10">
                <option value="">Status</option>
                @foreach ($statusOptions as $option)
                    <option value="{{ $option['value'] }}" @selected($selectedStatus === $option['value'])>{{ $option['label'] }} ({{ $option['count'] ?? 0 }})</option>
                @endforeach
            </select>
        </label>

        <label class="min-w-0">
            <span class="sr-only">Kategori program</span>
            <select name="category" class="h-11 w-full min-w-0 rounded-lg border border-slate-200 bg-white px-3 text-sm font-bold text-brand-navy outline-none focus:border-brand-navy focus:ring-2 focus:ring-brand-navy/10">
                <option value="">Kategori</option>
                @foreach ($categoryOptions as $option)
                    <option value="{{ $option['value'] }}" @selected($selectedCategory === $option['value'])>{{ $option['label'] }} ({{ $option['count'] ?? 0 }})</option>
                @endforeach
            </select>
        </label>

        <label class="min-w-0">
            <span class="sr-only">Format program</span>
            <select name="format" class="h-11 w-full min-w-0 rounded-lg border border-slate-200 bg-white px-3 text-sm font-bold text-brand-navy outline-none focus:border-brand-navy focus:ring-2 focus:ring-brand-navy/10">
                <option value="">Format</option>
                @foreach ($formatOptions as $option)
                    <option value="{{ $option['value'] }}" @selected($selectedFormat === $option['value'])>{{ $option['label'] }} ({{ $option['count'] ?? 0 }})</option>
                @endforeach
            </select>
        </label>

        <label class="min-w-0">
            <span class="sr-only">Level program</span>
            <select name="level" class="h-11 w-full min-w-0 rounded-lg border border-slate-200 bg-white px-3 text-sm font-bold text-brand-navy outline-none focus:border-brand-navy focus:ring-2 focus:ring-brand-navy/10">
                <option value="">Level</option>
                @foreach ($levelOptions as $option)
                    <option value="{{ $option['value'] }}" @selected($selectedLevel === $option['value'])>{{ $option['label'] }} ({{ $option['count'] ?? 0 }})</option>
                @endforeach
            </select>
        </label>

        <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-brand-navy px-4 text-sm font-black text-white transition hover:bg-[#102B4B]">
            Terapkan
        </button>

        <a href="{{ $indexUrl }}" class="inline-flex min-h-11 items-center justify-center rounded-lg px-3 text-sm font-black text-slate-500 transition hover:bg-slate-50 hover:text-brand-navy">
            Reset
        </a>
    </div>
</form>
