<x-filament-widgets::widget>
    <section class="edulaw-admin-card">
        <header class="edulaw-performance-panel-header">
            <div class="flex items-start gap-3">
                <span class="edulaw-performance-panel-icon bg-violet-50 text-violet-700">
                    <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-sm font-black text-slate-950">Aktivitas Terbaru</p>
                    <p class="mt-1 text-xs leading-5 edulaw-admin-muted">
                        Pembaruan konten dan interaksi terbaru di panel.
                    </p>
                </div>
            </div>
            <span class="edulaw-performance-period">{{ $activities->count() }} aktivitas</span>
        </header>

        <div class="divide-y divide-slate-200 px-5">
            @forelse ($activities as $activity)
                @php
                    $toneClass = match ($activity['tone']) {
                        'green' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                        'orange' => 'bg-orange-50 text-orange-700 ring-orange-100',
                        'red' => 'bg-rose-50 text-rose-700 ring-rose-100',
                        'purple' => 'bg-violet-50 text-violet-700 ring-violet-100',
                        default => 'bg-blue-50 text-blue-700 ring-blue-100',
                    };
                @endphp

                <a href="{{ $activity['url'] }}" class="group flex gap-3 py-3.5">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full text-[11px] font-black ring-1 {{ $toneClass }}">
                        {{ $activity['initials'] }}
                    </span>

                    <span class="min-w-0 flex-1">
                        <span class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm">
                            <span class="font-black text-slate-950">{{ $activity['userName'] }}</span>
                            <span class="font-semibold text-slate-500">{{ $activity['action'] }}</span>
                        </span>
                        <span class="mt-1 block line-clamp-2 text-sm font-bold leading-5 text-slate-800">
                            {{ $activity['title'] }}
                        </span>
                        <span class="mt-2 block text-xs font-semibold text-slate-500">
                            {{ $activity['time'] }}
                        </span>
                    </span>
                </a>
            @empty
                <div class="flex items-center gap-3 py-5">
                    <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-500">
                        <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5" />
                    </div>
                    <p class="text-sm font-black text-slate-950">Belum ada aktivitas terbaru.</p>
                </div>
            @endforelse
        </div>
    </section>
</x-filament-widgets::widget>
