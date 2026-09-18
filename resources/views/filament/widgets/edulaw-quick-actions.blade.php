<x-filament-widgets::widget>
    <section class="edulaw-performance-hero">
        <div class="edulaw-performance-hero-glow" aria-hidden="true"></div>
        <div class="edulaw-performance-hero-layout">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="edulaw-performance-kicker">
                        Ruang Kerja Edulaw
                    </span>
                    <span class="edulaw-dashboard-role">{{ $roleLabel }}</span>
                </div>

                <h1 class="edulaw-dashboard-greeting">
                    Selamat datang, {{ $displayName }}.
                </h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                    Semua aktivitas editorial dalam satu tempat. Lanjutkan pekerjaan dan pantau perkembangan konten Anda.
                </p>

                <a href="{{ $websiteUrl }}" target="_blank" rel="noopener" class="mt-5 inline-flex items-center gap-2 text-xs font-black text-amber-200 transition hover:text-white">
                    Lihat website publik
                    <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="h-4 w-4" />
                </a>
            </div>

            <div class="edulaw-dashboard-tools">
                <p class="edulaw-dashboard-date"><x-filament::icon icon="heroicon-o-calendar-days" class="h-4 w-4" /> {{ $dateLabel }}</p>
                <div class="edulaw-performance-actions">
                    <button
                        type="button"
                        class="edulaw-performance-action edulaw-performance-action-search"
                        x-on:click="document.querySelector('.fi-global-search input')?.focus()"
                    >
                        <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-4 w-4" />
                        Cari Konten
                    </button>

                    @if ($canCreateInsight)
                        <a href="{{ $insightCreateUrl }}" class="edulaw-performance-action edulaw-performance-action-primary">
                            <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
                            Tulis Insight
                        </a>
                    @endif

                    @if ($canCreatePublication)
                        <a href="{{ $publicationCreateUrl }}" class="edulaw-performance-action edulaw-performance-action-publication">
                            <x-filament::icon icon="heroicon-o-document-plus" class="h-4 w-4" />
                            Publikasi Baru
                        </a>
                    @endif

                    @if ($canCreateProgram)
                        <a href="{{ $programCreateUrl }}" class="edulaw-performance-action edulaw-performance-action-program">
                            <x-filament::icon icon="heroicon-o-academic-cap" class="h-4 w-4" />
                            Program Baru
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>
</x-filament-widgets::widget>
