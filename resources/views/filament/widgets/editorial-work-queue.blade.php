<x-filament-widgets::widget>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">
        <header class="border-b border-slate-200 px-5 py-4 dark:border-white/10 sm:px-6">
            <h2 class="text-base font-bold text-slate-950 dark:text-white">Ruang Kerja Editorial Saya</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Tanggung jawab writer dan editor dipisahkan berdasarkan pemilik tulisan dan penugasan editor.
            </p>
        </header>

        <div @class([
            'grid gap-px bg-slate-200 dark:bg-white/10',
            'lg:grid-cols-2' => $panels->count() > 1,
        ])>
            @foreach ($panels as $panel)
                <article class="min-w-0 bg-white p-5 dark:bg-slate-900 sm:p-6" wire:key="editorial-work-panel-{{ $panel['key'] }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-700 dark:bg-white/10 dark:text-slate-200">
                                <x-filament::icon :icon="$panel['icon']" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <h3 class="font-bold text-slate-950 dark:text-white">{{ $panel['title'] }}</h3>
                                <p class="mt-0.5 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $panel['description'] }}</p>
                            </div>
                        </div>
                        <x-filament::badge :color="$panel['color']">{{ number_format($panel['count'], 0, ',', '.') }}</x-filament::badge>
                    </div>

                    <div class="mt-5 divide-y divide-slate-100 border-y border-slate-100 dark:divide-white/10 dark:border-white/10">
                        @forelse ($panel['items'] as $item)
                            <a href="{{ $item['url'] }}" class="group flex min-h-16 items-center justify-between gap-4 py-3" wire:key="editorial-work-{{ $panel['key'] }}-{{ $item['id'] }}">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-800 transition group-hover:text-primary-600 dark:text-slate-100 dark:group-hover:text-primary-400">
                                        {{ $item['title'] }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">Diperbarui {{ $item['updated'] }}</p>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <x-filament::badge :color="$item['statusColor']" size="sm">{{ $item['status'] }}</x-filament::badge>
                                    <x-filament::icon icon="heroicon-m-chevron-right" class="h-4 w-4 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-primary-500" />
                                </div>
                            </a>
                        @empty
                            <p class="py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ $panel['empty'] }}</p>
                        @endforelse
                    </div>

                    <a href="{{ $panel['url'] }}" class="mt-4 inline-flex items-center gap-1.5 text-sm font-bold text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                        {{ $panel['action'] }}
                        <x-filament::icon icon="heroicon-m-arrow-right" class="h-4 w-4" />
                    </a>
                </article>
            @endforeach
        </div>
    </section>
</x-filament-widgets::widget>
