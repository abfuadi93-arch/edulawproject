@props(['block' => null])

@php
    $meta = (array) ($block?->meta ?? []);
    $eyebrow = $block?->eyebrow ?? 'Kolaborasi';
    $title = $block?->title ?? 'Bangun ruang literasi hukum bersama Edulaw Project.';
    $body = $block?->body ?? 'Kami membuka ruang kolaborasi dengan kampus, komunitas, lembaga, dan mitra strategis untuk mengembangkan edukasi hukum yang inklusif dan berdampak.';
    $primaryUrl = $block?->resolved_url ?? route('collaboration.index');
    $primaryLabel = $block?->url_label ?? 'Ajukan Kerja Sama';
    $secondaryUrl = \App\Support\EdulawSite::resolveUrl($meta['secondary_url'] ?? null, route('programs.index'));
    $secondaryLabel = $meta['secondary_label'] ?? 'Lihat Program';
@endphp

<section class="bg-[#f4f6f8] pb-10 pt-2 lg:pb-12">
    <div class="section-shell">
        <div class="relative overflow-hidden rounded-2xl bg-[linear-gradient(105deg,#173b68,#245485_65%,#2c5273)] shadow-[0_24px_70px_-35px_rgba(15,23,42,0.6)]">
            <div class="pointer-events-none absolute -right-14 -top-20 size-72 rounded-full bg-white/10 blur-2xl"></div>
            <div class="grid gap-7 px-6 py-9 text-center sm:px-10 lg:grid-cols-[1fr_auto] lg:items-center lg:px-12 lg:py-11 lg:text-left">
                <div>
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.18em] text-[#f0c55e]">{{ $eyebrow }}</p>
                    <h2 class="mt-2 max-w-2xl font-display text-2xl font-extrabold leading-tight text-white sm:text-3xl">{{ $title }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-200">{{ $body }}</p>
                </div>
                <div class="flex flex-col gap-3">
                    <a href="{{ $primaryUrl }}" class="inline-flex min-w-48 min-h-11 items-center justify-center gap-3 rounded-lg bg-[#f8bd38] px-6 py-3 text-xs font-extrabold text-[#142f57] shadow-sm transition hover:bg-[#ffd263]">{{ $primaryLabel }} <span aria-hidden="true">→</span></a>
                    <a href="{{ $secondaryUrl }}" class="inline-flex min-w-48 min-h-11 items-center justify-center rounded-lg border border-white/20 bg-white/10 px-6 py-3 text-xs font-extrabold text-white transition hover:bg-white/15">{{ $secondaryLabel }} →</a>
                </div>
            </div>
        </div>
    </div>
</section>
