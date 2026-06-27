@props([
    'programs' => collect(),
])

@php
    $programCollection = collect($programs)->take(3)->values();

    $fallbackImages = [
        'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=1200&q=85',
        'https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=1200&q=85',
        'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1200&q=85',
    ];

    $resolveImagePath = function ($path) {
        return edulaw_file_url($path);
    };

    $resolveImage = function ($program, $fallback) use ($resolveImagePath) {
        $image = collect([
            data_get($program, 'poster_url'),
            data_get($program, 'poster_image_url'),
            data_get($program, 'cover_image_url'),
            data_get($program, 'image_url'),
            data_get($program, 'poster'),
            data_get($program, 'poster_image'),
            data_get($program, 'cover_image'),
            data_get($program, 'image'),
            data_get($program, 'thumbnail'),
            data_get($program, 'og_image'),
        ])->first(fn ($path) => filled($path));

        return $resolveImagePath($image) ?? $fallback;
    };
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

            <a
                href="{{ route('programs.index') }}"
                class="inline-flex w-fit shrink-0 items-center gap-2 pt-1 text-sm font-extrabold text-brand-ink transition hover:text-brand-navy"
            >
                Lihat Semua Program
                <svg class="h-4 w-4 transition" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>

        <div class="mt-6 grid auto-rows-fr gap-5 lg:grid-cols-3">
            @forelse ($programCollection as $program)
                @php
                    $fallbackIndex = $loop->index % count($fallbackImages);
                    $image = $resolveImage($program, $fallbackImages[$fallbackIndex]);

                    $category = $program->display_category ?? $program->category?->name ?? 'Program';
                    $format = $program->display_format ?? $program->format ?? '-';
                    $level = $program->level ?? 'Umum';
                    $audience = $program->audience ?? 'Terbuka';
                    $eventDate = $program->event_date ?? $program->starts_at ?? null;
                @endphp

                <article class="group h-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-brand-ink/10">
                    <a href="{{ route('programs.show', $program->slug) }}" class="flex h-full flex-col">
                        {{-- Image --}}
                        <div class="relative h-63.75 overflow-hidden bg-brand-navy">
                            <img
                                src="{{ $image }}"
                                alt="{{ $program->name }}"
                                class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                                loading="lazy"
                            >

                            <div class="absolute inset-0 bg-linear-to-t from-brand-navy/86 via-brand-navy/26 to-transparent"></div>
                            <div class="absolute inset-0 bg-linear-to-r from-brand-navy/35 via-transparent to-transparent"></div>

                            <div class="absolute left-4 top-4 flex flex-wrap items-center gap-2">
                                <span class="inline-flex rounded-md bg-brand-amber px-3 py-1 text-[10px] font-black uppercase tracking-[0.13em] text-brand-black shadow-sm">
                                    {{ $category }}
                                </span>

                                @if ($eventDate)
                                    <span class="inline-flex rounded-md bg-white/90 px-3 py-1 text-[10px] font-black uppercase tracking-[0.13em] text-brand-ink shadow-sm backdrop-blur">
                                        {{ optional($eventDate)->translatedFormat('d M Y') }}
                                    </span>
                                @endif
                            </div>

                            <div class="absolute bottom-4 left-4 right-4">
                                <h3 class="line-clamp-2 text-[1.55rem] font-black leading-tight tracking-tight text-white">
                                    {{ $program->name }}
                                </h3>
                            </div>
                        </div>

                        {{-- Body --}}
                        <div class="flex flex-1 flex-col p-4">
                            <p class="line-clamp-3 text-[15px] leading-6 text-slate-600">
                                {{ $program->short_description }}
                            </p>

                            <div class="mt-4 grid grid-cols-3 gap-3 border-y border-slate-100 py-3">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                                        Format
                                    </p>

                                    <p class="mt-1 line-clamp-1 text-sm font-extrabold text-brand-ink">
                                        {{ $format }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                                        Level
                                    </p>

                                    <p class="mt-1 line-clamp-1 text-sm font-extrabold text-brand-ink">
                                        {{ $level }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                                        Audiens
                                    </p>

                                    <p class="mt-1 line-clamp-1 text-sm font-extrabold text-brand-ink">
                                        {{ \Illuminate\Support\Str::limit($audience, 18) }}
                                    </p>
                                </div>
                            </div>

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
                <div class="col-span-full flex min-h-55 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white/80 p-8 text-center shadow-sm">
                    <div>
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-brand-navy text-brand-amber">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 3 4 7l8 4 8-4-8-4Zm-6 8 6 3 6-3M6 15l6 3 6-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>

                        <h3 class="mt-4 text-lg font-black text-brand-ink">
                            Belum ada program dipublikasikan.
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Program yang sudah aktif akan tampil di bagian ini.
                        </p>
                    </div>
                </div>
            @endforelse

            {{-- Cadangan visual jika data kurang dari 3 --}}
            @if ($programCollection->isNotEmpty())
                @for ($i = $programCollection->count(); $i < 3; $i++)
                    <article class="h-full overflow-hidden rounded-xl border border-dashed border-slate-300 bg-white/75 shadow-sm">
                        <div class="flex h-full min-h-107.5col">
                            <div class="relative h-63.75 bg-linear-to-br from-white via-[#FDFBF7] to-slate-100">
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-white text-brand-navy shadow-sm ring-1 ring-slate-200">
                                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M12 6v12M6 12h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-1 flex-col p-4">
                                <p class="text-[11px] font-black uppercase tracking-[0.14em] text-brand-navy">
                                    Program Edulaw
                                </p>

                                <h3 class="mt-2 text-lg font-black leading-tight text-brand-ink">
                                    Program lainnya sedang disiapkan.
                                </h3>

                                <p class="mt-2 text-sm leading-6 text-slate-500">
                                    Kelas, diskusi, atau pelatihan baru akan tampil setelah dipublikasikan.
                                </p>

                                <div class="mt-auto pt-4">
                                    <span class="inline-flex items-center gap-2 text-sm font-black text-brand-navy">
                                        Segera hadir
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </article>
                @endfor
            @endif
        </div>
    </div>
</section>
