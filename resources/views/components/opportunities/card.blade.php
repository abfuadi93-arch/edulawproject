@props(['opportunity', 'view' => 'grid'])

@php
    $summary = $opportunity->excerpt
        ?: Illuminate\Support\Str::limit(strip_tags($opportunity->description ?? ''), 145);
    $isOpen = $opportunity->is_open_for_applications;
    $officialUrl = $opportunity->external_url;
    $optionalLinks = $opportunity->optional_links;
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
        <a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="grid min-h-[250px] place-items-center border-b border-slate-100 bg-[#edf1f5] p-4 focus-visible:outline-2 focus-visible:outline-offset-[-3px] focus-visible:outline-brand-navy sm:min-h-0 sm:border-b-0 sm:border-r" aria-label="Lihat informasi resmi {{ $opportunity->title }}">
            <div class="relative aspect-[4/5] w-full max-w-36 overflow-hidden rounded-lg bg-white">
                @if ($opportunity->poster_url)
                    <img src="{{ $opportunity->poster_url }}" alt="Poster {{ $opportunity->title }}" class="size-full object-contain" width="640" height="800" loading="lazy" decoding="async">
                @else
                    <div class="grid size-full place-items-center bg-linear-to-br from-[#e8eef4] to-[#d8e3ec] text-2xl font-bold text-brand-navy/55">{{ mb_substr($opportunity->display_type, 0, 1) }}</div>
                @endif
            </div>
        </a>
        <div class="flex min-w-0 flex-col justify-center p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.08em] {{ $typeBadgeClass }}">{{ $opportunity->display_type }}</span>
                <span class="type-role-meta font-normal text-slate-500">{{ $opportunity->display_format }} · {{ $opportunity->location ?: 'Lokasi menyesuaikan' }}</span>
            </div>
            <h2 class="type-role-card mt-3 line-clamp-2 text-xl font-bold leading-snug text-brand-ink transition group-hover:text-brand-navy">
                <a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer">{{ $opportunity->title }}</a>
            </h2>
            @if ($summary)
                <p class="mt-2 line-clamp-2 text-base leading-7 text-slate-600">{{ $summary }}</p>
            @endif
            @if ($opportunity->organizer || $opportunity->target_audience)
                <p class="mt-3 type-role-meta font-normal text-slate-500">
                    @if ($opportunity->organizer){{ $opportunity->organizer }}@endif
                    @if ($opportunity->organizer && $opportunity->target_audience)<span aria-hidden="true"> · </span>@endif
                    @if ($opportunity->target_audience)Target: {{ $opportunity->target_audience }}@endif
                </p>
            @endif
            <div class="mt-4 grid gap-4 border-t border-slate-100 pt-4 sm:grid-cols-[minmax(0,1fr)_minmax(18rem,auto)] sm:items-end">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.11em] text-slate-500">Deadline</p>
                    <p class="mt-1 text-sm font-bold text-brand-ink">{{ $opportunity->deadline_display }}</p>
                    <p class="mt-0.5 type-role-meta font-normal {{ $isOpen ? 'text-[#a56408]' : 'text-slate-500' }}">{{ $isOpen ? $opportunity->deadline_relative_label : 'Pendaftaran ditutup' }}</p>
                </div>
                <div class="grid gap-2 {{ match (count($optionalLinks)) { 2 => 'grid-cols-3', 1 => 'grid-cols-2', default => 'grid-cols-1' } }}">
                    <a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-brand-navy/20 px-3 text-center text-xs font-bold text-brand-navy transition hover:border-brand-navy hover:bg-slate-50 sm:text-sm">Informasi Resmi <svg class="ml-1 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                    @foreach ($optionalLinks as $optionalLink)
                        <a href="{{ $optionalLink['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-navy px-3 text-center text-xs font-bold text-white transition hover:bg-brand-ink sm:text-sm">{{ $optionalLink['label'] }} <svg class="ml-1 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                    @endforeach
                </div>
            </div>
        </div>
    </article>
