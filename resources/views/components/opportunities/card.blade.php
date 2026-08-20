@props(['opportunity'])

@php
    $summary = $opportunity->excerpt
        ?: Illuminate\Support\Str::limit(strip_tags($opportunity->description ?? ''), 145);
    $isOpen = $opportunity->is_open_for_applications;
@endphp

<article class="group flex h-full min-w-0 flex-col overflow-hidden rounded-[1.25rem] border border-slate-200 bg-white shadow-[0_16px_38px_-34px_rgba(15,23,42,.65)] transition duration-200 hover:-translate-y-0.5 hover:border-[#d9a24c]/65 hover:shadow-[0_22px_44px_-34px_rgba(15,23,42,.5)]" data-opportunity-card>
    <div class="relative aspect-[4/3] overflow-hidden border-b border-slate-100 bg-[#edf1f5]">
        @if ($opportunity->poster_url)
            <img
                src="{{ $opportunity->poster_url }}"
                alt="Poster {{ $opportunity->title }}"
                class="h-full w-full object-contain transition duration-500 group-hover:scale-[1.015]"
                width="640"
                height="480"
                loading="lazy"
                decoding="async"
            >
        @else
            <div class="flex h-full items-center justify-center bg-linear-to-br from-[#e8eef4] to-[#d8e3ec]" aria-hidden="true">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-2xl font-black text-brand-navy/55 shadow-sm">
                    {{ mb_substr($opportunity->display_type, 0, 1) }}
                </span>
            </div>
        @endif

        <span class="absolute left-4 top-4 rounded-full border border-white/80 bg-white/95 px-3 py-1 text-[10px] font-black uppercase tracking-[0.12em] text-brand-navy shadow-sm backdrop-blur">
            {{ $opportunity->display_type }}
        </span>
    </div>

    <div class="flex flex-1 flex-col p-5">
        <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-slate-500">
            <span>{{ $opportunity->display_format }}</span>
            <span class="text-slate-300" aria-hidden="true">•</span>
            <span class="line-clamp-1">{{ $opportunity->location ?: 'Lokasi menyesuaikan' }}</span>
        </div>

        <h2 class="mt-3 line-clamp-3 text-lg font-black leading-snug tracking-[-0.015em] text-brand-ink transition group-hover:text-brand-navy">
            {{ $opportunity->title }}
        </h2>

        @if ($summary)
            <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-500">{{ $summary }}</p>
        @endif

        <div class="mt-5 border-l-2 border-brand-amber pl-3">
            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Deadline</p>
            <p class="mt-1 text-base font-black text-brand-ink">{{ $opportunity->deadline_display }}</p>
            <p class="mt-0.5 text-xs font-black {{ $isOpen ? 'text-[#a56408]' : 'text-slate-500' }}">
                {{ $isOpen ? $opportunity->deadline_relative_label : 'Pendaftaran ditutup' }}
            </p>
        </div>

        <div class="mt-auto flex items-end justify-between gap-4 pt-5">
            <span class="inline-flex rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-[0.1em] {{ $isOpen ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                {{ $isOpen ? 'Masih Dibuka' : $opportunity->display_status }}
            </span>

            <a
                href="{{ $opportunity->application_link }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1.5 text-sm font-black text-brand-navy underline-offset-4 transition hover:text-[#9a610c] hover:underline focus-visible:rounded focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-navy"
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
