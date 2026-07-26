@props([
    'programs' => collect(),
])

@php
    $programCollection = collect($programs)->take(3)->values();
    $hasProgramIndex = \Illuminate\Support\Facades\Route::has('programs.index');
    $hasProgramShow = \Illuminate\Support\Facades\Route::has('programs.show');
@endphp

<section id="program-edulaw" class="home-section scroll-mt-24 bg-[#FBF8F1]" aria-labelledby="home-programs-title">
    <div class="section-shell">
        {{-- Header --}}
        <div class="home-section-header">
            <div class="home-section-copy">
                <p class="home-section-eyebrow">
                    Program Edulaw
                </p>

                <h2 id="home-programs-title" class="home-section-title">
                    Ruang Belajar dan Pengembangan Kapasitas Hukum
                </h2>

                <p class="home-section-description">
                    Kelas, diskusi, pelatihan, dan forum pengembangan kapasitas hukum bersama Edulaw Project.
                </p>
            </div>

            @if ($hasProgramIndex)
            <a
                href="{{ route('programs.index') }}"
                class="section-link w-fit shrink-0"
            >
                Lihat Semua Program
                <svg class="h-4 w-4 transition" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            @endif
        </div>

        <div class="mt-8 grid auto-rows-fr gap-5 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($hasProgramShow ? $programCollection : collect() as $program)
                @php
                    $image = $program->hero_image_url ?: $program->image_url;
                    $category = $program->categoryRelation?->name;
                    $format = $program->display_format;
                    $eventDate = $program->event_date ?? $program->starts_at ?? null;
                    $endDate = $program->end_date ?? null;
                    $statusLabel = $program->status === 'ongoing' ? 'Ongoing' : 'Upcoming';
                    $dateLabel = $eventDate
                        ? $eventDate->translatedFormat('d M Y')
                        : null;

                    if ($eventDate && $endDate) {
                        $dateLabel = $eventDate->isSameDay($endDate)
                            ? $eventDate->translatedFormat('d M Y')
                            : $eventDate->translatedFormat('d M Y').' – '.$endDate->translatedFormat('d M Y');
                    }

                    $formatLocation = collect([$format, $program->location])
                        ->filter()
                        ->unique()
                        ->join(' · ');
                @endphp

                <article data-home-program class="home-card home-card-interactive group h-full">
                    <a href="{{ route('programs.show', $program->slug) }}" class="home-card-link flex h-full flex-col">
                        {{-- Image --}}
                        <div class="relative aspect-[16/10] overflow-hidden bg-brand-navy">
                            @if ($image)
                                <img
                                    src="{{ $image }}"
                                    alt="Poster {{ $program->display_title }}"
                                    width="1200"
                                    height="800"
                                    class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                                    loading="lazy"
                                    decoding="async"
                                >
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-linear-to-br from-brand-navy via-brand-charcoal to-[#0b6f6b]">
                                    <div class="rounded-2xl border border-white/15 bg-white/10 px-5 py-4 text-center text-white shadow-sm backdrop-blur">
                                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-brand-amber">
                                            Program Edulaw
                                        </p>
                                        <p class="mt-2 text-sm font-semibold text-white/80">
                                            Poster sedang disiapkan
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <div class="absolute inset-0 bg-linear-to-t from-brand-navy/78 via-brand-navy/20 to-transparent"></div>
                            <div class="absolute inset-0 bg-linear-to-r from-brand-navy/28 via-transparent to-transparent"></div>

                            <div class="absolute left-4 top-4 flex flex-wrap items-center gap-2">
                                @if ($category)
                                    <span class="text-xs font-bold text-white drop-shadow">
                                        {{ $category }}
                                    </span>
                                @endif

                                <span class="inline-flex items-center gap-1.5 rounded-md {{ $program->status === 'ongoing' ? 'bg-brand-teal text-brand-black' : 'bg-brand-amber text-brand-black' }} px-2.5 py-1 text-xs font-bold shadow-sm">
                                    @if ($program->status === 'ongoing')
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="m8 5 11 7-11 7V5Z" fill="currentColor"/>
                                        </svg>
                                    @else
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M12 8v4l2.5 1.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    @endif
                                    {{ $statusLabel }}
                                </span>
                            </div>

                            <div class="absolute bottom-4 left-4 right-4">
                                <h3 class="line-clamp-2 text-[1.55rem] font-black leading-tight tracking-tight text-white">
                                    {{ $program->display_title }}
                                </h3>
                            </div>
                        </div>

                        {{-- Body --}}
                        <div class="flex flex-1 flex-col p-5">
                            @if ($program->display_description)
                                <p class="line-clamp-3 text-[15px] leading-6 text-slate-600">
                                    {{ $program->display_description }}
                                </p>
                            @endif

                            @if ($dateLabel || $formatLocation)
                            <div class="mt-4 grid gap-3 border-y border-slate-100 py-3 sm:grid-cols-2">
                                @if ($dateLabel)
                                <div>
                                    <p class="text-xs font-semibold text-slate-500">
                                        Waktu
                                    </p>

                                    <p class="home-meta mt-1 font-bold text-brand-ink">
                                        {{ $dateLabel }}
                                    </p>
                                </div>
                                @endif

                                @if ($formatLocation)
                                <div>
                                    <p class="text-xs font-semibold text-slate-500">
                                        Format / Lokasi
                                    </p>

                                    <p class="home-meta mt-1 line-clamp-2 font-bold text-brand-ink">
                                        {{ $formatLocation }}
                                    </p>
                                </div>
                                @endif
                            </div>
                            @endif

                            <div class="mt-auto flex items-center justify-between gap-4 pt-4">
                                <span class="text-sm font-bold text-brand-navy underline decoration-brand-amber decoration-2 underline-offset-4">
                                    Lihat Program
                                </span>

                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-brand-navy text-white transition group-hover:bg-brand-amber group-hover:text-brand-black">
                                    <svg class="h-4 w-4 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                </article>
            @empty
                <div class="home-empty-state col-span-full">
                    <p class="text-sm leading-6 text-slate-600">
                        Belum ada program yang ditampilkan. Nantikan program terbaru dari Edulaw Project.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</section>
