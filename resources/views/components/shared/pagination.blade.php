@props([
    'paginator',
    'fragment' => null,
    'label' => 'Navigasi halaman arsip',
])

@if ($paginator->hasPages())
    @php
        $firstPage = max(1, $paginator->currentPage() - 2);
        $lastPage = min($paginator->lastPage(), $paginator->currentPage() + 2);
        $normalizedFragment = filled($fragment) ? ltrim((string) $fragment, '#') : null;
        $pageUrl = static function (?string $url) use ($normalizedFragment): ?string {
            if (blank($url) || blank($normalizedFragment) || str_contains($url, '#')) {
                return $url;
            }

            return $url.'#'.$normalizedFragment;
        };
    @endphp

    <nav aria-label="{{ $label }}" {{ $attributes->class('mt-7 flex flex-wrap items-center justify-center gap-2') }}>
        @if (! $paginator->onFirstPage())
            <a href="{{ $pageUrl($paginator->previousPageUrl()) }}" aria-label="Halaman sebelumnya" class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 bg-white text-sm font-bold text-brand-navy">←</a>
        @endif

        @foreach (range($firstPage, $lastPage) as $page)
            <a href="{{ $pageUrl($paginator->url($page)) }}" aria-current="{{ $page === $paginator->currentPage() ? 'page' : 'false' }}" class="grid h-9 min-w-9 place-items-center rounded-lg px-2 text-xs font-extrabold {{ $page === $paginator->currentPage() ? 'bg-brand-navy text-white' : 'border border-slate-200 bg-white text-brand-navy' }}">{{ $page }}</a>
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $pageUrl($paginator->nextPageUrl()) }}" aria-label="Halaman berikutnya" class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 bg-white text-sm font-bold text-brand-navy">→</a>
        @endif

        <span class="ml-2 text-xs font-semibold text-slate-500">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>
    </nav>
@endif
