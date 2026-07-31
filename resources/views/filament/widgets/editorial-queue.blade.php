<x-filament-widgets::widget>
    <section class="edulaw-admin-card">
        <header class="edulaw-performance-panel-header">
            <div class="flex items-start gap-3">
                <span class="edulaw-performance-panel-icon bg-blue-50 text-blue-700">
                    <x-filament::icon icon="heroicon-o-queue-list" class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-sm font-black text-slate-950">Antrean Editorial</p>
                    <p class="mt-1 text-xs leading-5 edulaw-admin-muted">
                        Draft, naskah hasil review, dan Editorial yang baru diterbitkan.
                    </p>
                </div>
            </div>
            <a href="{{ $indexUrl }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-950 shadow-sm transition hover:border-blue-200 hover:text-blue-700">
                Lihat Semua
                <x-filament::icon icon="heroicon-o-chevron-right" class="h-4 w-4" />
            </a>
        </header>

        <div>
            @forelse ($items as $item)
                @php
                    $badgeClass = match ($item['statusTone']) {
                        'green' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                        'amber' => 'bg-amber-50 text-amber-700 ring-amber-200',
                        default => 'bg-blue-50 text-blue-700 ring-blue-200',
                    };
                @endphp

                <a href="{{ $item['url'] }}" class="block border-b border-slate-200 p-4 transition last:border-b-0 hover:bg-slate-50">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="line-clamp-2 text-sm font-black leading-5 text-slate-950">
                                {{ $item['title'] }}
                            </p>
                            <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-xs edulaw-admin-muted">
                                <span class="inline-flex items-center gap-1">
                                    <x-filament::icon icon="heroicon-o-user" class="h-3.5 w-3.5" />
                                    {{ $item['author'] }}
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <x-filament::icon icon="heroicon-o-folder" class="h-3.5 w-3.5" />
                                    {{ $item['category'] }}
                                </span>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-md px-2 py-1 text-xs font-black ring-1 {{ $badgeClass }}">
                            {{ $item['statusLabel'] }}
                        </span>
                    </div>
                    <p class="mt-2 text-xs font-semibold text-slate-500">
                        Diperbarui {{ $item['updated'] }}
                    </p>
                </a>
            @empty
                <div class="flex items-center gap-3 p-5">
                    <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-700">
                        <x-filament::icon icon="heroicon-o-check" class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="text-sm font-black text-slate-950">
                            Tidak ada antrean editorial.
                        </p>
                        <p class="mt-1 max-w-sm text-xs leading-5 edulaw-admin-muted">
                            Draft dan naskah yang perlu ditinjau akan muncul di sini.
                        </p>
                    </div>
                </div>
            @endforelse
        </div>
    </section>
</x-filament-widgets::widget>
