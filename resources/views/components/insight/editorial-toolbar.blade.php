@props([
    'channels',
    'selectedCategory',
    'search',
    'featuredOnly',
    'selectedSort' => 'latest',
    'selectedView' => 'grid',
    'selectedTag' => null,
])

@php
    $channels = collect($channels);
    $gridParams = array_filter([
        'archive' => 'latest',
        'q' => $search ?: null,
        'category' => $selectedCategory ?: null,
        'tag' => $selectedTag,
        'featured' => $featuredOnly ? 1 : null,
        'sort' => $selectedSort !== 'latest' ? $selectedSort : null,
        'view' => 'grid',
    ]);
    $listParams = array_merge($gridParams, ['view' => 'list']);
@endphp

<div class="mt-5 rounded-[13px] bg-white p-3">
    <form method="GET" action="{{ route('insights.index') }}#insight-archive" class="grid gap-2 sm:grid-cols-2 lg:grid-cols-[minmax(280px,1fr)_190px_150px_auto_auto]">
        <input type="hidden" name="archive" value="latest">
        <input type="hidden" name="view" value="{{ $selectedView }}">
        @if (filled($selectedTag))
            <input type="hidden" name="tag" value="{{ $selectedTag }}">
        @endif
        @if ($featuredOnly)
            <input type="hidden" name="featured" value="1">
        @endif

        <div class="relative min-w-0">
            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m21 21-4.3-4.3M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            <label for="editorial-search" class="sr-only">Cari artikel editorial</label>
            <input id="editorial-search" name="q" value="{{ $search }}" type="search" placeholder="Cari judul, isu, putusan, atau kata kunci..." class="h-11 w-full rounded-lg border border-slate-200 bg-[#f8fafc] pl-10 pr-3 text-sm font-medium text-brand-ink outline-none transition placeholder:text-slate-400 focus:border-brand-navy focus:bg-white focus:ring-2 focus:ring-brand-navy/10">
        </div>

        <label class="sr-only" for="editorial-category">Kategori editorial</label>
        <select id="editorial-category" name="category" class="h-11 min-w-0 rounded-lg border border-slate-200 bg-[#f8fafc] px-3 text-sm font-bold text-brand-navy outline-none focus:border-brand-navy focus:ring-2 focus:ring-brand-navy/10">
            <option value="">Semua Kategori</option>
            @foreach ($channels as $channel)
                @php($category = $channel['category'] ?? null)
                @if ($category)
                    <option value="{{ $category->slug }}" @selected($selectedCategory === $category->slug)>{{ $channel['label'] }}</option>
                @endif
            @endforeach
        </select>

        <label class="sr-only" for="editorial-sort">Urutkan editorial</label>
        <select id="editorial-sort" name="sort" class="h-11 min-w-0 rounded-lg border border-slate-200 bg-[#f8fafc] px-3 text-sm font-bold text-brand-navy outline-none focus:border-brand-navy focus:ring-2 focus:ring-brand-navy/10">
            <option value="latest" @selected($selectedSort === 'latest')>Terbaru</option>
            <option value="oldest" @selected($selectedSort === 'oldest')>Terlama</option>
            <option value="title" @selected($selectedSort === 'title')>Judul A–Z</option>
        </select>

        <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-navy px-4 text-sm font-extrabold text-white transition hover:bg-brand-ink">Terapkan</button>

        <div class="flex h-11 items-center rounded-lg border border-slate-200 bg-[#f8fafc] p-1" aria-label="Pilihan tampilan">
            <a href="{{ route('insights.index', $gridParams) }}#insight-archive" aria-label="Tampilan grid" aria-current="{{ $selectedView === 'grid' ? 'true' : 'false' }}" class="grid h-9 w-9 place-items-center rounded-md {{ $selectedView === 'grid' ? 'bg-brand-navy text-white' : 'text-slate-500 hover:text-brand-navy' }}">
                <svg viewBox="0 0 20 20" class="h-4 w-4" fill="currentColor" aria-hidden="true"><path d="M2.5 2.5h6v6h-6v-6Zm9 0h6v6h-6v-6Zm-9 9h6v6h-6v-6Zm9 0h6v6h-6v-6Z"/></svg>
            </a>
            <a href="{{ route('insights.index', $listParams) }}#insight-archive" aria-label="Tampilan daftar" aria-current="{{ $selectedView === 'list' ? 'true' : 'false' }}" class="grid h-9 w-9 place-items-center rounded-md {{ $selectedView === 'list' ? 'bg-brand-navy text-white' : 'text-slate-500 hover:text-brand-navy' }}">
                <svg viewBox="0 0 20 20" class="h-4 w-4" fill="currentColor" aria-hidden="true"><path d="M2.5 3.5h3v3h-3v-3Zm5 0h10v3h-10v-3Zm-5 5h3v3h-3v-3Zm5 0h10v3h-10v-3Zm-5 5h3v3h-3v-3Zm5 0h10v3h-10v-3Z"/></svg>
            </a>
        </div>
    </form>

    @if ($channels->isNotEmpty())
        <nav aria-label="Kategori editorial" class="mt-2 flex gap-2 overflow-x-auto pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            <a href="{{ route('insights.index', ['archive' => 'latest', 'tag' => $selectedTag]) }}#insight-archive" class="inline-flex min-h-8 shrink-0 items-center rounded-full px-3 text-xs font-bold {{ blank($selectedCategory) ? 'bg-brand-navy text-white' : 'bg-slate-100 text-brand-navy' }}">Semua</a>
            @foreach ($channels as $channel)
                @php($category = $channel['category'] ?? null)
                @if ($category)
                    <a href="{{ route('insights.index', ['archive' => 'latest', 'category' => $category->slug, 'tag' => $selectedTag]) }}#insight-archive" class="inline-flex min-h-8 shrink-0 items-center rounded-full px-3 text-xs font-bold {{ $selectedCategory === $category->slug ? 'bg-brand-navy text-white' : 'bg-slate-100 text-brand-navy' }}">{{ $channel['label'] }}</a>
                @endif
            @endforeach
        </nav>
    @endif
</div>
