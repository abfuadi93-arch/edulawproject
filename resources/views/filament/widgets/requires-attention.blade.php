<x-filament-widgets::widget>
    <section class="edulaw-admin-card">
        <header class="flex items-center justify-between gap-4 border-b border-slate-200 p-5">
            <div>
                <p class="text-sm font-black text-slate-950">
                    Requires Attention
                </p>
                <p class="mt-1 text-xs leading-5 edulaw-admin-muted">
                    Today&apos;s operational queue across editorial, programs and interaction.
                </p>
            </div>
            <span class="rounded-full bg-slate-50 px-2.5 py-1 text-xs font-black text-slate-600 ring-1 ring-slate-200">
                {{ $pendingCount }}
            </span>
        </header>

        <div class="divide-y divide-slate-200">
            @forelse ($items as $item)
                @php
                    $toneClass = match ($item['tone']) {
                        'green' => 'border-emerald-500 bg-emerald-50 text-emerald-700',
                        'orange' => 'border-orange-500 bg-orange-50 text-orange-700',
                        'red' => 'border-rose-500 bg-rose-50 text-rose-700',
                        default => 'border-blue-500 bg-blue-50 text-blue-700',
                    };
                @endphp

                <a href="{{ $item['url'] }}" class="flex items-center justify-between gap-4 border-l-4 border-l-transparent p-4 transition hover:border-l-current hover:bg-slate-50">
                    <span class="flex min-w-0 items-center gap-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg {{ $toneClass }}">
                            <x-filament::icon :icon="$item['icon']" class="h-4 w-4" />
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-black text-slate-950">
                                {{ $item['label'] }}
                            </span>
                        </span>
                    </span>
                    <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-xs font-black text-slate-700 ring-1 ring-slate-200">
                        {{ $item['count'] }}
                    </span>
                </a>
            @empty
                <div class="grid min-h-56 place-items-center p-8 text-center">
                    <div>
                        <div class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-emerald-50 text-emerald-600">
                            <x-filament::icon icon="heroicon-o-check" class="h-6 w-6" />
                        </div>
                        <p class="mt-4 text-sm font-black text-slate-950">
                            No pending actions.
                        </p>
                    </div>
                </div>
            @endforelse
        </div>
    </section>
</x-filament-widgets::widget>
