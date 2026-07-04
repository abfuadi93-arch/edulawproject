<x-filament-widgets::widget>
    <section class="edulaw-admin-card overflow-hidden">
        <div class="grid gap-6 bg-gradient-to-r from-blue-50 via-white to-slate-50 p-6 md:grid-cols-[1fr_auto] md:items-center">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.16em] text-blue-600">
                    Admin Dashboard
                </p>
                <h2 class="mt-2 text-xl font-black tracking-tight text-slate-950">
                    Selamat datang kembali, {{ $userName }}.
                </h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 edulaw-admin-muted">
                    Gunakan dashboard ini untuk menerbitkan editorial, mengunggah publikasi, memantau program, dan menjaga kualitas konten Edulaw Project.
                </p>
            </div>

            @if ($canCreateInsight || $canCreatePublication || $canCreateProgram)
                <div class="grid gap-2 sm:min-w-56">
                    @if ($canCreateInsight)
                        <a href="{{ $insightCreateUrl }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-black text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700">
                            <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
                            Tambah Editorial
                        </a>
                    @endif

                    @if ($canCreatePublication)
                        <a href="{{ $publicationCreateUrl }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-950 shadow-sm transition hover:border-blue-200 hover:text-blue-700">
                            <x-filament::icon icon="heroicon-o-document-plus" class="h-4 w-4" />
                            Tambah Publikasi
                        </a>
                    @endif

                    @if ($canCreateProgram)
                        <a href="{{ $programCreateUrl }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-950 shadow-sm transition hover:border-blue-200 hover:text-blue-700">
                            <x-filament::icon icon="heroicon-o-academic-cap" class="h-4 w-4" />
                            Tambah Program
                        </a>
                    @endif
                </div>
            @endif
        </div>

        @if ($canViewInsights || $canViewPrograms || $canViewPublications)
            <div class="grid border-t border-slate-200 bg-white sm:grid-cols-3">
                @if ($canViewInsights)
                    <a href="{{ $insightIndexUrl }}" class="border-b border-slate-200 p-4 transition hover:bg-slate-50 sm:border-b-0 sm:border-r">
                        <p class="text-xs font-bold text-slate-500">Editorial terbit</p>
                        <p class="mt-1 text-2xl font-black text-slate-950">{{ $publishedInsights }}</p>
                    </a>
                @endif

                @if ($canViewPrograms)
                    <div class="border-b border-slate-200 p-4 sm:border-b-0 sm:border-r">
                        <p class="text-xs font-bold text-slate-500">Program aktif</p>
                        <p class="mt-1 text-2xl font-black text-slate-950">{{ $activePrograms }}</p>
                    </div>
                @endif

                @if ($canViewPublications)
                    <div class="p-4">
                        <p class="text-xs font-bold text-slate-500">Publikasi tayang</p>
                        <p class="mt-1 text-2xl font-black text-slate-950">{{ $publishedPublications }}</p>
                    </div>
                @endif
            </div>
        @endif
    </section>
</x-filament-widgets::widget>
