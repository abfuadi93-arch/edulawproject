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
    'sectionSpacing' => 'py-[9px]',
])

@php
    $primaryUrl = $primaryUrl ?? route('collaboration.index');
    $hasSecondary = filled($secondaryUrl) && filled($secondaryLabel);
    $resolvedHeadingId = $headingId ?: Illuminate\Support\Str::slug($eyebrow).'-cta-title';
    $summary = Illuminate\Support\Str::limit(strip_tags((string) $body), 180);
@endphp

<section
    {{ $attributes->class(['shared-cta-section bg-brand-ice', $sectionSpacing]) }}
    aria-labelledby="{{ $resolvedHeadingId }}"
>
    <div class="section-shell">
        <div class="shared-cta-panel rounded-xl bg-brand-navy px-6 py-8 text-white sm:px-8 lg:grid lg:grid-cols-[minmax(0,4fr)_minmax(0,1fr)] lg:items-center lg:gap-6 lg:px-10 lg:py-9">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-white">{{ $eyebrow }}</p>
                <h2 id="{{ $resolvedHeadingId }}" class="shared-cta-title mt-2 text-2xl font-bold leading-tight text-white sm:text-3xl {{ $titleClass }}">{{ $title }}</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-200">{{ $summary }}</p>
            </div>

            <div class="mt-6 flex min-w-0 flex-col gap-3 sm:flex-row lg:mt-0 lg:flex-col">
                <a href="{{ $primaryUrl }}" class="inline-flex min-h-11 min-w-0 gap-2 items-center justify-center rounded-lg bg-brand-amber px-5 py-3 text-sm font-bold text-brand-navy transition hover:bg-brand-amber/90">
                    {{ $primaryLabel }} <span aria-hidden="true">→</span>
                </a>

                @if ($hasSecondary)
                    <a href="{{ $secondaryUrl }}" class="inline-flex min-h-11 min-w-0 gap-2 items-center justify-center rounded-lg border border-white/25 bg-white/10 px-5 py-3 text-xs font-bold text-white transition hover:bg-white/15">
                        {{ $secondaryLabel }} <span aria-hidden="true">→</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>
