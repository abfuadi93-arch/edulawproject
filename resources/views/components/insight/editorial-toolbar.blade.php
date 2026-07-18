@props(['channels', 'selectedCategory', 'search', 'featuredOnly'])

<section aria-label="Filter editorial" class="border-y border-slate-200 bg-white">
    <div class="mx-auto flex max-w-7xl flex-col gap-4 px-5 py-[18px] sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8 lg:py-5">
        <nav aria-label="Kategori editorial" class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden lg:pb-0">
            <a href="{{ route('insights.index') }}" class="inline-flex h-10 shrink-0 items-center rounded-md border px-3.5 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber {{ blank($selectedCategory) && blank($search) && ! $featuredOnly ? 'border-brand-navy bg-brand-navy text-white' : 'border-slate-200 bg-white text-brand-navy hover:border-brand-navy/35' }}">Semua</a>
            @foreach ($channels as $channel)
                @php
                    $category = $channel['category'] ?? null;
                    $label = $channel['label'];
                    $url = $category
                        ? route('insights.index', ['category' => $category->slug]).'#insight-archive'
                        : route('insights.index', ['q' => $label, 'archive' => 'latest']).'#insight-archive';
                    $active = $category
                        ? $selectedCategory === $category->slug
                        : blank($selectedCategory) && Illuminate\Support\Str::lower($search) === Illuminate\Support\Str::lower($label);
                @endphp
                <a href="{{ $url }}" class="inline-flex h-10 shrink-0 items-center rounded-md border px-3.5 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber {{ $active ? 'border-brand-navy bg-brand-navy text-white' : 'border-slate-200 bg-white text-brand-navy hover:border-brand-navy/35' }}">{{ $label }}</a>
            @endforeach
        </nav>

        <form method="GET" action="{{ route('insights.index') }}#insight-archive" class="relative w-full lg:w-[320px]">
            <input type="hidden" name="archive" value="latest">
            @if ($selectedCategory)
                <input type="hidden" name="category" value="{{ $selectedCategory }}">
            @endif
            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m21 21-4.3-4.3M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            <label for="editorial-search" class="sr-only">Cari artikel editorial</label>
            <input id="editorial-search" name="q" value="{{ $search }}" type="search" placeholder="Cari artikel editorial" class="h-11 w-full rounded-md border border-slate-200 bg-white pl-10 pr-4 text-sm font-medium text-brand-ink outline-none transition placeholder:text-slate-400 focus:border-brand-navy focus:ring-2 focus:ring-brand-navy/10">
        </form>
    </div>
</section>
