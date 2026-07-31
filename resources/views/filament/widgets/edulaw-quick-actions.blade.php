<x-filament-widgets::widget>
    <section class="edulaw-performance-hero">
        <div class="edulaw-performance-hero-glow" aria-hidden="true"></div>
        <div class="edulaw-performance-hero-layout relative z-10 grid gap-6 p-5 sm:p-6 lg:grid-cols-[minmax(0,1fr)_minmax(12rem,15rem)] lg:items-end lg:p-7 xl:grid-cols-[minmax(0,1fr)_minmax(14rem,17rem)]">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="edulaw-performance-kicker">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        Control Center
                    </span>
                    <span class="text-xs font-bold text-white/55">{{ $dateLabel }}</span>
                </div>

                <h2 class="mt-4 max-w-3xl text-2xl font-black tracking-tight text-white sm:text-3xl">
                    Selamat datang, {{ $displayName }}.
                </h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                    Gunakan ringkasan di bawah untuk membaca kesehatan website, memantau audiens, dan menyelesaikan pekerjaan editorial yang paling mendesak.
                </p>

                <a href="{{ $websiteUrl }}" target="_blank" rel="noopener" class="mt-5 inline-flex items-center gap-2 text-xs font-black text-blue-200 transition hover:text-white">
                    Lihat website publik
                    <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="h-4 w-4" />
                </a>
            </div>

            <div class="edulaw-performance-actions lg:justify-self-end">
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
                        Editorial Baru
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
    </section>
</x-filament-widgets::widget>
