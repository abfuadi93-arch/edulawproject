<x-filament-widgets::widget>
    <section class="edulaw-admin-card">
        <header class="edulaw-performance-panel-header">
            <div class="flex items-start gap-3">
                <span class="edulaw-performance-panel-icon bg-emerald-50 text-emerald-700">
                    <x-filament::icon icon="heroicon-o-presentation-chart-line" class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-sm font-black text-slate-950">Performa Konten</p>
                    <p class="mt-1 text-xs leading-5 edulaw-admin-muted">
                        Konten dengan performa terbaik dan publikasi terbaru.
                    </p>
                </div>
            </div>
            <span class="edulaw-performance-period">Snapshot</span>
        </header>

        <div class="grid gap-3 p-5 sm:grid-cols-2">
            @foreach ($items as $item)
                @php
                    $toneClass = match ($item['tone']) {
                        'green' => 'border-l-emerald-500 text-emerald-700 bg-emerald-50',
                        'orange' => 'border-l-orange-500 text-orange-700 bg-orange-50',
                        default => 'border-l-blue-500 text-blue-700 bg-blue-50',
                    };
                    $cardClass = 'block rounded-xl border border-l-4 border-slate-200 bg-white p-4 shadow-sm transition hover:bg-slate-50';
                @endphp

                @if ($item['url'])
                    <a href="{{ $item['url'] }}" target="_blank" rel="noopener" class="{{ $cardClass }} {{ $toneClass }}">
                @else
                    <article class="{{ $cardClass }} {{ $toneClass }}">
                @endif
                    <div class="flex items-start gap-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-current/10">
                            <x-filament::icon :icon="$item['icon']" class="h-4 w-4" />
                        </span>
                        <span class="min-w-0">
                            <span class="block text-xs font-black uppercase tracking-wide text-slate-500">
                                {{ $item['label'] }}
                            </span>
                            <span class="mt-1 block line-clamp-3 text-sm font-black leading-5 text-slate-950">
                                {{ $item['title'] }}
                            </span>
                            <span class="mt-2 block text-xs font-bold text-slate-500">
                                {{ $item['metric'] }}
                            </span>
                        </span>
                    </div>
                @if ($item['url'])
                    </a>
                @else
                    </article>
                @endif
            @endforeach
        </div>
    </section>
</x-filament-widgets::widget>
