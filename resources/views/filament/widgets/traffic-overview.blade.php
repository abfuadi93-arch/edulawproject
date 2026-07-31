<x-filament-widgets::widget>
    <section class="edulaw-admin-card">
        <header class="edulaw-performance-panel-header">
            <div class="flex items-start gap-3">
                <span class="edulaw-performance-panel-icon bg-blue-50 text-blue-700">
                    <x-filament::icon icon="heroicon-o-chart-bar-square" class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-sm font-black text-slate-950">{{ $title }}</p>
                    <p class="mt-1 text-xs leading-5 edulaw-admin-muted">
                        {{ $description }}
                    </p>
                </div>
            </div>
            <span class="edulaw-performance-period">30 hari terakhir</span>
        </header>

        <div class="space-y-4 p-5">
            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                @foreach ($stats as $stat)
                    @php
                        $toneClass = match ($stat['tone']) {
                            'success' => 'text-emerald-700 bg-emerald-50',
                            'warning' => 'text-orange-700 bg-orange-50',
                            default => 'text-blue-700 bg-blue-50',
                        };
                    @endphp

                    <article class="rounded-xl border border-slate-200 bg-white p-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-bold leading-4 text-slate-500">
                                    {{ $stat['label'] }}
                                </p>
                                <p class="mt-1 text-xl font-black text-slate-950">
                                    {{ $stat['value'] }}
                                </p>
                            </div>
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg {{ $toneClass }}">
                                <x-filament::icon :icon="$stat['icon']" class="h-4 w-4" />
                            </span>
                        </div>
                    </article>
                @endforeach
            </div>

            <article class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-slate-500">{{ $trendTitle }}</p>
                        <div class="mt-2 flex items-center gap-4 text-[10px] font-bold text-slate-500">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-2.5 w-4 rounded-sm bg-blue-600"></span>
                                Batang: Pageviews
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-0.5 w-5 bg-emerald-500"></span>
                                Garis: Visitors
                            </span>
                        </div>
                    </div>

                    <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-600">
                        <span class="sr-only">Periode grafik traffic</span>
                        <select
                            wire:model.live="trafficPeriod"
                            class="rounded-lg border border-slate-200 bg-white py-2 pl-3 pr-8 text-xs font-bold text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        >
                            <option value="week">Mingguan</option>
                            <option value="month">Bulanan</option>
                        </select>
                    </label>
                </div>

                <div
                    class="overflow-x-auto rounded-xl border border-slate-200 bg-slate-50/70 p-2"
                    wire:loading.class="opacity-60"
                    wire:target="trafficPeriod"
                >
                    <svg
                        viewBox="0 0 {{ $chartWidth }} 260"
                        class="h-[260px] w-full"
                        style="min-width: 700px"
                        role="img"
                        aria-label="{{ $trendTitle }}: batang menunjukkan pageviews dan garis menunjukkan visitors"
                    >
                        <defs>
                            <linearGradient id="trafficBarGradient" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#2563eb" />
                                <stop offset="100%" stop-color="#60a5fa" />
                            </linearGradient>
                        </defs>

                        @foreach ([50, 90, 130, 170, 210] as $gridY)
                            <line
                                x1="20"
                                y1="{{ $gridY }}"
                                x2="{{ $chartWidth - 20 }}"
                                y2="{{ $gridY }}"
                                stroke="#e2e8f0"
                                stroke-width="1"
                                stroke-dasharray="4 5"
                            />
                        @endforeach

                        @foreach ($chartSeries as $point)
                            <rect
                                x="{{ $point['x'] - 22 }}"
                                y="{{ $point['viewsY'] }}"
                                width="44"
                                height="{{ $point['viewsHeight'] }}"
                                rx="8"
                                fill="url(#trafficBarGradient)"
                            />
                            <text
                                x="{{ $point['x'] - 13 }}"
                                y="{{ max(24, $point['viewsY'] - 9) }}"
                                text-anchor="middle"
                                fill="#1d4ed8"
                                font-size="12"
                                font-weight="800"
                            >{{ $point['views'] }}</text>
                        @endforeach

                        @if (filled($linePoints))
                            <polyline
                                points="{{ $linePoints }}"
                                fill="none"
                                stroke="#10b981"
                                stroke-width="4"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                vector-effect="non-scaling-stroke"
                            />
                        @endif

                        @foreach ($chartSeries as $point)
                            <circle
                                cx="{{ $point['x'] }}"
                                cy="{{ $point['visitorsY'] }}"
                                r="6"
                                fill="#ffffff"
                                stroke="#10b981"
                                stroke-width="4"
                                vector-effect="non-scaling-stroke"
                            />
                            <text
                                x="{{ $point['x'] + 15 }}"
                                y="{{ max(22, $point['visitorsY'] - 9) }}"
                                fill="#047857"
                                font-size="12"
                                font-weight="800"
                            >{{ $point['visitors'] }}</text>
                            <text
                                x="{{ $point['x'] }}"
                                y="242"
                                text-anchor="middle"
                                fill="#64748b"
                                font-size="11"
                                font-weight="700"
                            >{{ $point['label'] }}</text>
                        @endforeach
                    </svg>
                </div>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white">
                <header class="border-b border-slate-200 px-4 py-3">
                    <p class="text-xs font-black uppercase tracking-wide text-slate-500">
                        Halaman Teratas
                    </p>
                </header>

                <div class="divide-y divide-slate-200">
                    @forelse ($topPages as $page)
                        <a href="{{ $page['url'] }}" target="_blank" rel="noopener" class="flex items-center justify-between gap-4 px-4 py-3 transition hover:bg-slate-50">
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-black text-slate-950">
                                    {{ $page['label'] }}
                                </span>
                                <span class="mt-0.5 block truncate text-xs edulaw-admin-muted">
                                    {{ $page['route'] }}
                                </span>
                            </span>
                            <span class="shrink-0 text-right">
                                <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-black text-blue-700">
                                    {{ $page['views'] }}
                                </span>
                            </span>
                        </a>
                    @empty
                        <div class="p-6 text-center">
                            <p class="text-sm font-black text-slate-950">
                                Data traffic belum tersedia.
                            </p>
                            <p class="mt-1 text-xs edulaw-admin-muted">
                                Page views akan tampil setelah halaman publik dikunjungi.
                            </p>
                        </div>
                    @endforelse
                </div>
            </article>
        </div>
    </section>
</x-filament-widgets::widget>
