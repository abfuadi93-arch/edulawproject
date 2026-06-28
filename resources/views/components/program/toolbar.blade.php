@props([
    'total' => 0,
    'search' => '',
    'selectedSort' => 'terdekat',
    'selectedView' => 'grid',
])

@php
    $indexUrl = \Illuminate\Support\Facades\Route::has('programs.index') ? route('programs.index') : url('/program');
    $preservedQuery = request()->except(['q', 'sort', 'view', 'active_page', 'archive_page']);
    $sortOptions = [
        'terbaru' => 'Terbaru',
        'terdekat' => 'Terdekat',
        'nama' => 'Nama A-Z',
    ];
@endphp

<div class="mb-5 space-y-4">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-[11px] font-black uppercase tracking-[0.22em] text-brand-navy">
                Program Aktif ({{ $total }})
            </p>
        </div>

        <form action="{{ $indexUrl }}" method="GET" class="grid gap-3 sm:grid-cols-[minmax(220px,1fr)_150px_auto] lg:min-w-[560px]">
            @foreach ($preservedQuery as $key => $value)
                @foreach (\Illuminate\Support\Arr::wrap($value) as $item)
                    <input type="hidden" name="{{ is_array($value) ? $key.'[]' : $key }}" value="{{ $item }}">
                @endforeach
            @endforeach

            <input type="hidden" name="view" value="{{ $selectedView }}">

            <label class="relative block">
                <span class="sr-only">Search Program</span>
                <input
                    type="search"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Cari program aktif..."
                    oninput="clearTimeout(this._programSearchTimer); this._programSearchTimer = setTimeout(() => this.form.requestSubmit(), 450)"
                    class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-4 pr-10 text-sm font-semibold text-brand-ink shadow-sm outline-none transition placeholder:text-slate-400 focus:border-brand-navy focus:ring-4 focus:ring-brand-navy/10"
                >
                <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-brand-navy" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </label>

            <label>
                <span class="sr-only">Urutkan Program</span>
                <select
                    name="sort"
                    onchange="this.form.requestSubmit()"
                    class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-black text-brand-navy shadow-sm outline-none transition focus:border-brand-navy focus:ring-4 focus:ring-brand-navy/10"
                >
                    @foreach ($sortOptions as $value => $label)
                        <option value="{{ $value }}" @selected($selectedSort === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <div class="flex items-center gap-2">
                <a
                    href="{{ request()->fullUrlWithQuery(['view' => 'grid']) }}"
                    aria-label="Tampilan grid"
                    @class([
                        'grid h-11 w-11 place-items-center rounded-xl border text-sm font-black transition',
                        'border-brand-navy bg-brand-navy text-white shadow-lg shadow-brand-navy/15' => $selectedView === 'grid',
                        'border-slate-200 bg-white text-brand-navy hover:border-brand-navy' => $selectedView !== 'grid',
                    ])
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 4h6v6H4V4Zm10 0h6v6h-6V4ZM4 14h6v6H4v-6Zm10 0h6v6h-6v-6Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                    </svg>
                </a>

                <a
                    href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}"
                    aria-label="Tampilan list"
                    @class([
                        'grid h-11 w-11 place-items-center rounded-xl border text-sm font-black transition',
                        'border-brand-navy bg-brand-navy text-white shadow-lg shadow-brand-navy/15' => $selectedView === 'list',
                        'border-slate-200 bg-white text-brand-navy hover:border-brand-navy' => $selectedView !== 'list',
                    ])
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M8 6h12M8 12h12M8 18h12M4 6h.01M4 12h.01M4 18h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </a>
            </div>
        </form>
    </div>
</div>
