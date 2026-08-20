@props(['opportunities' => collect()])

@php
    $opportunityCollection = collect($opportunities)->take(4)->values();
    $featuredOpportunity = $opportunityCollection->first();
    $secondaryOpportunities = $opportunityCollection->slice(1, 3)->values();
    $badgeTone = fn (?string $type): string => match ($type) {
        'competition' => 'oppP-gold',
        'fellowship', 'volunteer' => 'oppP-teal',
        'scholarship' => 'oppP-blue',
        'call_for_paper' => 'oppP-violet',
        'internship' => 'oppP-coral',
        'open_collaboration' => 'oppP-navy',
        default => 'oppP-gold',
    };
    $deadlineLabel = fn ($opportunity, bool $short = false): string => $opportunity->deadline?->isPast()
        ? 'Tenggat telah lewat'
        : ($opportunity->deadline ? ($short ? 'Batas akhir' : 'Batas akhir pendaftaran') : 'Jadwal pendaftaran');
    $deadlineValue = fn ($opportunity): string => $opportunity->deadline
        ? $opportunity->deadline->locale('id')->translatedFormat('d F Y')
        : 'Tenggat fleksibel';
@endphp

<section id="opportunities" class="oppP-section" aria-labelledby="home-opportunities-title">
    <div class="section-shell">
        <div class="oppP-header">
            <div class="oppP-copy">
                <p class="home-section-eyebrow text-[#e57b66]">Peluang Terbuka</p>
                <h2 id="home-opportunities-title" class="home-section-title">Ruang untuk Tumbuh dan Berkontribusi</h2>
                <p class="oppP-desc">Beasiswa, kompetisi, fellowship, program pengembangan, dan peluang kolaborasi pilihan untuk memperluas pengalaman dan jejaring.</p>
            </div>

            <a href="{{ route('opportunities.index') }}" class="oppP-all">
                Semua Peluang
                <span aria-hidden="true">→</span>
            </a>
        </div>

        @if ($featuredOpportunity)
            <div @class([
                'oppP-layout',
                'oppP-layout-single max-w-4xl' => $secondaryOpportunities->isEmpty(),
            ])>
                <article class="oppP-featured" data-home-opportunity data-home-opportunity-featured>
                    <div class="oppP-main">
                        <div class="oppP-top">
                            <div class="oppP-icon" aria-hidden="true">↗</div>
                            <span class="oppP-badge {{ $badgeTone($featuredOpportunity->type) }}">{{ $featuredOpportunity->display_type }}</span>
                        </div>

                        <div class="oppP-body">
                            <p class="oppP-mini">Peluang terkini</p>
                            <h3>{{ $featuredOpportunity->title }}</h3>

                            <div class="oppP-deadline">
                                <svg class="oppP-cal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path d="M7 3v3M17 3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z" />
                                </svg>
                                <div>
                                    <span>{{ $deadlineLabel($featuredOpportunity) }}</span>
                                    <strong>{{ $deadlineValue($featuredOpportunity) }}</strong>
                                </div>
                            </div>

                            @if ($featuredOpportunity->location || $featuredOpportunity->format)
                                <div class="oppP-context" aria-label="Informasi pelaksanaan">
                                    @if ($featuredOpportunity->location)
                                        <span>{{ $featuredOpportunity->location }}</span>
                                    @endif
                                    @if ($featuredOpportunity->format)
                                        <span>{{ Illuminate\Support\Str::headline($featuredOpportunity->format) }}</span>
                                    @endif
                                </div>
                            @endif

                            <a href="{{ $featuredOpportunity->application_link }}" target="_blank" rel="noopener noreferrer" class="oppP-action" aria-label="Buka peluang {{ $featuredOpportunity->title }} di situs eksternal">
                                Lihat Peluang
                                <span aria-hidden="true">↗</span>
                            </a>
                        </div>
                    </div>

                    <div class="oppP-poster">
                        <div class="oppP-poster-fallback" data-home-opportunity-fallback aria-hidden="true">
                            <span>{{ mb_substr($featuredOpportunity->display_type, 0, 1) }}</span>
                        </div>
                        @if ($featuredOpportunity->poster_url)
                            <img src="{{ $featuredOpportunity->poster_url }}" alt="Poster {{ $featuredOpportunity->title }}" loading="lazy" decoding="async" onerror="this.remove()">
                        @endif
                    </div>
                </article>

                @if ($secondaryOpportunities->isNotEmpty())
                    <div class="oppP-stack">
                        @foreach ($secondaryOpportunities as $opportunity)
                            <article class="oppP-card" data-home-opportunity data-home-opportunity-secondary>
                                <div class="oppP-thumb">
                                    <div class="oppP-poster-fallback" data-home-opportunity-fallback aria-hidden="true">
                                        <span>{{ mb_substr($opportunity->display_type, 0, 1) }}</span>
                                    </div>
                                    @if ($opportunity->poster_url)
                                        <img src="{{ $opportunity->poster_url }}" alt="Poster {{ $opportunity->title }}" loading="lazy" decoding="async" onerror="this.remove()">
                                    @endif
                                </div>

                                <div class="oppP-cardbody">
                                    <div class="oppP-cardtop">
                                        <span class="oppP-badge {{ $badgeTone($opportunity->type) }}">{{ $opportunity->display_type }}</span>
                                        <span class="oppP-arrow" aria-hidden="true">↗</span>
                                    </div>

                                    <h3>{{ $opportunity->title }}</h3>

                                    <div class="oppP-deadline">
                                        <svg class="oppP-cal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path d="M7 3v3M17 3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z" />
                                        </svg>
                                        <div>
                                            <span>{{ $deadlineLabel($opportunity, true) }}</span>
                                            <strong>{{ $deadlineValue($opportunity) }}</strong>
                                        </div>
                                    </div>

                                    <a href="{{ $opportunity->application_link }}" target="_blank" rel="noopener noreferrer" class="oppP-link" aria-label="Buka detail peluang {{ $opportunity->title }} di situs eksternal">
                                        Lihat Peluang
                                        <span aria-hidden="true">↗</span>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>

        @else
            <div class="oppP-empty">
                <div class="oppP-empty-icon" aria-hidden="true">↗</div>
                <h3>Belum ada peluang yang sedang dibuka.</h3>
                <p>Peluang terbaru akan ditampilkan setelah tersedia melalui kanal resmi Edulaw.</p>
                <a href="{{ route('opportunities.index') }}">Lihat arsip peluang →</a>
            </div>
        @endif
    </div>
</section>
