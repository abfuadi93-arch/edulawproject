@props(['program' => null])

@if ($program)
    @php
        $detailUrl = Route::has('programs.show')
            ? route('programs.show', $program->slug)
            : url('/program/'.$program->slug);
        $image = edulaw_file_url($program->image ?? null);
        $title = $program->display_title ?? $program->name ?? 'Program Edulaw';
        $category = $program->display_category ?? $program->categoryRelation?->name ?? 'Program';
        $date = $program->event_date ? $program->event_date->translatedFormat('d M Y') : 'Tanggal arsip';
    @endphp

    <article data-program-archive-card class="group h-full min-w-0 overflow-hidden rounded-xl border border-[#e1e7e6] bg-white transition hover:border-brand-navy/25">
        <a href="{{ $detailUrl }}" class="block h-full focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
            <div class="relative aspect-[4/3] overflow-hidden bg-slate-200">
                @if ($image)
                    <img src="{{ $image }}" alt="Poster {{ $title }}" width="480" height="360" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]" loading="lazy" decoding="async" onerror="this.remove()">
                @else
                    <div class="h-full w-full bg-linear-to-br from-brand-navy via-[#224C7D] to-brand-teal"></div>
                @endif
            </div>

            <div class="p-4">
                <p class="text-[11px] font-black uppercase tracking-[0.1em] text-brand-teal">{{ $category }}</p>
                <h3 class="mt-2 line-clamp-2 min-h-10 text-sm font-black leading-snug tracking-normal text-brand-ink transition group-hover:text-brand-navy">{{ $title }}</h3>
                <p class="mt-3 text-xs font-bold text-slate-500">{{ $date }}</p>
            </div>
        </a>
    </article>
@endif
