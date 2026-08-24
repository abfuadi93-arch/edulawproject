@props([
    'eyebrow' => 'Kolaborasi',
    'title' => 'Bangun ruang literasi hukum bersama Edulaw Project.',
    'body' => 'Edulaw Project terbuka untuk kerja sama program edukasi hukum, diskusi publik, riset, publikasi, pelatihan, dan pengembangan ekosistem literasi hukum.',
    'primaryUrl' => null,
    'primaryLabel' => 'Ajukan Kerja Sama',
    'secondaryUrl' => null,
    'secondaryLabel' => null,
    'titleClass' => '',
    'headingId' => null,
])

@php
    $primaryUrl = $primaryUrl ?? route('collaboration.index');
    $hasSecondary = filled($secondaryUrl) && filled($secondaryLabel);
    $resolvedHeadingId = $headingId ?: Illuminate\Support\Str::slug($eyebrow).'-cta-title';
    $summary = Illuminate\Support\Str::limit(strip_tags((string) $body), 180);
@endphp

<section
    {{ $attributes->class(['home-surface-paper py-3']) }}
    aria-labelledby="{{ $resolvedHeadingId }}"
>
    <div class="section-shell">
        <div class="rounded-xl bg-[linear-gradient(105deg,#12385f_0%,#155e68_58%,#2f638f_100%)] px-6 py-8 text-white sm:px-8 lg:grid lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center lg:gap-8">
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#f5c451]">{{ $eyebrow }}</p>
                <h2 id="{{ $resolvedHeadingId }}" class="mt-2 text-2xl font-extrabold leading-tight text-white sm:text-3xl {{ $titleClass }}">{{ $title }}</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-200">{{ $summary }}</p>
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row lg:mt-0 lg:flex-col">
                <a href="{{ $primaryUrl }}" class="inline-flex min-h-11 min-w-44 items-center justify-center rounded-lg bg-[#f5c451] px-5 py-3 text-xs font-extrabold text-[#102f56] transition hover:bg-[#ffd670]">
                    {{ $primaryLabel }} <span aria-hidden="true">→</span>
                </a>

                @if ($hasSecondary)
                    <a href="{{ $secondaryUrl }}" class="inline-flex min-h-11 min-w-44 items-center justify-center rounded-lg border border-white/25 bg-white/10 px-5 py-3 text-xs font-extrabold text-white transition hover:bg-white/15">
                        {{ $secondaryLabel }} <span aria-hidden="true">→</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>
