<x-filament-widgets::widget>
    <section class="edulaw-admin-card">
        <header class="border-b border-slate-200 p-5">
            <p class="text-sm font-black text-slate-950">
                Pemeriksaan Panel Admin
            </p>
            <p class="mt-1 text-xs leading-5 edulaw-admin-muted">
                Pantau konfigurasi dan konten yang perlu perhatian sebelum tampil di website.
            </p>
        </header>

        <div class="grid gap-3 p-5 sm:grid-cols-3">
            @foreach ($summary as $item)
                @php
                    $summaryClass = match ($item['tone']) {
                        'danger' => 'bg-rose-50 text-rose-700',
                        'warning' => 'bg-amber-50 text-amber-700',
                        default => 'bg-blue-50 text-blue-700',
                    };
                @endphp

                <div class="rounded-lg border border-slate-200 bg-white p-4 text-center">
                    <p class="text-2xl font-black {{ $summaryClass }} mx-auto grid h-11 w-14 place-items-center rounded-lg">
                        {{ $item['count'] }}
                    </p>
                    <p class="mt-2 text-xs font-bold text-slate-600">
                        {{ $item['label'] }}
                    </p>
                </div>
            @endforeach
        </div>

        <div class="space-y-3 border-t border-slate-200 bg-slate-50/60 p-5">
            @forelse ($alerts as $alert)
                @php
                    $alertClass = match ($alert['severity']) {
                        'critical' => 'border-rose-200 bg-white text-rose-700',
                        'high' => 'border-amber-200 bg-white text-amber-700',
                        default => 'border-slate-200 bg-white text-slate-700',
                    };
                @endphp

                <article class="rounded-lg border p-4 {{ $alertClass }}">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-black text-slate-950">
                                {{ $alert['label'] }}
                            </p>
                            <p class="mt-1 text-xs leading-5 edulaw-admin-muted">
                                {{ $alert['description'] }}
                            </p>
                        </div>
                        <span class="shrink-0 rounded-md bg-current/10 px-2 py-1 text-xs font-black uppercase">
                            {{ $alert['severity'] }}
                        </span>
                    </div>
                </article>
            @empty
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-700">
                    Tidak ada alert penting saat ini.
                </div>
            @endforelse
        </div>
    </section>
</x-filament-widgets::widget>
