@props([
    'featured' => null,
    'items' => collect(),
])

@if ($featured)
    <section id="multimedia" class="scroll-mt-24 bg-slate-50 py-6 sm:py-10" aria-labelledby="home-multimedia-title">
        <div class="section-shell grid items-center gap-6 lg:grid-cols-2 lg:gap-10">
            <div class="flex min-w-0 flex-col items-start gap-4 lg:order-2">
                <div class="home-section-copy">
                    <p class="home-section-eyebrow">Multimedia</p>
                    <h2 id="home-multimedia-title" class="home-section-title">Belajar Hukum Melalui Beragam Format</h2>
                    <p class="home-section-description">Video, podcast, dan diskusi untuk memperluas wawasan hukum.</p>
                </div>

                <a href="{{ route('multimedia.index') }}" class="home-section-link">Lihat Semua Multimedia →</a>
            </div>

            <div class="min-w-0 lg:order-1">
                <x-multimedia.featured-card :item="$featured" variant="home" />
            </div>
        </div>
    </section>
@endif
