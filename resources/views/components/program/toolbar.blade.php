@props([
    'total' => 0,
    'search' => '',
    'selectedSort' => 'terdekat',
    'selectedView' => 'grid',
])

@php
    $indexUrl = Route::has('programs.index') ? route('programs.index') : url('/program');
    $preservedQuery = request()->except(['sort', 'view', 'active_page', 'archive_page']);
    $sortOptions = [
        'terbaru' => 'Terbaru',
        'terdekat' => 'Terdekat',
        'nama' => 'Nama A-Z',
    ];
@endphp

<div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <h2 id="program-active-title" class="text-sm font-black uppercase tracking-[0.14em] text-brand-navy">
        Program Aktif ({{ $total }})
    </h2>

    <div class="flex items-center gap-2 self-start sm:self-auto">
        <form action="{{ $indexUrl }}" method="GET">
            @foreach ($preservedQuery as $key => $value)
                @foreach (\Illuminate\Support\Arr::wrap($value) as $item)
                    <input type="hidden" name="{{ is_array($value) ? $key.'[]' : $key }}" value="{{ $item }}">
                @endforeach
            @endforeach
            <input type="hidden" name="view" value="{{ $selectedView }}">
            <label>
                <span class="sr-only">Urutkan program</span>
                <select name="sort" onchange="this.form.requestSubmit()" class="h-10 rounded-lg border-0 bg-transparent px-2 text-sm font-bold text-brand-navy outline-none focus:ring-2 focus:ring-brand-navy/10">
                    @foreach ($sortOptions as $value => $label)
                        <option value="{{ $value }}" @selected($selectedSort === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </form>

        <a
            href="{{ request()->fullUrlWithQuery(['view' => 'grid']) }}"
            aria-label="Tampilan grid"
            @class([
                'grid h-10 w-10 place-items-center rounded-lg border text-sm transition',
                'border-brand-navy bg-brand-navy text-white' => $selectedView === 'grid',
                'border-slate-200 bg-white text-brand-navy hover:border-brand-navy' => $selectedView !== 'grid',
            ])
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 4h6v6H4V4Zm10 0h6v6h-6V4ZM4 14h6v6H4v-6Zm10 0h6v6h-6v-6Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
        </a>

        <a
            href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}"
            aria-label="Tampilan list"
            @class([
                'grid h-10 w-10 place-items-center rounded-lg border text-sm transition',
                'border-brand-navy bg-brand-navy text-white' => $selectedView === 'list',
                'border-slate-200 bg-white text-brand-navy hover:border-brand-navy' => $selectedView !== 'list',
            ])
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M8 6h12M8 12h12M8 18h12M4 6h.01M4 12h.01M4 18h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </a>
    </div>
</div>
