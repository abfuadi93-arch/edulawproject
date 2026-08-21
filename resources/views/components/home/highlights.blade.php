@props(['program' => null, 'opportunity' => null, 'insight' => null])

<section class="relative z-10 bg-white py-6 lg:py-8" aria-labelledby="home-highlights-title">
    <div class="section-shell">
        <div class="mb-5 flex items-center gap-4">
            <span class="h-px flex-1 bg-slate-200"></span>
            <h2 id="home-highlights-title" class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#1f3c69]">Terbaru</h2>
            <span class="h-px flex-1 bg-slate-200"></span>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <article class="rounded-xl border border-[#dfe7f1] bg-[linear-gradient(135deg,#f8fbff,#eef4ff)] p-5 shadow-[0_15px_35px_-30px_rgba(15,23,42,.7)]">
                <div class="flex gap-4">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-[#e1ebff] text-lg font-black text-[#32689c]" aria-hidden="true">▣</span>
                    <div class="min-w-0">
                        <p class="home-card-kicker text-slate-400">Program Pilihan</p>
                        <h3 class="home-card-title line-clamp-2">{{ $program?->display_title ?? 'Program terbaru sedang disiapkan' }}</h3>
                        @if ($program)
                            <p class="mt-2 text-xs font-bold text-slate-500">{{ $program->event_date?->translatedFormat('d M Y') ?? 'Jadwal segera diumumkan' }}{{ $program->display_format ? ' · '.$program->display_format : '' }}</p>
                            <a href="{{ route('programs.show', $program->slug) }}" class="home-card-action mt-4">Lihat Detail →</a>
                        @endif
                    </div>
                </div>
            </article>

            <article class="rounded-xl border border-[#dceee7] bg-[linear-gradient(135deg,#f7fffb,#edf9f3)] p-5 shadow-[0_15px_35px_-30px_rgba(15,23,42,.7)]">
                <div class="flex gap-4">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-[#dff6eb] text-lg font-black text-[#168565]" aria-hidden="true">↗</span>
                    <div class="min-w-0">
                        <p class="home-card-kicker text-slate-400">Peluang Terbuka</p>
                        <h3 class="home-card-title line-clamp-2">{{ $opportunity?->title ?? 'Peluang terbaru sedang disiapkan' }}</h3>
                        @if ($opportunity)
                            <p class="mt-2 text-xs font-bold text-slate-500">{{ $opportunity->deadline ? 'Batas akhir '.$opportunity->deadline->translatedFormat('d M Y') : 'Tenggat fleksibel' }}</p>
                            <a href="{{ $opportunity->application_link }}" target="_blank" rel="noopener noreferrer" class="home-card-action mt-4">Lihat Peluang ↗</a>
                        @endif
                    </div>
                </div>
            </article>

            <article class="rounded-xl border border-[#f0e4d4] bg-[linear-gradient(135deg,#fffdf8,#fff5e9)] p-5 shadow-[0_15px_35px_-30px_rgba(15,23,42,.7)]">
                <div class="flex gap-4">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-[#fff0d6] text-lg font-black text-[#b18332]" aria-hidden="true">§</span>
                    <div class="min-w-0">
                        <p class="home-card-kicker text-slate-400">Pilihan Editor</p>
                        <h3 class="home-card-title line-clamp-2">{{ $insight?->title ?? 'Editorial pilihan sedang disiapkan' }}</h3>
                        @if ($insight)
                            <p class="mt-2 text-xs font-bold text-slate-500">{{ $insight->display_category }}{{ $insight->reading_time ? ' · '.$insight->reading_time.' menit baca' : '' }}</p>
                            <a href="{{ route('insights.show', $insight->slug) }}" class="home-card-action mt-4">Baca Editorial →</a>
                        @endif
                    </div>
                </div>
            </article>
        </div>
    </div>
</section>
