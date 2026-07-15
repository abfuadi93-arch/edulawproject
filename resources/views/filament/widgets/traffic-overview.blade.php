<x-filament-widgets::widget>
    <section class="edulaw-admin-card">
        <header class="border-b border-slate-200 p-5">
            <p class="text-sm font-black text-slate-950">
                Traffic Analytics
            </p>
            <p class="mt-1 text-xs leading-5 edulaw-admin-muted">
                Public website performance at a glance.
            </p>
        </header>

        <div class="space-y-4 p-5">
            <div class="grid grid-cols-2 gap-3">
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
                <div class="flex items-end gap-2">
                    @foreach ($dailySeries as $day)
                        <div class="flex h-16 flex-1 items-end rounded bg-slate-50 p-1" title="{{ $day['views'] }} pageviews">
                            <div class="w-full rounded bg-blue-600" style="height: {{ $day['height'] }}%;"></div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2 flex justify-between text-[10px] font-semibold text-slate-400">
                    @foreach ($dailySeries as $day)
                        <span>{{ $day['label'] }}</span>
                    @endforeach
                </div>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white">
                <header class="border-b border-slate-200 px-4 py-3">
                    <p class="text-xs font-black uppercase tracking-wide text-slate-500">
                        Top five pages
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
                                <span class="block text-sm font-black text-slate-950">
                                    {{ $page['views'] }}
                                </span>
                                <span class="block text-[10px] font-bold uppercase tracking-wide text-slate-400">
                                    Views
                                </span>
                            </span>
                        </a>
                    @empty
                        <div class="p-6 text-center">
                            <p class="text-sm font-black text-slate-950">
                                No traffic yet.
                            </p>
                            <p class="mt-1 text-xs edulaw-admin-muted">
                                Pageviews will appear after public pages are visited.
                            </p>
                        </div>
                    @endforelse
                </div>
            </article>
        </div>
    </section>
</x-filament-widgets::widget>
