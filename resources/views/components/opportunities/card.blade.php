@props(['opportunity'])

@php
    $summary = $opportunity->excerpt
        ?: Illuminate\Support\Str::limit(strip_tags($opportunity->description ?? ''), 145);
    $isOpen = $opportunity->is_open_for_applications;
@endphp

<article class="group grid h-full min-w-0 grid-cols-[7.5rem_minmax(0,1fr)] overflow-hidden rounded-[1.25rem] border border-slate-200 bg-white shadow-[0_16px_38px_-34px_rgba(15,23,42,.65)] transition duration-200 hover:-translate-y-0.5 hover:border-[#d9a24c]/65 hover:shadow-[0_22px_44px_-34px_rgba(15,23,42,.5)] sm:grid-cols-[10.5rem_minmax(0,1fr)] xl:grid-cols-[12rem_minmax(0,1fr)]" data-opportunity-card>
    <div class="flex min-w-0 items-center justify-center border-r border-slate-100 bg-[#edf1f5] p-2.5 sm:p-3.5">
        <div class="relative aspect-[4/5] w-full overflow-hidden rounded-xl border border-white/80 bg-white shadow-sm">
            @if ($opportunity->poster_url)
                <img
                    src="{{ $opportunity->poster_url }}"
                    alt="Poster {{ $opportunity->title }}"
                    class="h-full w-full object-contain transition duration-500 group-hover:scale-[1.015]"
                    width="640"
                    height="800"
                    loading="lazy"
                    decoding="async"
                >
            @else
                <div class="flex h-full items-center justify-center bg-linear-to-br from-[#e8eef4] to-[#d8e3ec]" aria-hidden="true">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-white text-xl font-black text-brand-navy/55 shadow-sm">
                        {{ mb_substr($opportunity->display_type, 0, 1) }}
                    </span>
                </div>
            @endif
        </div>
    </div>

    <div class="flex min-w-0 flex-1 flex-col p-4 sm:p-5">
        <div class="flex items-start justify-between gap-2">
            <span class="min-w-0 rounded-full bg-[#eef2f7] px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.1em] text-brand-navy">
                {{ $opportunity->display_type }}
            </span>
            <div class="flex min-w-0 items-center justify-end gap-1.5 text-right">
                <span class="shrink-0 text-[11px] font-bold text-slate-500">{{ $opportunity->display_format }}</span>
                <span class="text-slate-300" aria-hidden="true">•</span>
                <span class="line-clamp-1 min-w-0 max-w-24 text-[11px] font-bold text-slate-500 sm:max-w-32">{{ $opportunity->location ?: 'Lokasi menyesuaikan' }}</span>
            </div>
        </div>

        <h2 class="mt-3 line-clamp-3 text-base font-black leading-snug tracking-[-0.015em] text-brand-ink transition group-hover:text-brand-navy sm:text-lg">
            {{ $opportunity->title }}
        </h2>

        @if ($summary)
            <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-500">{{ $summary }}</p>
        @endif

        <div class="mt-auto flex items-end justify-between gap-3 pt-4">
            <div class="min-w-0 border-l-2 border-brand-amber pl-3">
                <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Deadline</p>
                <p class="mt-1 text-base font-black text-brand-ink">{{ $opportunity->deadline_display }}</p>
                <p class="mt-0.5 text-xs font-black {{ $isOpen ? 'text-[#a56408]' : 'text-slate-500' }}">
                    {{ $isOpen ? $opportunity->deadline_relative_label : 'Pendaftaran ditutup' }}
                </p>
            </div>

            <a
                href="{{ $opportunity->application_link }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex shrink-0 items-center gap-1 text-xs font-black text-brand-navy underline-offset-4 transition hover:text-[#9a610c] hover:underline focus-visible:rounded focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-navy sm:gap-1.5 sm:text-sm"
                aria-label="Lihat peluang {{ $opportunity->title }} di situs eksternal"
            >
                Lihat Peluang
                <svg class="h-4 w-4 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M7 17 17 7M8 7h9v9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>
    </div>
</article>
