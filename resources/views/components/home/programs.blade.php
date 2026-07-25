@props([
    'programs' => collect(),
])

@php
    $programCollection = collect($programs)->take(3)->values();
    $hasProgramIndex = \Illuminate\Support\Facades\Route::has('programs.index');
    $hasProgramShow = \Illuminate\Support\Facades\Route::has('programs.show');
@endphp

<section class="bg-[#FBF8F1] py-8 lg:py-10">
    <div class="section-shell">
        {{-- Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-5xl">
                <p class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.18em] text-brand-sky shadow-sm ring-1 ring-slate-200">
                    <span class="h-2 w-2 rounded-full bg-brand-sky"></span>
                    Program Edulaw
                </p>

                <h2 class="mt-1.5 max-w-5xl text-2xl font-black leading-tight tracking-tight text-brand-ink sm:text-3xl lg:text-[2.15rem]">
                    Ruang Belajar dan Pengembangan Kapasitas Hukum
                </h2>

                <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600 sm:text-[15px]">
                    Kelas, diskusi, pelatihan, dan forum pengembangan kapasitas hukum bersama Edulaw Project.
                </p>
            </div>

            @if ($hasProgramIndex)
            <a
                href="{{ route('programs.index') }}"
                class="inline-flex w-fit shrink-0 items-center gap-2 pt-1 text-sm font-extrabold text-brand-ink transition hover:text-brand-navy"
            >
                Lihat Semua Program
                <svg class="h-4 w-4 transition" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            @endif
        </div>

        <div class="mt-6 grid auto-rows-fr gap-5 lg:grid-cols-3">
            @forelse ($hasProgramShow ? $programCollection : collect() as $program)
                @php
                    $image = $program->hero_image_url ?: $program->image_url;
                    $category = $program->categoryRelation?->name;
                    $format = $program->display_format;
                    $level = $program->display_level;
                    $audience = $program->audience;
                    $eventDate = $program->event_date ?? $program->starts_at ?? null;
                @endphp

                <article class="group h-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-brand-ink/10">
                    <a href="{{ route('programs.show', $program->slug) }}" class="flex h-full flex-col">
                        {{-- Image --}}
                        <div class="relative h-[255px] overflow-hidden bg-brand-navy">
                            @if ($image)
                                <img
                                    src="{{ $image }}"
                                    alt="{{ $program->display_title }}"
                                    class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                                    loading="lazy"
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
                                    <span class="inline-flex rounded-md bg-brand-amber px-3 py-1 text-[10px] font-black uppercase tracking-[0.13em] text-brand-black shadow-sm">
                                        {{ $category }}
                                    </span>
                                @endif

                                @if ($eventDate)
                                    <span class="inline-flex rounded-md bg-white/90 px-3 py-1 text-[10px] font-black uppercase tracking-[0.13em] text-brand-ink shadow-sm backdrop-blur">
                                        {{ optional($eventDate)->translatedFormat('d M Y') }}
                                    </span>
                                @endif
                            </div>

                            <div class="absolute bottom-4 left-4 right-4">
                                <h3 class="line-clamp-2 text-[1.55rem] font-black leading-tight tracking-tight text-white">
                                    {{ $program->display_title }}
                                </h3>
                            </div>
                        </div>

                        {{-- Body --}}
                        <div class="flex flex-1 flex-col p-4">
                            @if ($program->display_description)
                                <p class="line-clamp-3 text-[15px] leading-6 text-slate-600">
                                    {{ $program->display_description }}
                                </p>
                            @endif

                            @if ($format || $level || $audience)
                            <div class="mt-4 grid grid-flow-col auto-cols-fr gap-3 border-y border-slate-100 py-3">
                                @if ($format)
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                                        Format
                                    </p>

                                    <p class="mt-1 line-clamp-1 text-sm font-extrabold text-brand-ink">
                                        {{ $format }}
                                    </p>
                                </div>
                                @endif

                                @if ($level)
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                                        Level
                                    </p>

                                    <p class="mt-1 line-clamp-1 text-sm font-extrabold text-brand-ink">
                                        {{ $level }}
                                    </p>
                                </div>
                                @endif

                                @if ($audience)
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                                        Audiens
                                    </p>

                                    <p class="mt-1 line-clamp-1 text-sm font-extrabold text-brand-ink">
                                        {{ \Illuminate\Support\Str::limit($audience, 18) }}
                                    </p>
                                </div>
                                @endif
                            </div>
                            @endif

                            <div class="mt-auto flex items-center justify-between gap-4 pt-4">
                                <span class="text-sm font-black text-brand-ink">
                                    Lihat Detail
                                </span>

                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-brand-black text-white transition group-hover:bg-brand-amber group-hover:text-brand-black">
                                    <svg class="h-4 w-4 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-5 text-center shadow-sm">
                    <p class="text-sm leading-6 text-slate-600">
                        Belum ada program yang ditampilkan. Nantikan program terbaru dari Edulaw Project.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</section>
