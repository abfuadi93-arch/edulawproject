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
        $excerpt = $program->display_description ?? $program->short_description ?? '';
        $category = $program->display_category ?? $program->categoryRelation?->name ?? 'Program';
        $date = $program->event_date ? $program->event_date->translatedFormat('d M Y') : 'Tanggal menyusul';
        $format = $program->display_format ?? \Illuminate\Support\Str::headline((string) ($program->format ?? 'Fleksibel'));
        $level = $program->display_level ?? \Illuminate\Support\Str::headline((string) ($program->level ?? 'Umum'));
        $location = $program->location ?: 'Lokasi menyusul';
    @endphp

    <section class="bg-white py-10 sm:py-12">
        <div class="mx-auto max-w-[1320px] px-5 sm:px-6 lg:px-8">
            <div class="mb-5 flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.22em] text-brand-navy">
                <span class="text-[#D99A25]">★</span>
                Featured Program
            </div>

            <article class="grid overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-[0_24px_70px_rgba(15,23,42,0.10)] lg:grid-cols-[0.45fr_0.55fr]">
                <a href="{{ $detailUrl }}" class="group relative min-h-[300px] overflow-hidden bg-[#102B4B] sm:min-h-[360px] lg:min-h-full">
                    @if ($image)
                        <img
                            src="{{ $image }}"
                            alt="{{ $title }}"
                            class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                        >
                    @else
                        <div class="absolute inset-0 bg-linear-to-br from-brand-navy via-[#123D68] to-brand-teal"></div>
                    @endif

                    <div class="absolute inset-0 bg-linear-to-t from-[#071426]/55 via-transparent to-transparent"></div>
                </a>

                <div class="flex flex-col justify-center p-6 sm:p-8 lg:p-10">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-[#EAF2FF] px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-brand-navy">
                            {{ $category }}
                        </span>
                        <span class="rounded-full bg-[#DFF7EF] px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-[#087B65]">
                            Featured
                        </span>
                    </div>

                    <h2 class="mt-4 max-w-3xl text-2xl font-black leading-tight tracking-normal text-brand-ink sm:text-3xl lg:text-[2.35rem]">
                        {{ $title }}
                    </h2>

                    <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">
                        {{ $excerpt }}
                    </p>

                    <div class="mt-6 grid gap-3 text-sm font-bold text-slate-600 sm:grid-cols-2">
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 text-brand-navy" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M8 2v4m8-4v4M3 10h18M5 5h14a2 2 0 0 1 2 2v12a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            {{ $date }}
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 text-brand-navy" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 20h9M3 4h18M5 4v16h4V4m6 0v16h4V4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            {{ $format }}
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 text-brand-navy" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 3 3 8l9 5 9-5-9-5Zm-7 8v5l7 4 7-4v-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            {{ $level }}
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 text-brand-navy" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Zm0-8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            {{ $location }}
                        </span>
                    </div>

                    <div class="mt-8">
                        <a href="{{ $detailUrl }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-brand-navy px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-navy/20 transition duration-300 hover:-translate-y-0.5 hover:bg-[#102B4B]">
                            Lihat Detail Program
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </article>
        </div>
    </section>
@endif
