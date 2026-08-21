@props(['programs' => collect(), 'featuredInsight' => null])

@php
    $programCollection = collect($programs)->take(3)->values();
    $hasProgramShow = \Illuminate\Support\Facades\Route::has('programs.show');
@endphp

<section id="program-edulaw" class="scroll-mt-20 bg-[#fbfaf7] py-9 lg:py-12" aria-labelledby="home-programs-title">
    <div class="section-shell">
        <div class="grid gap-8 xl:grid-cols-[1.55fr_.75fr]">
            <div>
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="home-section-eyebrow text-[#b18332]">Program Edulaw</p>
                        <h2 id="home-programs-title" class="home-section-title">Belajar Hukum secara Kontekstual</h2>
                    </div>
                    <a href="{{ route('programs.index') }}" class="home-section-link hidden sm:inline-flex">Semua Program →</a>
                </div>

                <div class="mt-7 grid gap-4 md:grid-cols-3">
                    @forelse ($hasProgramShow ? $programCollection : collect() as $program)
                        @php
                            $image = $program->hero_image_url ?: $program->image_url;
                            $eventDate = $program->event_date ?? $program->starts_at;
                            $format = $program->display_format ?: $program->location;
                        @endphp
                        <article data-home-program class="group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_16px_36px_-30px_rgba(15,23,42,.7)] transition hover:-translate-y-0.5 hover:shadow-lg">
                            <a href="{{ route('programs.show', $program->slug) }}" class="block h-full focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                                <div class="relative h-40 overflow-hidden bg-[#234777]">
                                    @if ($image)
                                        <img src="{{ $image }}" alt="Poster {{ $program->display_title }}" width="640" height="400" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" decoding="async" onerror="this.remove()">
                                    @endif
                                    <div class="absolute inset-0 bg-linear-to-t from-[#17375f]/80 to-transparent"></div>
                                    <div class="absolute inset-x-3 top-3 flex items-center justify-between gap-2">
                                        <span class="max-w-32 truncate rounded bg-[#17375f]/90 px-2 py-1 text-[10px] font-extrabold uppercase tracking-wider text-white">{{ $program->categoryRelation?->name ?? 'Program' }}</span>
                                        <span class="rounded-full bg-[#35c4a0] px-2 py-1 text-[10px] font-extrabold text-[#102b50]">{{ $program->status === 'ongoing' ? 'Berlangsung' : 'Tersedia' }}</span>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <h3 class="home-card-title line-clamp-2 min-h-12">{{ $program->display_title }}</h3>
                                    <div class="home-card-meta flex flex-wrap gap-x-3 gap-y-1">
                                        @if ($eventDate)<span>{{ $eventDate->translatedFormat('d M Y') }}</span>@endif
                                        @if ($eventDate && $format)<span>·</span>@endif
                                        @if ($format)<span>{{ $format }}</span>@endif
                                    </div>
                                    <span class="home-card-action mt-4">Lihat Detail →</span>
                                </div>
                            </a>
                        </article>
                    @empty
                        <div class="home-empty-state col-span-full py-4"><p class="text-sm leading-6 text-slate-600">Program terbaru sedang disiapkan.</p></div>
                    @endforelse
                </div>
            </div>

            <div>
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="home-section-eyebrow text-[#e57b66]">Pilihan Editor</p>
                        <h2 class="home-subsection-title">Perspektif Utama</h2>
                    </div>
                    <a href="{{ route('insights.index') }}" class="home-section-link">Editorial →</a>
                </div>

                @if ($featuredInsight)
                    <article class="group relative mt-7 flex min-h-[338px] items-end overflow-hidden rounded-xl bg-[#142f57] p-6 text-white shadow-[0_22px_48px_-34px_rgba(15,23,42,.9)]">
                        @if ($featuredInsight->cover_image_url)
                            <img src="{{ $featuredInsight->cover_image_url }}" alt="Sampul {{ $featuredInsight->title }}" class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105" loading="lazy">
                        @endif
                        <div class="absolute inset-0 bg-linear-to-t from-[#07172e]/95 via-[#142f57]/45 to-transparent"></div>
                        <span class="absolute left-5 top-5 rounded bg-[#f8bd38] px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-wider text-[#142f57]">Editor's Pick</span>
                        <div class="relative">
                            <p class="home-card-kicker text-[#f0c55e]">{{ $featuredInsight->display_category }}</p>
                            <h3 class="mt-3 line-clamp-4 text-2xl font-extrabold leading-[1.22] tracking-[-0.015em] text-white">{{ $featuredInsight->title }}</h3>
                            <p class="mt-3 text-[13px] leading-5 text-slate-300">{{ $featuredInsight->display_author }}{{ $featuredInsight->reading_time ? ' · '.$featuredInsight->reading_time.' menit baca' : '' }}</p>
                            <a href="{{ route('insights.show', $featuredInsight->slug) }}" class="mt-5 inline-flex text-[13px] font-extrabold text-white">Baca Editorial →</a>
                        </div>
                    </article>
                @else
                    <div class="mt-7 flex min-h-[338px] items-end rounded-xl bg-[#142f57] p-6 text-white">
                        <div><p class="text-[11px] font-extrabold uppercase tracking-wider text-[#f0c55e]">Editorial Edulaw</p><p class="mt-3 text-xl font-extrabold text-white">Pilihan editor sedang disiapkan.</p></div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