@else
    <article class="opportunity-landscape group grid aspect-[21/9] w-full min-w-0 grid-rows-[minmax(0,1fr)] grid-cols-[minmax(0,3fr)_minmax(0,7fr)] sm:grid-cols-[minmax(0,35fr)_minmax(0,65fr)] overflow-hidden rounded-[14px] border border-slate-200 bg-white transition hover:border-brand-amber/70" data-opportunity-card>
        <a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="grid min-h-0 min-w-0 place-items-center overflow-hidden bg-white focus-visible:outline-2 focus-visible:outline-offset-[-3px] focus-visible:outline-brand-navy" aria-label="Lihat informasi resmi {{ $opportunity->title }}">
            <div class="relative aspect-[4/5] w-full max-h-full overflow-hidden">
                @if ($opportunity->poster_url)
                    <img src="{{ $opportunity->poster_url }}" alt="Poster {{ $opportunity->title }}" class="absolute inset-0 size-full object-contain object-center" width="640" height="800" loading="lazy" decoding="async">
                @else
                    <div class="grid size-full place-items-center bg-linear-to-br from-[#e8eef4] to-[#d8e3ec] text-2xl font-bold text-brand-navy/55">{{ mb_substr($opportunity->display_type, 0, 1) }}</div>
                @endif
            </div>
        </a>

        <div class="opportunity-landscape-content flex min-h-0 min-w-0 flex-col p-3">
            <div class="flex items-start justify-between gap-2">
                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.08em] {{ $typeBadgeClass }}">{{ $opportunity->display_type }}</span>
                <span class="line-clamp-1 text-right text-[11px] font-bold text-slate-500">{{ $opportunity->display_format }} · {{ $opportunity->location ?: 'Menyesuaikan' }}</span>
            </div>

            <h2 class="type-role-card mt-2 line-clamp-2 text-lg font-bold leading-snug text-brand-ink transition group-hover:text-brand-navy">
                <a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer">{{ $opportunity->title }}</a>
            </h2>
            @if ($summary)
                <p class="opportunity-summary mt-2 line-clamp-3 text-sm leading-5 text-slate-600">{{ $summary }}</p>
            @endif
            @if ($opportunity->organizer || $opportunity->target_audience)
                <p class="opportunity-organizer mt-2 line-clamp-1 type-role-meta font-normal leading-5 text-slate-500">
                    @if ($opportunity->organizer){{ $opportunity->organizer }}@endif
                    @if ($opportunity->organizer && $opportunity->target_audience)<span aria-hidden="true"> · </span>@endif
                    @if ($opportunity->target_audience)Target: {{ $opportunity->target_audience }}@endif
                </p>
            @endif

            <div class="opportunity-landscape-footer mt-auto border-t border-slate-100 pt-2">
                <div class="flex flex-wrap items-baseline gap-x-2">
                    <p class="text-[11px] font-bold uppercase tracking-[0.11em] text-slate-500">Deadline</p>
                    <p class="mt-1 text-sm font-bold text-brand-ink">{{ $opportunity->deadline_display }}</p>
                    <p class="opportunity-relative-date ml-auto mt-0.5 text-right type-role-meta font-normal {{ $isOpen ? 'text-[#a56408]' : 'text-slate-500' }}">{{ $isOpen ? $opportunity->deadline_relative_label : 'Pendaftaran ditutup' }}</p>
                </div>
                <div class="mt-2 grid gap-2 {{ match (count($optionalLinks)) { 2 => 'grid-cols-3', 1 => 'grid-cols-2', default => 'grid-cols-1' } }}">
                    <a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-brand-navy/20 px-2 text-center text-xs font-bold text-brand-navy transition hover:border-brand-navy hover:bg-slate-50">Informasi Resmi <svg class="ml-1 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                    @foreach ($optionalLinks as $optionalLink)
                        <a href="{{ $optionalLink['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-navy px-2 text-center text-xs font-bold text-white transition hover:bg-brand-ink">{{ $optionalLink['label'] }} <svg class="ml-1 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                    @endforeach
                </div>
            </div>
        </div>
    </article>
@endif
