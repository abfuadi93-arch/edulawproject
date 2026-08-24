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
        $excerpt = \Illuminate\Support\Str::limit($program->display_description ?? $program->short_description ?? '', 140);
        $category = $program->display_category ?? $program->categoryRelation?->name ?? 'Program';
        $statusLabel = match ($program->status) {
            'upcoming' => 'Akan Datang',
            'ongoing' => 'Program Aktif',
            default => 'Program',
        };
        $statusClass = $program->status === 'ongoing'
            ? 'bg-[#DFF7EF] text-[#087B65]'
            : 'bg-[#FFF1CF] text-[#9A640B]';
        $date = $program->event_date ? $program->event_date->translatedFormat('d M Y') : 'Tanggal menyusul';
        $format = $program->display_format ?? \Illuminate\Support\Str::headline((string) ($program->format ?? 'Fleksibel'));
        $level = $program->display_level ?? \Illuminate\Support\Str::headline((string) ($program->level ?? 'Umum'));
    @endphp

    <article class="group flex h-full min-w-0 flex-col overflow-hidden rounded-xl border border-[#e1e7e6] bg-white transition duration-200 hover:border-brand-navy/25">
        <a href="{{ $detailUrl }}" class="relative block aspect-[16/10] shrink-0 overflow-hidden bg-slate-200">
            @if ($image)
                <img
                    src="{{ $image }}"
                    alt="{{ $title }}"
                    class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]"
                    loading="lazy"
                >
            @else
                <div class="h-full w-full bg-linear-to-br from-brand-navy via-[#224C7D] to-brand-teal"></div>
            @endif

            <div class="absolute left-3 top-3 flex flex-wrap gap-2">
                <span class="rounded-full bg-white/92 px-2.5 py-1 text-[11px] font-black uppercase tracking-[0.11em] text-brand-navy shadow-sm backdrop-blur">
                    {{ $category }}
                </span>
                <span class="rounded-full px-2.5 py-1 text-[11px] font-black uppercase tracking-[0.11em] shadow-sm {{ $statusClass }}">
                    {{ $statusLabel }}
                </span>
            </div>
        </a>

        <div class="flex flex-1 flex-col p-4">
            <h3 class="line-clamp-2 text-base font-black leading-snug tracking-normal text-brand-ink">
                <a href="{{ $detailUrl }}" class="transition hover:text-brand-navy">
                    {{ $title }}
                </a>
            </h3>

            <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">
                {{ $excerpt }}
            </p>

            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs font-black text-brand-navy">
                <span class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M8 2v4m8-4v4M3 10h18M5 5h14a2 2 0 0 1 2 2v12a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ $date }}
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 20h9M3 4h18M5 4v16h4V4m6 0v16h4V4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ $format }}
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 3 3 8l9 5 9-5-9-5Zm-7 8v5l7 4 7-4v-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ $level }}
                </span>
            </div>

            <div class="mt-auto pt-3">
                <a href="{{ $detailUrl }}" class="inline-flex items-center gap-2 text-sm font-black text-brand-navy transition hover:text-brand-teal">
                    Lihat Detail
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        </div>
    </article>
@endif
