@props([
    'program' => null,
])

@if ($program)
    @php
        $detailUrl = \Illuminate\Support\Facades\Route::has('programs.show')
            ? route('programs.show', $program->slug)
            : url('/program/'.$program->slug);

        $image = edulaw_file_url($program->image ?? null);
        $title = $program->display_title ?? $program->name ?? 'Program Edulaw';
        $category = $program->display_category ?? $program->categoryRelation?->name ?? 'Program';
        $date = $program->event_date ? $program->event_date->translatedFormat('d M Y') : 'Tanggal arsip';
    @endphp

    <article class="group flex h-[330px] flex-col overflow-hidden rounded-[18px] border border-slate-200 bg-white shadow-[0_14px_34px_rgba(15,23,42,0.05)] transition duration-200 hover:-translate-y-1 hover:shadow-[0_20px_50px_rgba(15,23,42,0.10)]">
        <a href="{{ $detailUrl }}" class="relative block h-[135px] shrink-0 overflow-hidden bg-slate-200">
            @if ($image)
                <img
                    src="{{ $image }}"
                    alt="{{ $title }}"
                    class="absolute inset-0 z-10 h-full w-full object-cover transition duration-200 group-hover:scale-105"
                    loading="lazy"
                    onerror="this.classList.add('hidden'); this.nextElementSibling?.classList.remove('hidden');"
                >
                <div class="absolute inset-0 hidden h-full w-full bg-linear-to-br from-brand-navy via-[#224C7D] to-slate-500"></div>
            @else
                <div class="absolute inset-0 h-full w-full bg-linear-to-br from-brand-navy via-[#224C7D] to-slate-500"></div>
            @endif

            <span class="absolute left-3 top-3 z-20 rounded-full bg-white/92 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.16em] text-brand-navy shadow-sm backdrop-blur">
                Arsip
            </span>
        </a>

        <div class="flex flex-1 flex-col p-4">
            <h3 class="line-clamp-2 text-sm font-black leading-snug tracking-normal text-brand-ink">
                <a href="{{ $detailUrl }}" class="transition hover:text-brand-navy">{{ $title }}</a>
            </h3>

            <div class="mt-3 space-y-2 text-xs font-bold text-slate-600">
                <span class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-brand-navy" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M8 2v4m8-4v4M3 10h18M5 5h14a2 2 0 0 1 2 2v12a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ $date }}
                </span>
                <span class="inline-flex rounded-full bg-[#EAF2FF] px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-brand-navy">
                    {{ $category }}
                </span>
            </div>

            <div class="mt-auto pt-5">
                <a href="{{ $detailUrl }}" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-brand-navy/20 bg-white px-3 py-2 text-xs font-black text-brand-navy transition hover:border-brand-navy hover:bg-brand-navy hover:text-white">
                    Lihat Arsip
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        </div>
    </article>
@endif
