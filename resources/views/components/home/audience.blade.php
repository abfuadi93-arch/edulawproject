@props(['intro' => null, 'audiences' => collect()])

@php
    $introEyebrow = $intro?->eyebrow ?? 'Siapa yang Kami Layani';
    $introTitle = $intro?->title ?? 'Ruang literasi hukum untuk berbagai kebutuhan.';

    $fallbackAudiences = [
        [
            'title' => 'Mahasiswa',
            'description' => 'Akses hukum untuk penelitian, penulisan, dan pengembangan diri.',
            'icon' => 'cap',
            'accent' => 'bg-brand-amber text-brand-black',
        ],
        [
            'title' => 'Profesional Hukum',
            'description' => 'Perbarui keahlian, temukan perspektif baru.',
            'icon' => 'briefcase',
            'accent' => 'bg-brand-teal text-brand-ink',
        ],
        [
            'title' => 'Masyarakat',
            'description' => 'Pahami hak dan kewajiban, ambil bagian dalam perubahan.',
            'icon' => 'users',
            'accent' => 'bg-brand-coral text-brand-ink',
        ],
        [
            'title' => 'Pembuat Kebijakan',
            'description' => 'Dapatkan data dan perspektif hukum yang kredibel.',
            'icon' => 'building',
            'accent' => 'bg-brand-sky text-white',
        ],
    ];

    $audienceCards = ($audiences instanceof \Illuminate\Support\Collection && $audiences->isNotEmpty())
        ? $audiences->map(fn ($audience) => [
            'title' => $audience->title,
            'description' => $audience->body,
            'icon' => $audience->icon ?: 'users',
            'accent' => $audience->accent ?: 'bg-brand-amber text-brand-black',
        ])->all()
        : $fallbackAudiences;
@endphp

<section class="bg-white py-6 lg:py-7">
    <div class="section-shell relative">
        <div class="grid gap-4 lg:grid-cols-[0.6fr_1.4fr] lg:items-stretch">
            {{-- Left text --}}
            <div class="flex flex-col justify-center">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.18em] text-brand-navy">
                    {{ $introEyebrow }}
                </p>

                <h2 class="mt-2 text-xl font-extrabold leading-tight tracking-normal text-brand-ink sm:text-2xl">
                    {{ $introTitle }}
                </h2>
            </div>

            {{-- Right cards --}}
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($audienceCards as $audience)
                    <article class="group flex h-full items-center gap-3 rounded-xl border border-brand-ink/10 bg-white p-4 shadow-sm shadow-brand-ink/5 transition duration-300 hover:-translate-y-0.5 hover:border-brand-navy/20 hover:shadow-lg hover:shadow-brand-ink/10">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $audience['accent'] }} shadow-sm transition duration-300 group-hover:scale-105">
                            @if ($audience['icon'] === 'cap')
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M3 8.5 12 4l9 4.5-9 4.5L3 8.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    <path d="M7 11v4.2c0 1.2 2.2 2.3 5 2.3s5-1.1 5-2.3V11M21 9v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            @elseif ($audience['icon'] === 'briefcase')
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M9 7V5.8C9 4.8 9.8 4 10.8 4h2.4C14.2 4 15 4.8 15 5.8V7M5 7h14v12H5V7Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    <path d="M5 12h14M10 12v1.2h4V12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            @elseif ($audience['icon'] === 'users')
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M16 19c0-2.2-1.8-4-4-4s-4 1.8-4 4M12 12a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm6 6c0-1.7-.9-3.2-2.3-4M18 10a2.5 2.5 0 0 0-2-2.45M6 18c0-1.7.9-3.2 2.3-4M6 10a2.5 2.5 0 0 1 2-2.45" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            @else
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M4 20h16M6 20V8l6-4 6 4v12M9 20v-5h6v5M9 10h.01M12 10h.01M15 10h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <h3 class="text-[15px] font-extrabold leading-snug tracking-normal text-brand-ink">
                                {{ $audience['title'] }}
                            </h3>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
