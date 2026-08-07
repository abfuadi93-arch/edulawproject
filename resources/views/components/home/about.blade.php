@props(['stats' => collect()])

@php
    $settings = \App\Support\EdulawSite::settings();
    $siteName = $settings['site.name'] ?? 'Edulaw Project';
    $brandMark = \App\Support\EdulawSite::assetUrl($settings['site.logo'] ?? null, 'images/logo/edulaw-icon.png');
@endphp

<section id="tentang-edulaw" class="scroll-mt-20 bg-[#f4f6f8] py-9 lg:py-12" aria-labelledby="home-about-title">
    <div class="section-shell grid gap-5 lg:grid-cols-[1.05fr_.95fr]">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_18px_42px_-34px_rgba(15,23,42,.7)] sm:p-8">
            <div class="flex items-start justify-between gap-5">
                <div>
                    <p class="home-section-eyebrow text-[#b18332]">Tentang Edulaw</p>
                    <h2 id="home-about-title" class="home-section-title mt-3">Ruang belajar dan riset hukum untuk kepentingan publik.</h2>
                </div>
                @if ($brandMark)
                    <img src="{{ $brandMark }}" alt="Identitas {{ $siteName }}" width="64" height="64" class="hidden size-16 object-contain sm:block" loading="lazy" decoding="async">
                @endif
            </div>
            <p class="mt-5 text-[15px] leading-7 text-slate-500">{{ $siteName }} menghubungkan literasi konstitusi, analisis regulasi, program pembelajaran, riset berbasis data, dan kolaborasi publik dalam satu ekosistem pengetahuan yang terbuka.</p>
            <div class="mt-6 flex flex-wrap gap-2" aria-label="Nilai Edulaw">
                @foreach (['Equal', 'Educative', 'Embrace'] as $value)
                    <span class="rounded-full bg-[#fff2cc] px-3 py-1.5 text-[11px] font-extrabold text-[#8b661d]">{{ $value }}</span>
                @endforeach
            </div>
            <a href="{{ route('about') }}" class="mt-7 inline-flex min-h-11 items-center rounded-lg bg-[#1f3c69] px-5 py-3 text-[13px] font-extrabold text-white transition hover:bg-[#142f57]">Kenali Edulaw →</a>
        </article>

        <article class="overflow-hidden rounded-2xl bg-[#0d315e] text-white shadow-[0_22px_52px_-36px_rgba(15,23,42,.9)]">
            <div class="p-6 sm:p-8">
                <p class="home-section-eyebrow text-[#f0c55e]">Dampak Edulaw</p>
                <h2 class="home-subsection-title mt-3 text-white">Pengetahuan yang terus bertumbuh.</h2>
                <p class="mt-3 text-sm leading-6 text-slate-300">Angka berikut diperbarui langsung dari konten yang telah diterbitkan.</p>
            </div>

            @if ($stats->count() >= 2)
                <dl class="grid grid-cols-2 border-t border-white/10 sm:grid-cols-4 lg:grid-cols-2 xl:grid-cols-4" aria-label="Statistik kredibilitas Edulaw Project">
                    @foreach ($stats->take(4) as $stat)
                        <div class="border-b border-r border-white/10 p-5" data-home-stat="{{ $stat['label'] }}">
                            <dd class="font-display text-3xl font-extrabold text-[#f5c451]">{{ number_format($stat['value'], 0, ',', '.') }}</dd>
                            <dt class="mt-1 text-[10px] font-bold uppercase tracking-wider text-slate-300">{{ $stat['label'] }}</dt>
                        </div>
                    @endforeach
                </dl>
            @else
                <div class="border-t border-white/10 p-6 text-sm leading-7 text-slate-300">Data dampak akan tampil setelah lebih banyak konten diterbitkan.</div>
            @endif
        </article>
    </div>
</section>
