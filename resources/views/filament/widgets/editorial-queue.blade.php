<x-filament-widgets::widget>
    <section class="edulaw-admin-card">
        <header class="flex items-center justify-between gap-4 border-b border-slate-200 p-5">
            <div>
                <p class="text-sm font-black text-slate-950">Editorial Queue</p>
                <p class="mt-1 text-xs leading-5 edulaw-admin-muted">
                    Insight yang masih perlu ditulis, ditinjau, atau disiapkan untuk terbit.
                </p>
            </div>
            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-black text-amber-700">
                {{ $count }}
            </span>
        </header>

        <div class="min-h-72">
            @forelse ($items as $item)
                @php
                    $badgeClass = match ($item['status']) {
                        'submitted' => 'bg-orange-50 text-orange-700 ring-orange-200',
                        'reviewed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                        default => 'bg-slate-50 text-slate-700 ring-slate-200',
                    };
                @endphp

                <a href="{{ $item['url'] }}" class="block border-b border-slate-200 p-5 transition last:border-b-0 hover:bg-slate-50">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="line-clamp-2 text-sm font-black leading-5 text-slate-950">
                                {{ $item['title'] }}
                            </p>
                            <p class="mt-1 line-clamp-1 text-xs edulaw-admin-muted">
                                {{ $item['meta'] }}
                            </p>
                        </div>
                        <span class="shrink-0 rounded-md px-2 py-1 text-xs font-black ring-1 {{ $badgeClass }}">
                            {{ $item['statusLabel'] }}
                        </span>
                    </div>
                    <p class="mt-2 text-xs font-semibold text-slate-500">
                        Update {{ $item['updated'] }}
                    </p>
                </a>
            @empty
                <div class="grid min-h-72 place-items-center p-8 text-center">
                    <div>
                        <div class="mx-auto grid h-12 w-12 place-items-center rounded-full border-2 border-blue-500 text-blue-600">
                            <x-filament::icon icon="heroicon-o-check" class="h-6 w-6" />
                        </div>
                        <p class="mt-4 text-sm font-black text-slate-950">
                            Queue editorial kosong
                        </p>
                        <p class="mt-1 max-w-sm text-xs leading-5 edulaw-admin-muted">
                            Semua insight sudah tertangani. Tidak ada naskah yang menunggu review.
                        </p>
                    </div>
                </div>
            @endforelse
        </div>

        <footer class="border-t border-slate-200 p-4 text-center">
            <a href="{{ $indexUrl }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-950 shadow-sm transition hover:border-blue-200 hover:text-blue-700">
                Lihat semua queue
                <x-filament::icon icon="heroicon-o-chevron-right" class="h-4 w-4" />
            </a>
        </footer>
    </section>
</x-filament-widgets::widget>
