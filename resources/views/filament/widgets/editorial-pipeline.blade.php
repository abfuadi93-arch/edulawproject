<x-filament-widgets::widget>
    <section class="edulaw-admin-card edulaw-editorial-pipeline">
        <header class="edulaw-performance-panel-header">
            <div class="flex items-start gap-3">
                <span class="edulaw-performance-panel-icon bg-primary-50 text-primary-700">
                    <x-filament::icon icon="heroicon-o-queue-list" class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-sm font-black text-slate-950">Aktivitas Editorial</p>
                    <p class="mt-1 text-xs leading-5 edulaw-admin-muted">
                        Posisi naskah aktif, penugasan editor, dan deadline terdekat.
                    </p>
                </div>
            </div>
            <a href="{{ $workspaceUrl }}" class="edulaw-performance-period edulaw-panel-link">
                Buka Workspace
                <x-filament::icon icon="heroicon-m-arrow-right" class="h-3.5 w-3.5" />
            </a>
        </header>

        <div class="grid grid-cols-2 gap-px border-b border-slate-200 bg-slate-200 sm:grid-cols-4">
            @foreach ([
                ['label' => 'Naskah Baru', 'value' => $counts['draft'], 'tone' => 'navy'],
                ['label' => 'Dalam Review', 'value' => $counts['review'], 'tone' => 'amber'],
                ['label' => 'Belum Ditugaskan', 'value' => $counts['unassigned'], 'tone' => 'blue'],
                ['label' => 'Lewat Deadline', 'value' => $counts['overdue'], 'tone' => 'red'],
            ] as $metric)
                <article class="bg-white p-4">
                    <p class="text-2xl font-black edulaw-pipeline-metric edulaw-pipeline-metric-{{ $metric['tone'] }}">
                        {{ number_format($metric['value'], 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-xs font-bold text-slate-500">{{ $metric['label'] }}</p>
                </article>
            @endforeach
        </div>

        <div class="divide-y divide-slate-200">
            @forelse ($items as $item)
                <a href="{{ $item['url'] }}" class="group grid gap-3 p-4 transition hover:bg-slate-50 sm:grid-cols-[minmax(0,1fr)_10rem_8rem_auto] sm:items-center">
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-black text-slate-950">{{ $item['title'] }}</span>
                        <span class="mt-1 block text-xs edulaw-admin-muted">Editor: {{ $item['editor'] }}</span>
                    </span>
                    <x-filament::badge :color="$item['statusColor']" size="sm">{{ $item['status'] }}</x-filament::badge>
                    <span @class([
                        'text-xs font-bold',
                        'text-rose-700' => $item['overdue'],
                        'text-slate-500' => ! $item['overdue'],
                    ])>
                        {{ $item['deadline'] }}
                    </span>
                    <x-filament::icon icon="heroicon-m-chevron-right" class="h-4 w-4 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-primary-600" />
                </a>
            @empty
                <div class="p-6 text-center">
                    <p class="text-sm font-black text-slate-950">Tidak ada naskah aktif.</p>
                    <p class="mt-1 text-xs edulaw-admin-muted">Draft dan naskah review akan muncul di sini.</p>
                </div>
            @endforelse
        </div>
    </section>
</x-filament-widgets::widget>
