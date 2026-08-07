@props(['opportunities' => collect()])

@php($opportunityCollection = collect($opportunities)->take(3)->values())

@if ($opportunityCollection->isNotEmpty())
    <section id="opportunities" class="scroll-mt-20 bg-white pb-9 lg:pb-12" aria-labelledby="home-opportunities-title">
        <div class="section-shell">
            <div class="flex items-end justify-between gap-5">
                <div>
                    <p class="home-section-eyebrow text-[#e57b66]">Peluang Terbuka</p>
                    <h2 id="home-opportunities-title" class="home-section-title">Ruang untuk Tumbuh dan Berkontribusi</h2>
                </div>
                <a href="{{ route('opportunities.index') }}" class="home-section-link">Semua Peluang →</a>
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
                        <h3 class="home-card-title mt-4 line-clamp-2 min-h-12">{{ $opportunity->title }}</h3>
                        <p class="home-card-meta">{{ $opportunity->deadline?->isPast() ? 'Tenggat telah lewat' : ($opportunity->deadline ? 'Batas akhir '.$opportunity->deadline->translatedFormat('d M Y') : 'Tenggat fleksibel') }}</p>
                        <a href="{{ $opportunity->application_link }}" target="_blank" rel="noopener noreferrer" class="home-card-action mt-auto pt-4" aria-label="Buka peluang {{ $opportunity->title }} di situs eksternal">Lihat Peluang ↗</a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
