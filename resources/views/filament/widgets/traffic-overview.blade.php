<x-filament-widgets::widget>
    <section class="edulaw-admin-card">
        <header class="flex flex-col gap-3 border-b border-slate-200 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-black text-slate-950">
                    Traffic Kunjungan
                </p>
                <p class="mt-1 text-xs leading-5 edulaw-admin-muted">
                    Kunjungan halaman publik yang tercatat langsung dari website.
                </p>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 text-xs font-bold text-slate-600 ring-1 ring-slate-200">
                <x-filament::icon icon="heroicon-o-shield-check" class="h-4 w-4 text-emerald-600" />
                IP disimpan sebagai hash
            </span>
        </header>

        <div class="grid gap-4 p-5 xl:grid-cols-[0.9fr_1.1fr]">
            <div class="space-y-4">
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($stats as $stat)
                        @php
                            $toneClass = match ($stat['tone']) {
                                'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                                'warning' => 'bg-amber-50 text-amber-700 ring-amber-100',
                                'slate' => 'bg-slate-50 text-slate-700 ring-slate-200',
                                default => 'bg-blue-50 text-blue-700 ring-blue-100',
                            };
                        @endphp

                        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-bold text-slate-500">
                                        {{ $stat['label'] }}
                                    </p>
                                    <p class="mt-1 text-2xl font-black text-slate-950">
                                        {{ $stat['value'] }}
                                    </p>
                                </div>
                                <span class="grid h-10 w-10 place-items-center rounded-lg ring-1 {{ $toneClass }}">
                                    <x-filament::icon :icon="$stat['icon']" class="h-5 w-5" />
                                </span>
                            </div>
                        </article>
                    @endforeach
                </div>

                <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-black text-slate-950">
                                Tren 7 hari
                            </p>
                            <p class="mt-1 text-xs edulaw-admin-muted">
                                Pageview harian dari halaman publik.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 grid h-40 grid-cols-7 items-end gap-2">
                        @foreach ($dailySeries as $day)
                            <div class="flex h-full flex-col items-center justify-end gap-2">
                                <div class="flex h-24 w-full items-end rounded-lg bg-slate-50 p-1">
                                    <div
                                        class="w-full rounded-md bg-blue-600"
                                        style="height: {{ $day['height'] }}%;"
                                        title="{{ $day['views'] }} kunjungan"
                                    ></div>
                                </div>
                                <div class="text-center">
                                    <p class="text-xs font-black text-slate-700">
                                        {{ $day['views'] }}
                                    </p>
                                    <p class="mt-1 text-[10px] font-semibold text-slate-400">
                                        {{ $day['label'] }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>
            </div>

            <article class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <header class="border-b border-slate-200 p-4">
                    <p class="text-sm font-black text-slate-950">
                        Halaman Teratas
                    </p>
                    <p class="mt-1 text-xs edulaw-admin-muted">
                        Berdasarkan pageview 30 hari terakhir.
                    </p>
                </header>

                <div class="divide-y divide-slate-200">
                    @forelse ($topPages as $page)
                        <a href="{{ $page['url'] }}" target="_blank" rel="noopener" class="flex items-center justify-between gap-4 p-4 transition hover:bg-slate-50">
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-black text-slate-950">
                                    {{ $page['label'] }}
                                </span>
                                <span class="mt-1 block truncate text-xs edulaw-admin-muted">
                                    {{ $page['route'] }}
                                </span>
                            </span>
                            <span class="grid grid-cols-2 gap-2 text-right">
                                <span>
                                    <span class="block text-sm font-black text-slate-950">
                                        {{ $page['views'] }}
                                    </span>
                                    <span class="block text-[10px] font-bold uppercase tracking-wide text-slate-400">
                                        Views
                                    </span>
                                </span>
                                <span>
                                    <span class="block text-sm font-black text-slate-950">
                                        {{ $page['visitors'] }}
                                    </span>
                                    <span class="block text-[10px] font-bold uppercase tracking-wide text-slate-400">
                                        Visitor
                                    </span>
                                </span>
                            </span>
                        </a>
                    @empty
                        <div class="grid min-h-72 place-items-center p-8 text-center">
                            <div>
                                <div class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-slate-100 text-slate-500">
                                    <x-filament::icon icon="heroicon-o-chart-bar" class="h-6 w-6" />
                                </div>
                                <p class="mt-4 text-sm font-black text-slate-950">
                                    Belum ada data traffic
                                </p>
                                <p class="mt-1 max-w-sm text-xs leading-5 edulaw-admin-muted">
                                    Data akan muncul setelah halaman publik dikunjungi.
                                </p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </article>
        </div>
    </section>
</x-filament-widgets::widget>
