@props(['stats' => collect()])

@php
    $impactStats = collect($stats)->take(6)->values();
@endphp

@if ($impactStats->isNotEmpty())
    <section class="relative z-10" data-home-impact style="--impact-mobile-rows: {{ (int) ceil($impactStats->count() / 2) }}; --impact-tablet-rows: {{ (int) ceil($impactStats->count() / 3) }}" aria-labelledby="home-impact-title">
        <div class="section-shell">
            <h2 id="home-impact-title" class="sr-only">Dampak Edulaw Project</h2>
            <dl class="grid grid-cols-2 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_14px_35px_-28px_rgba(15,23,42,.55)] sm:grid-cols-3 lg:grid-cols-6" aria-label="Statistik Edulaw Project">
                @foreach ($impactStats as $stat)
                    <div data-home-impact-stat="{{ $stat['label'] }}" class="flex min-h-24 items-center gap-3 border-b border-r border-slate-200 px-4 py-4 last:border-r-0 sm:px-5 lg:min-h-[104px] lg:border-b-0">
                        <span class="grid size-9 shrink-0 place-items-center text-brand-navy/80" aria-hidden="true">
                            @switch($stat['label'])
                                @case('Insight Terbit')
                                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6 3h9l3 3v15H6z"/><path d="M14 3v4h4M9 11h6M9 15h6"/></svg>
                                    @break
                                @case('Program Edulaw')
                                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    @break
                                @case('Riset & Publikasi')
                                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5.5A3.5 3.5 0 0 1 7.5 2H11v17H7.5A3.5 3.5 0 0 0 4 22zM20 5.5A3.5 3.5 0 0 0 16.5 2H13v17h3.5A3.5 3.5 0 0 1 20 22z"/></svg>
                                    @break
                                @case('Konten Multimedia')
                                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m10 9 5 3-5 3z"/></svg>
                                    @break
                                @case('Kontributor Aktif')
                                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4z"/></svg>
                                    @break
                                @default
                                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 11v2M6 9v6M9 7v10M12 12l8-5v10l-8-5Z"/><path d="M12 12v7a2 2 0 0 1-2 2H9"/></svg>
                            @endswitch
                        </span>
                        <div class="min-w-0">
                            <dd class="text-xl font-bold leading-none text-brand-navy sm:text-[22px]">{{ number_format($stat['value'], 0, ',', '.') }}</dd>
                            <dt class="mt-1.5 text-xs font-bold leading-4 text-slate-500">{{ $stat['label'] }}</dt>
                        </div>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>
@endif
