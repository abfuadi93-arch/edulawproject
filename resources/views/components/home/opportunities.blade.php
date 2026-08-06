@props(['opportunities' => collect()])

@php($opportunityCollection = collect($opportunities)->take(3)->values())

@if ($opportunityCollection->isNotEmpty())
    <section id="opportunities" class="scroll-mt-20 bg-white pb-9 lg:pb-12" aria-labelledby="home-opportunities-title">
        <div class="section-shell">
            <div class="flex items-end justify-between gap-5">
                <div>
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.18em] text-[#e57b66]">Peluang Terbuka</p>
                    <h2 id="home-opportunities-title" class="mt-2 font-display text-2xl font-extrabold tracking-tight text-[#1f3c69]">Ruang untuk Tumbuh dan Berkontribusi</h2>
                </div>
                <a href="{{ route('opportunities.index') }}" class="text-xs font-extrabold text-[#1f3c69]">Semua Peluang →</a>
            </div>

            <div @class([
                'mt-6 grid overflow-hidden rounded-xl border border-slate-200 bg-[#f8fafc]',
                'max-w-4xl' => $opportunityCollection->count() === 1,
                'sm:grid-cols-2 lg:grid-cols-3' => $opportunityCollection->count() > 1,
            ])>
                @foreach ($opportunityCollection as $opportunity)
                    <article class="flex min-h-48 flex-col border-slate-200 p-5 lg:border-r lg:last:border-r-0" data-home-opportunity>
                        <div class="hidden" data-home-opportunity-fallback aria-hidden="true"></div>
                        <span class="w-fit rounded-full bg-[#fde9e3] px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide text-[#9a4f3f]">{{ $opportunity->display_type }}</span>
                        <h3 class="mt-4 line-clamp-2 min-h-11 text-base font-extrabold leading-snug text-[#142f57]">{{ $opportunity->title }}</h3>
                        <p class="mt-3 text-xs font-bold text-slate-400">{{ $opportunity->deadline?->isPast() ? 'Tenggat telah lewat' : ($opportunity->deadline ? 'Batas akhir '.$opportunity->deadline->translatedFormat('d M Y') : 'Tenggat fleksibel') }}</p>
                        <a href="{{ $opportunity->application_link }}" target="_blank" rel="noopener noreferrer" class="mt-auto inline-flex pt-4 text-xs font-extrabold text-[#1f3c69]" aria-label="Buka peluang {{ $opportunity->title }} di situs eksternal">Lihat Peluang ↗</a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
