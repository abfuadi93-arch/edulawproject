@props(['paginator'])

@if ($paginator->hasPages())
    @php
        $start = max(1, $paginator->currentPage() - 2);
        $end = min($paginator->lastPage(), $paginator->currentPage() + 2);
    @endphp

    <nav aria-label="Pagination video YouTube" class="mt-8 flex items-center justify-center gap-2">
        @if ($paginator->onFirstPage())
            <span aria-disabled="true" class="inline-flex min-h-10 items-center rounded-full border border-slate-200 bg-slate-100 px-3.5 text-xs font-black text-slate-400 sm:px-4">Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex min-h-10 items-center rounded-full border border-slate-200 bg-white px-3.5 text-xs font-black text-brand-navy transition hover:border-brand-navy focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy sm:px-4">Sebelumnya</a>
        @endif

        @foreach (range($start, $end) as $page)
            @if ($page === $paginator->currentPage())
                <span aria-current="page" class="inline-flex h-10 min-w-10 items-center justify-center rounded-full border border-brand-navy bg-brand-navy px-3 text-xs font-black text-white">{{ $page }}</span>
            @else
                <a href="{{ $paginator->url($page) }}" aria-label="Buka halaman video {{ $page }}" class="hidden h-10 min-w-10 items-center justify-center rounded-full border border-slate-200 bg-white px-3 text-xs font-black text-brand-navy transition hover:border-brand-navy focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy sm:inline-flex">{{ $page }}</a>
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex min-h-10 items-center rounded-full border border-slate-200 bg-white px-3.5 text-xs font-black text-brand-navy transition hover:border-brand-navy focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy sm:px-4">Berikutnya</a>
        @else
            <span aria-disabled="true" class="inline-flex min-h-10 items-center rounded-full border border-slate-200 bg-slate-100 px-3.5 text-xs font-black text-slate-400 sm:px-4">Berikutnya</span>
        @endif
    </nav>
@endif
