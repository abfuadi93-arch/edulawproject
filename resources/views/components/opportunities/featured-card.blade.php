@props(['opportunity'])

@php
    $summary = filled($opportunity->excerpt)
        ? Illuminate\Support\Str::limit(Illuminate\Support\Str::squish(strip_tags($opportunity->excerpt)), 180)
        : null;
    $officialUrl = $opportunity->external_url;
@endphp

<article class="overflow-hidden rounded-[14px] border border-[#dbe2ea] bg-white" data-featured-opportunity>
    <div class="grid md:grid-cols-[minmax(15rem,.8fr)_minmax(0,1.7fr)]">
        <div class="flex items-center justify-center bg-[#eef2f6] p-4 sm:p-6">
            <a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="relative block aspect-[4/5] w-full max-w-[18rem] overflow-hidden rounded-2xl border border-white/80 bg-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-navy" aria-label="Lihat informasi resmi {{ $opportunity->title }}">
                @if ($opportunity->poster_url)
                    <img
                        src="{{ $opportunity->poster_url }}"
                        alt="Poster {{ $opportunity->title }}"
                        class="h-full w-full object-contain"
                        width="640"
                        height="800"
                        decoding="async"
                        fetchpriority="high"
                    >
                @else
                    <div class="flex h-full flex-col items-center justify-center bg-linear-to-br from-[#e9eef4] to-[#dbe5ed] px-6 text-center" aria-hidden="true">
                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-2xl font-black text-brand-navy shadow-sm">
                            {{ mb_substr($opportunity->display_type, 0, 1) }}
                        </span>
                        <span class="mt-4 text-xs font-black uppercase tracking-[0.16em] text-brand-navy/45">Edulaw Opportunity</span>
                    </div>
                @endif
            </a>
        </div>

        <div class="flex min-w-0 flex-col p-6 sm:p-8 lg:p-9">
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-[#fff4d7] px-3 py-1 text-[11px] font-black uppercase tracking-[0.11em] text-[#80500a]">
                    {{ $opportunity->display_type }}
                </span>
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-black uppercase tracking-[0.1em] text-emerald-700">
                    Masih Dibuka
                </span>
            </div>

            <h2 class="mt-4 max-w-3xl text-2xl font-black leading-tight tracking-[-0.02em] text-brand-ink sm:text-3xl">
                <a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="rounded-sm transition hover:text-brand-navy focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy">
                    {{ $opportunity->title }}
                </a>
            </h2>

            @if ($summary)
                <p class="mt-3 line-clamp-5 max-w-none text-sm leading-6 text-slate-600 sm:text-[15px]">
                    {{ $summary }}
                </p>
            @endif

            @if ($opportunity->organizer || $opportunity->target_audience)
                <p class="mt-3 text-sm font-bold text-slate-500">
                    @if ($opportunity->organizer){{ $opportunity->organizer }}@endif
                    @if ($opportunity->organizer && $opportunity->target_audience)<span aria-hidden="true"> · </span>@endif
                    @if ($opportunity->target_audience)Target: {{ $opportunity->target_audience }}@endif
                </p>
            @endif

            <div class="mt-auto grid gap-4 border-y border-slate-100 py-5 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
                <dl class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <dt class="text-[11px] font-black uppercase tracking-[0.11em] text-slate-500">Deadline</dt>
                        <dd class="mt-1 text-sm font-black text-brand-ink">{{ $opportunity->deadline_display }}</dd>
                        <dd class="mt-0.5 text-xs font-bold text-[#a56408]">{{ $opportunity->deadline_relative_label }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-black uppercase tracking-[0.11em] text-slate-500">Format</dt>
                        <dd class="mt-1 text-sm font-black text-brand-ink">{{ $opportunity->display_format }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-black uppercase tracking-[0.11em] text-slate-500">Lokasi</dt>
                        <dd class="mt-1 line-clamp-2 text-sm font-black text-brand-ink">{{ $opportunity->location ?: 'Menyesuaikan' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-black uppercase tracking-[0.11em] text-slate-500">Status</dt>
                        <dd class="mt-1 text-sm font-black text-emerald-700">Masih Dibuka</dd>
                    </div>
                </dl>

                <div class="flex justify-start lg:justify-end">
                    <a
                        href="{{ $officialUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-brand-navy px-5 py-3 text-sm font-black text-white transition hover:bg-brand-ink focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy sm:w-auto"
                        aria-label="Lihat informasi resmi {{ $opportunity->title }}"
                    >
                        Lihat Informasi Resmi
                        <svg class="h-4 w-4 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M7 17 17 7M8 7h9v9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</article>
