@props(['opportunity'])

@php
    $summary = filled($opportunity->excerpt)
        ? Illuminate\Support\Str::limit(Illuminate\Support\Str::squish(strip_tags($opportunity->excerpt)), 180)
        : null;
    $officialUrl = $opportunity->external_url;
    $additionalUrl = $opportunity->additional_url;
    $additionalLabel = filled($additionalUrl) && filled($opportunity->additional_link_label)
        ? trim($opportunity->additional_link_label)
        : null;
@endphp

<article class="channel-feature-card overflow-hidden rounded-[14px] border border-[#dbe2ea] bg-white" data-featured-opportunity data-channel-feature-card>
    <div class="grid h-full md:grid-cols-[minmax(15rem,.8fr)_minmax(0,1.7fr)] lg:grid-cols-[365px_minmax(0,1fr)]">
        <div class="flex items-center justify-center bg-[#eef2f6] p-4 sm:p-6 lg:p-4">
            <a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="relative block aspect-square w-full max-w-[18rem] overflow-hidden rounded-2xl border border-white/80 bg-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-navy lg:size-[333px] lg:max-w-none" aria-label="Lihat informasi resmi {{ $opportunity->title }}">
                <div class="absolute inset-0 flex flex-col items-center justify-center bg-linear-to-br from-[#e9eef4] to-[#dbe5ed] px-6 text-center text-brand-navy" aria-hidden="true">
                    <span class="grid size-12 place-items-center rounded-xl border border-brand-navy/10 bg-white/70">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h5"/></svg>
                    </span>
                    <span class="mt-4 text-xs font-black uppercase tracking-[0.16em] text-brand-navy/55">Edulaw Opportunity</span>
                </div>
                @if ($opportunity->poster_url)
                    <img
                        src="{{ $opportunity->poster_url }}"
                        alt="Poster {{ $opportunity->title }}"
                        class="relative z-10 h-full w-full object-contain"
                        width="640"
                        height="800"
                        decoding="async"
                        fetchpriority="high"
                        onerror="this.remove()"
                    >
                @endif
            </a>
        </div>

        <div class="flex min-w-0 flex-col justify-center p-6 sm:p-8 lg:p-6">
            <div class="flex flex-wrap items-center gap-2">
                <span class="channel-feature-badge bg-[#fff4d7] text-[#80500a]">
                    {{ $opportunity->display_type }}
                </span>
                <span class="channel-feature-badge bg-emerald-50 text-emerald-700">
                    Masih Dibuka
                </span>
            </div>

            <h2 class="channel-feature-title">
                <a href="{{ $officialUrl }}" target="_blank" rel="noopener noreferrer" class="rounded-sm transition hover:text-brand-navy focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy">
                    {{ $opportunity->title }}
                </a>
            </h2>

            @if ($summary)
                <p class="channel-feature-summary">
                    {{ $summary }}
                </p>
            @endif

            @if ($opportunity->organizer || $opportunity->target_audience)
                <p class="mt-2 line-clamp-1 text-sm font-bold text-slate-500 lg:text-xs">
                    @if ($opportunity->organizer){{ $opportunity->organizer }}@endif
                    @if ($opportunity->organizer && $opportunity->target_audience)<span aria-hidden="true"> · </span>@endif
                    @if ($opportunity->target_audience)Target: {{ $opportunity->target_audience }}@endif
                </p>
            @endif

            <div class="channel-feature-meta border-y border-slate-100 py-3">
                <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <dt class="text-[11px] font-black uppercase tracking-[0.11em] text-slate-500">Deadline</dt>
                        <dd class="mt-1 text-sm font-black text-brand-ink">{{ $opportunity->deadline_display }}</dd>
                        <dd class="mt-0.5 text-xs font-bold text-[#a56408]">{{ $opportunity->deadline_relative_label }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-black uppercase tracking-[0.11em] text-slate-500">Format</dt>
                        <dd class="mt-1 line-clamp-1 text-sm font-black text-brand-ink">{{ $opportunity->display_format }}</dd>
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

                <div class="channel-feature-actions">
                    <a
                        href="{{ $officialUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="channel-feature-secondary-action group text-center focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy"
                        aria-label="Lihat informasi resmi {{ $opportunity->title }}"
                    >
                        Lihat Informasi Resmi
                        <svg class="h-4 w-4 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M7 17 17 7M8 7h9v9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    @if ($additionalLabel)
                        <a
                            href="{{ $additionalUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="channel-feature-primary-action group text-center focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy"
                            aria-label="{{ $additionalLabel }} untuk {{ $opportunity->title }}"
                        >
                            {{ $additionalLabel }}
                            <svg class="h-4 w-4 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M7 17 17 7M8 7h9v9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</article>
