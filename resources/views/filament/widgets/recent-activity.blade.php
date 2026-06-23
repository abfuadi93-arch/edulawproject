<x-filament-widgets::widget>
    <section class="edulaw-admin-card">
        <header class="border-b border-slate-200 p-5">
            <p class="text-sm font-black text-slate-950">Aktivitas Terbaru</p>
            <p class="mt-1 text-xs leading-5 edulaw-admin-muted">
                Perubahan terbaru dari modul konten, interaksi, dan publikasi.
            </p>
        </header>

        <div class="min-h-72">
            @forelse ($activities as $activity)
                @php
                    $toneClass = match ($activity['tone']) {
                        'success' => 'bg-emerald-50 text-emerald-600',
                        'warning' => 'bg-amber-50 text-amber-600',
                        'danger' => 'bg-rose-50 text-rose-600',
                        'purple' => 'bg-violet-50 text-violet-600',
                        default => 'bg-blue-50 text-blue-600',
                    };
                @endphp

                <a href="{{ $activity['url'] }}" class="flex gap-4 border-b border-slate-200 p-5 transition last:border-b-0 hover:bg-slate-50">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full {{ $toneClass }}">
                        <x-filament::icon icon="heroicon-o-bolt" class="h-4 w-4" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-black text-slate-950">
                            {{ $activity['label'] }}
                        </span>
                        <span class="mt-1 block truncate text-xs edulaw-admin-muted">
                            {{ $activity['title'] }}
                        </span>
                    </span>
                    <span class="shrink-0 text-xs font-bold text-slate-500">
                        {{ $activity['time'] }}
                    </span>
                </a>
            @empty
                <div class="grid min-h-72 place-items-center p-8 text-center">
                    <div>
                        <div class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-slate-100 text-slate-500">
                            <x-filament::icon icon="heroicon-o-clock" class="h-6 w-6" />
                        </div>
                        <p class="mt-4 text-sm font-black text-slate-950">
                            Belum ada aktivitas
                        </p>
                        <p class="mt-1 text-xs leading-5 edulaw-admin-muted">
                            Aktivitas terbaru akan muncul setelah konten diperbarui.
                        </p>
                    </div>
                </div>
            @endforelse
        </div>
    </section>
</x-filament-widgets::widget>
