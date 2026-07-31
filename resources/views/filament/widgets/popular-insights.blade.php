<x-filament-widgets::widget>
    <section class="edulaw-admin-card">
        <header class="edulaw-performance-panel-header">
            <div class="flex items-start gap-3">
                <span class="edulaw-performance-panel-icon bg-emerald-50 text-emerald-700">
                    <x-filament::icon icon="heroicon-o-arrow-trending-up" class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-sm font-black text-slate-950">Artikel Populer</p>
                    <p class="mt-1 text-xs leading-5 edulaw-admin-muted">
                        Berdasarkan page views valid pada {{ $periodLabel }}.
                    </p>
                </div>
            </div>
            <span class="edulaw-performance-period">Top 5</span>
        </header>

        @if ($items->isEmpty())
            <div class="flex items-center gap-3 p-5">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-700">
                    <x-filament::icon icon="heroicon-o-chart-bar" class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-sm font-black text-slate-950">Data kunjungan belum tersedia.</p>
                    <p class="mt-0.5 text-xs edulaw-admin-muted">Artikel tampil setelah menerima kunjungan publik yang valid.</p>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
            <table class="w-full min-w-[42rem] text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-[11px] font-black uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Artikel</th>
                        <th class="px-3 py-3">Kategori</th>
                        <th class="px-3 py-3 text-right">Kunjungan</th>
                        <th class="px-3 py-3">Terbit</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($items as $item)
                        <tr class="bg-white transition hover:bg-slate-50">
                            <td class="max-w-md px-5 py-3">
                                <p class="line-clamp-2 font-black leading-5 text-slate-950">{{ $item['title'] }}</p>
                            </td>
                            <td class="px-3 py-3">
                                <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-700">{{ $item['category'] }}</span>
                            </td>
                            <td class="px-3 py-3 text-right">
                                <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-black tabular-nums text-blue-700">{{ $item['viewsLabel'] }}</span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 text-xs font-semibold text-slate-500">{{ $item['published'] }}</td>
                            <td class="px-5 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ $item['editUrl'] }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-black text-blue-700 hover:border-blue-300 hover:bg-blue-50">
                                        Edit
                                    </a>
                                    @if ($item['publicUrl'])
                                        <a href="{{ $item['publicUrl'] }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-black text-slate-700 hover:border-slate-300 hover:bg-slate-50">
                                            Lihat
                                            <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="h-3.5 w-3.5" />
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </section>
</x-filament-widgets::widget>
