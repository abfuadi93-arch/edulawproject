<x-filament-widgets::widget>
    <section class="edulaw-admin-card h-full">
        <header class="edulaw-performance-panel-header">
            <div class="flex items-start gap-3">
                <span class="edulaw-performance-panel-icon bg-amber-50 text-amber-700">
                    <x-filament::icon :icon="$icon" class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-sm font-black text-slate-950">{{ $title }}</p>
                    <p class="mt-1 text-xs leading-5 edulaw-admin-muted">{{ $description }}</p>
                </div>
            </div>
            <span class="edulaw-performance-period">Quality Control</span>
        </header>

        @if ($items->isEmpty())
            <div class="flex items-center gap-3 p-5">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-700">
                    <x-filament::icon icon="heroicon-o-check" class="h-5 w-5" />
                </span>
                <p class="text-sm font-black text-emerald-700">✓ {{ $emptyMessage }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
            <table class="w-full min-w-[38rem] text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-[11px] font-black uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Judul</th>
                        <th class="px-3 py-3">Status</th>
                        <th class="px-3 py-3">Kategori</th>
                        <th class="px-3 py-3">Diperbarui</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($items as $item)
                        @php
                            $statusClass = match ($item['statusTone']) {
                                'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                'warning' => 'bg-amber-50 text-amber-700 ring-amber-200',
                                default => 'bg-blue-50 text-blue-700 ring-blue-200',
                            };
                        @endphp
                        <tr class="bg-white transition hover:bg-slate-50">
                            <td class="max-w-xs px-5 py-3">
                                <p class="line-clamp-2 font-black leading-5 text-slate-950">{{ $item['title'] }}</p>
                            </td>
                            <td class="px-3 py-3">
                                <span class="rounded-md px-2 py-1 text-[11px] font-black ring-1 {{ $statusClass }}">
                                    {{ $item['statusLabel'] }}
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <span class="rounded-full bg-blue-50 px-2 py-1 text-[11px] font-bold text-blue-700">{{ $item['category'] }}</span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 text-xs font-semibold text-slate-500">{{ $item['updated'] }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ $item['url'] }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-black text-blue-700 shadow-sm transition hover:border-blue-300 hover:bg-blue-50">
                                    <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </section>
</x-filament-widgets::widget>
