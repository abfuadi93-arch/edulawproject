@props(['channels', 'selectedCategory', 'search', 'featuredOnly'])

@php
    $channels = collect($channels);
    $hasChannels = $channels->isNotEmpty();
    $baseParams = [];

    if (filled($search)) {
        $baseParams['q'] = $search;
        $baseParams['archive'] = 'latest';
    }

    $allUrl = route('insights.index', $baseParams).($baseParams ? '#insight-archive' : '');
@endphp

<section aria-label="Navigasi editorial" class="border-y border-slate-200 bg-white">
    <div class="mx-auto flex max-w-7xl flex-col gap-3 px-5 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:gap-5 lg:px-8">
        @if ($hasChannels)
        <nav aria-label="Kategori editorial" class="-mx-1 flex flex-nowrap gap-2 overflow-x-auto px-1 pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden lg:mx-0 lg:min-w-0 lg:flex-1 lg:flex-wrap lg:overflow-visible lg:pb-0 lg:px-0">
            <a href="{{ $allUrl }}" class="inline-flex h-9 shrink-0 items-center whitespace-nowrap rounded-full border px-3 text-xs font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber sm:text-sm {{ blank($selectedCategory) && ! $featuredOnly ? 'border-brand-navy bg-brand-navy text-white shadow-sm shadow-brand-navy/10' : 'border-slate-200 bg-slate-50 text-brand-navy hover:border-brand-navy/35 hover:bg-white' }}">Semua</a>
            @foreach ($channels as $channel)
                @php
                    $category = $channel['category'] ?? null;
                    $label = $channel['label'];
                    $params = ['archive' => 'latest'];

                    if ($category) {
                        $params['category'] = $category->slug;

                        if (filled($search)) {
                            $params['q'] = $search;
                        }
                    } else {
                        $params['q'] = $label;
                    }

                    $url = route('insights.index', $params).'#insight-archive';
                    $active = $category
                        ? $selectedCategory === $category->slug
                        : blank($selectedCategory) && Illuminate\Support\Str::lower($search) === Illuminate\Support\Str::lower($label);
                @endphp
                <a href="{{ $url }}" class="inline-flex h-9 shrink-0 items-center whitespace-nowrap rounded-full border px-3 text-xs font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber sm:text-sm {{ $active ? 'border-brand-navy bg-brand-navy text-white shadow-sm shadow-brand-navy/10' : 'border-slate-200 bg-slate-50 text-brand-navy hover:border-brand-navy/35 hover:bg-white' }}">{{ $label }}</a>
            @endforeach
        </nav>
        @endif

        <form method="GET" action="{{ route('insights.index') }}#insight-archive" class="relative w-full lg:ml-auto lg:w-72 xl:w-80">
            <input type="hidden" name="archive" value="latest">
            @if ($selectedCategory)
                <input type="hidden" name="category" value="{{ $selectedCategory }}">
            @endif
            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m21 21-4.3-4.3M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            <label for="editorial-search" class="sr-only">Cari artikel editorial</label>
            <input id="editorial-search" name="q" value="{{ $search }}" type="search" placeholder="Cari editorial..." class="h-10 w-full rounded-full border border-slate-200 bg-slate-50 pl-9 pr-4 text-sm font-medium text-brand-ink outline-none transition placeholder:text-slate-400 hover:bg-white focus:border-brand-navy focus:bg-white focus:ring-2 focus:ring-brand-navy/10">
        </form>
    </div>
</section>
