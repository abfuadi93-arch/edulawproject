@props(['publications' => collect()])

@php($publicationCollection = collect($publications)->take(3)->values())

<section id="riset-publikasi" class="scroll-mt-20 bg-[#f6f8fa] py-9 lg:py-12" aria-labelledby="home-publications-title">
    <div class="section-shell">
        <div class="flex items-end justify-between gap-5">
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-[0.18em] text-[#b18332]">Riset &amp; Publikasi Pilihan</p>
                <h2 id="home-publications-title" class="mt-2 font-display text-2xl font-extrabold tracking-tight text-[#1f3c69] sm:text-3xl">Pengetahuan yang Dapat Digunakan</h2>
            </div>
            <a href="{{ route('publications.index') }}" class="text-xs font-extrabold text-[#1f3c69]">Semua Publikasi →</a>
        </div>

        @if ($publicationCollection->isNotEmpty())
            <div class="mt-7 grid gap-4 md:grid-cols-3">
                @foreach ($publicationCollection as $publication)
                    <article data-home-publication class="group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_14px_34px_-28px_rgba(15,23,42,.7)] transition hover:-translate-y-0.5 hover:shadow-lg">
                        <a href="{{ route('publications.show', $publication->slug) }}" class="flex h-full flex-col focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber">
                            <div class="relative aspect-[16/9] overflow-hidden bg-[linear-gradient(135deg,#173b63,#4a8796)]">
                                @if ($publication->cover_image_url)
                                    <img src="{{ $publication->cover_image_url }}" alt="Sampul {{ $publication->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" decoding="async" onerror="this.remove()">
                                @endif
                                <span class="absolute left-3 top-3 rounded bg-white/90 px-2 py-1 text-[10px] font-extrabold uppercase tracking-wider text-[#142f57]">{{ $publication->type?->name ?? 'Dokumen' }}</span>
                            </div>
                            <div class="flex flex-1 flex-col p-4">
                                <h3 class="line-clamp-2 min-h-11 text-base font-extrabold leading-snug text-[#142f57]">{{ $publication->title }}</h3>
                                <p class="mt-3 text-xs text-slate-400">{{ $publication->published_at?->format('Y') ?? 'Publikasi Edulaw' }}{{ $publication->page_count ? ' · '.number_format($publication->page_count, 0, ',', '.').' halaman' : '' }}</p>
                                <span class="mt-auto inline-flex pt-4 text-xs font-extrabold text-[#1f3c69]">Lihat Publikasi →</span>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        @else
            <div class="mt-7 grid overflow-hidden rounded-2xl border border-slate-200 bg-white lg:grid-cols-[1fr_1.2fr]">
                <div class="bg-[#173b63] p-7 text-white sm:p-9">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.18em] text-[#f0c55e]">Repositori Edulaw</p>
                    <h3 class="mt-3 text-2xl font-extrabold text-white">Publikasi sedang disiapkan.</h3>
                    <p class="mt-4 text-sm leading-6 text-slate-200">Kajian, policy brief, naskah akademik, dan modul akan tampil otomatis setelah diterbitkan melalui sistem editorial.</p>
                    <a href="{{ route('publications.index') }}" class="mt-6 inline-flex rounded-lg bg-[#f8bd38] px-4 py-2.5 text-xs font-extrabold text-[#142f57]">Buka Repositori →</a>
                </div>
                <div class="grid gap-3 p-6 sm:grid-cols-2 sm:p-8">
                    @foreach ([
                        ['Policy Brief', 'Rekomendasi ringkas untuk kebijakan.'],
                        ['Laporan Riset', 'Temuan berbasis data dan regulasi.'],
                        ['Kajian Hukum', 'Analisis isu, putusan, dan kelembagaan.'],
                        ['Modul', 'Materi pembelajaran yang dapat digunakan.'],
                    ] as [$title, $description])
                        <article class="rounded-xl border border-slate-200 bg-[#f8fafc] p-4">
                            <span class="text-base font-black text-[#d4a93f]" aria-hidden="true">§</span>
                            <h4 class="mt-3 text-base font-extrabold text-[#1f3c69]">{{ $title }}</h4>
                            <p class="mt-1 text-xs leading-5 text-slate-500">{{ $description }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
