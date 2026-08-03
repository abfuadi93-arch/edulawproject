@php
    $audiences = [
        [
            'title' => 'Mahasiswa & Pembelajar Hukum',
            'description' => 'Materi, program, dan perspektif untuk memperkuat proses belajar hukum.',
            'icon' => 'cap',
            'accent' => 'bg-brand-amber-soft text-brand-navy',
            'link_label' => 'Lihat Program Edulaw',
            'link_url' => '#program-edulaw',
        ],
        [
            'title' => 'Peneliti & Akademisi',
            'description' => 'Riset, publikasi, dan ruang pertukaran gagasan hukum yang relevan.',
            'icon' => 'book',
            'accent' => 'bg-brand-teal-soft text-brand-navy',
            'link_label' => 'Jelajahi Riset & Publikasi',
            'link_url' => '#riset-publikasi',
        ],
        [
            'title' => 'Praktisi & Profesional',
            'description' => 'Insight dan peluang pengembangan kapasitas untuk praktik profesional.',
            'icon' => 'briefcase',
            'accent' => 'bg-brand-sky-soft text-brand-navy',
            'link_label' => 'Baca Edulaw Insight',
            'link_url' => '#edulaw-insight',
        ],
        [
            'title' => 'Masyarakat & Komunitas',
            'description' => 'Pengetahuan hukum yang lebih mudah dipahami untuk kepentingan publik.',
            'icon' => 'users',
            'accent' => 'bg-brand-coral-soft text-brand-ink',
            'link_label' => 'Jelajahi Multimedia',
            'link_url' => '#multimedia',
        ],
    ];
@endphp

<section class="home-section bg-white" aria-labelledby="home-audience-title" data-home-audience>
    <div class="section-shell">
        <div class="home-section-header">
            <div class="home-section-copy">
                <p class="home-section-eyebrow">Audience</p>
                <h2 id="home-audience-title" class="home-section-title">Untuk Siapa Edulaw</h2>
                <p class="home-section-description">
                    Edulaw menghadirkan ruang belajar, riset, dan kolaborasi hukum untuk beragam kebutuhan.
                </p>
            </div>
        </div>

        <div class="mt-7 grid auto-rows-fr gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($audiences as $audience)
                <article class="home-card flex h-full flex-col p-5" data-home-audience-card>
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $audience['accent'] }}" aria-hidden="true">
                        @if ($audience['icon'] === 'cap')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                <path d="M3 8.5 12 4l9 4.5-9 4.5-9-4.5Z M7 11v4.2c0 1.2 2.2 2.3 5 2.3s5-1.1 5-2.3V11 M21 9v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        @elseif ($audience['icon'] === 'book')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H11v16H6.5A2.5 2.5 0 0 0 4 21V5.5Zm16 0A2.5 2.5 0 0 0 17.5 3H13v16h4.5A2.5 2.5 0 0 1 20 21V5.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            </svg>
                        @elseif ($audience['icon'] === 'briefcase')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                <path d="M9 7V5.8C9 4.8 9.8 4 10.8 4h2.4c1 0 1.8.8 1.8 1.8V7 M5 7h14v12H5V7Zm0 5h14 M10 12v1.2h4V12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        @else
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                <path d="M16 19c0-2.2-1.8-4-4-4s-4 1.8-4 4 M12 12a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm6 6c0-1.7-.9-3.2-2.3-4 M6 18c0-1.7.9-3.2 2.3-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        @endif
                    </span>
                    <h3 class="mt-4 text-base font-extrabold leading-snug text-brand-ink">{{ $audience['title'] }}</h3>
                    <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">{{ $audience['description'] }}</p>
                    <a href="{{ $audience['link_url'] }}" class="home-card-link mt-auto inline-flex min-h-11 items-center gap-2 pt-4 text-sm font-extrabold text-brand-navy">
                        {{ $audience['link_label'] }}
                        <span aria-hidden="true">↓</span>
                    </a>
                </article>
            @endforeach
        </div>
    </div>
</section>
