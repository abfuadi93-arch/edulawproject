@props(['opportunity', 'view' => 'grid'])

@php
    $summary = $opportunity->excerpt
        ?: Illuminate\Support\Str::limit(strip_tags($opportunity->description ?? ''), 145);
    $isOpen = $opportunity->is_open_for_applications;
    $typeBadgeClass = match ($opportunity->type) {
        'scholarship' => 'bg-emerald-50 text-emerald-700',
        'internship' => 'bg-sky-50 text-sky-700',
        'volunteer' => 'bg-amber-50 text-amber-800',
        'fellowship' => 'bg-violet-50 text-violet-700',
        'call_for_paper' => 'bg-rose-50 text-rose-700',
        'competition' => 'bg-orange-50 text-orange-700',
        'career' => 'bg-blue-50 text-blue-700',
        'open_collaboration' => 'bg-teal-50 text-teal-700',
        default => 'bg-slate-100 text-slate-700',
    };
@endphp

@if ($view === 'list')
    <article class="group grid min-w-0 overflow-hidden rounded-[14px] border border-slate-200 bg-white sm:grid-cols-[190px_minmax(0,1fr)]" data-opportunity-card>
        <a href="{{ route('opportunities.show', $opportunity->slug) }}" class="grid min-h-[250px] place-items-center border-b border-slate-100 bg-[#edf1f5] p-4 focus-visible:outline-2 focus-visible:outline-offset-[-3px] focus-visible:outline-brand-navy sm:min-h-0 sm:border-b-0 sm:border-r" aria-label="Lihat detail {{ $opportunity->title }}">
            <div class="relative aspect-[4/5] w-full max-w-36 overflow-hidden rounded-lg bg-white">
                @if ($opportunity->poster_url)
                    <img src="{{ $opportunity->poster_url }}" alt="Poster {{ $opportunity->title }}" class="size-full object-contain" width="640" height="800" loading="lazy" decoding="async">
                @else
                    <div class="grid size-full place-items-center bg-linear-to-br from-[#e8eef4] to-[#d8e3ec] text-2xl font-black text-brand-navy/55">{{ mb_substr($opportunity->display_type, 0, 1) }}</div>
                @endif
            </div>
        </a>
        <div class="flex min-w-0 flex-col justify-center p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <span class="rounded-full px-2.5 py-1 text-[11px] font-black uppercase tracking-[0.08em] {{ $typeBadgeClass }}">{{ $opportunity->display_type }}</span>
                <span class="text-xs font-bold text-slate-500">{{ $opportunity->display_format }} · {{ $opportunity->location ?: 'Lokasi menyesuaikan' }}</span>
            </div>
            <h2 class="mt-3 line-clamp-2 text-xl font-black leading-snug text-brand-ink transition group-hover:text-brand-navy">
                <a href="{{ route('opportunities.show', $opportunity->slug) }}">{{ $opportunity->title }}</a>
            </h2>
            @if ($summary)
                <p class="mt-2 line-clamp-2 text-base leading-7 text-slate-600">{{ $summary }}</p>
            @endif
            <div class="mt-4 flex flex-wrap items-end justify-between gap-4 border-t border-slate-100 pt-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.11em] text-slate-500">Deadline</p>
                    <p class="mt-1 text-sm font-black text-brand-ink">{{ $opportunity->deadline_display }}</p>
                    <p class="mt-0.5 text-xs font-black {{ $isOpen ? 'text-[#a56408]' : 'text-slate-500' }}">{{ $isOpen ? $opportunity->deadline_relative_label : 'Pendaftaran ditutup' }}</p>
                </div>
                <a href="{{ route('opportunities.show', $opportunity->slug) }}" class="text-sm font-black text-brand-navy">Lihat Detail <span aria-hidden="true">↗</span></a>
            </div>
        </div>
    </article>
@else
    <article class="group flex min-w-0 flex-col overflow-hidden rounded-[14px] border border-slate-200 bg-white transition hover:border-brand-amber/70" data-opportunity-card>
        <a href="{{ route('opportunities.show', $opportunity->slug) }}" class="grid aspect-[4/3] place-items-center bg-[#edf1f5] p-4 focus-visible:outline-2 focus-visible:outline-offset-[-3px] focus-visible:outline-brand-navy" aria-label="Lihat detail {{ $opportunity->title }}">
            <div class="relative aspect-[4/5] h-full max-h-64 overflow-hidden rounded-lg bg-white shadow-sm">
                @if ($opportunity->poster_url)
                    <img src="{{ $opportunity->poster_url }}" alt="Poster {{ $opportunity->title }}" class="size-full object-contain transition duration-500 group-hover:scale-[1.015]" width="640" height="800" loading="lazy" decoding="async">
                @else
                    <div class="grid size-full place-items-center bg-linear-to-br from-[#e8eef4] to-[#d8e3ec] text-2xl font-black text-brand-navy/55">{{ mb_substr($opportunity->display_type, 0, 1) }}</div>
                @endif
            </div>
        </a>

        <div class="flex flex-1 flex-col p-5">
            <div class="flex items-start justify-between gap-2">
                <span class="rounded-full px-2.5 py-1 text-[11px] font-black uppercase tracking-[0.08em] {{ $typeBadgeClass }}">{{ $opportunity->display_type }}</span>
                <span class="line-clamp-1 text-right text-[11px] font-bold text-slate-500">{{ $opportunity->display_format }} · {{ $opportunity->location ?: 'Menyesuaikan' }}</span>
            </div>

            <h2 class="mt-3 line-clamp-3 text-lg font-black leading-snug text-brand-ink transition group-hover:text-brand-navy">
                <a href="{{ route('opportunities.show', $opportunity->slug) }}">{{ $opportunity->title }}</a>
            </h2>
            @if ($summary)
                <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">{{ $summary }}</p>
            @endif

            <div class="mt-auto flex items-end justify-between gap-3 border-t border-slate-100 pt-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.11em] text-slate-500">Deadline</p>
                    <p class="mt-1 text-sm font-black text-brand-ink">{{ $opportunity->deadline_display }}</p>
                    <p class="mt-0.5 text-xs font-black {{ $isOpen ? 'text-[#a56408]' : 'text-slate-500' }}">{{ $isOpen ? $opportunity->deadline_relative_label : 'Pendaftaran ditutup' }}</p>
                </div>
                <a href="{{ route('opportunities.show', $opportunity->slug) }}" class="shrink-0 text-xs font-black text-brand-navy sm:text-sm">Lihat Detail <span aria-hidden="true">↗</span></a>
            </div>
        </div>
    </article>
@endif
