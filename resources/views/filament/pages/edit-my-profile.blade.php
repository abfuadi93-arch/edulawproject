<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-end">
            <x-filament::button type="submit" icon="heroicon-o-check" size="lg">
                Simpan Profil
            </x-filament::button>
        </div>
    </form>

    @php
        $statistics = $this->getProfileStatistics();
        $latestInsights = $this->getLatestInsights();
        $latestPublications = $this->getLatestPublications();
        $statusLabel = fn (?string $status): string => match ($status) {
            'published' => 'Published',
            'reviewed' => 'Reviewed',
            default => 'Draft',
        };
        $statusClass = fn (?string $status): string => match ($status) {
            'published' => 'bg-green-100 text-green-800 dark:bg-green-600 dark:text-white',
            'reviewed' => 'bg-amber-100 text-amber-800 dark:bg-amber-500 dark:text-slate-950',
            default => 'bg-slate-100 text-slate-700 dark:bg-slate-600 dark:text-white',
        };
    @endphp

    <x-filament::section
        heading="Statistik"
        description="Ringkasan kontribusi dan jangkauan seluruh konten yang terhubung ke profil ini."
        icon="heroicon-o-chart-bar"
    >
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['label' => 'Insight diterbitkan', 'value' => $statistics['insights'], 'icon' => 'heroicon-o-newspaper'],
                ['label' => 'Publikasi', 'value' => $statistics['publications'], 'icon' => 'heroicon-o-book-open'],
                ['label' => 'Program', 'value' => $statistics['programs'], 'icon' => 'heroicon-o-calendar-days'],
                ['label' => 'Total View', 'value' => $statistics['views'], 'icon' => 'heroicon-o-eye'],
            ] as $stat)
                <article class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-900">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $stat['label'] }}</p>
                            <p class="mt-2 text-3xl font-black text-slate-950 dark:text-slate-50">
                                {{ number_format($stat['value'], 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="rounded-xl bg-blue-50 p-3 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400">
                            <x-filament::icon :icon="$stat['icon']" class="h-6 w-6" />
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </x-filament::section>

    <div class="grid gap-6 xl:grid-cols-2">
        <x-filament::section
            heading="Artikel Terbaru"
            description="Lima insight terbaru yang mencantumkan profil ini sebagai penulis."
            icon="heroicon-o-newspaper"
        >
            @if ($latestInsights->isEmpty())
                <div class="py-8 text-center">
                    <x-filament::icon icon="heroicon-o-document-text" class="mx-auto h-10 w-10 text-blue-500" />
                    <p class="mt-3 font-semibold text-slate-700 dark:text-slate-300">Belum ada insight terhubung.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-slate-200 text-xs uppercase text-slate-500 dark:border-slate-700 dark:text-slate-400">
                            <tr>
                                <th class="px-3 py-3 font-bold">Judul</th>
                                <th class="px-3 py-3 font-bold">Status</th>
                                <th class="px-3 py-3 font-bold">Tanggal Publish</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach ($latestInsights as $insight)
                                @php($editUrl = $this->getInsightEditUrl($insight))
                                <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-700/60">
                                    <td class="px-3 py-4 font-semibold text-slate-900 dark:text-slate-100">
                                        @if ($editUrl)
                                            <a href="{{ $editUrl }}" class="hover:text-blue-600 dark:hover:text-blue-400">{{ $insight->title }}</a>
                                        @else
                                            {{ $insight->title }}
                                        @endif
                                    </td>
                                    <td class="px-3 py-4">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $statusClass($insight->status) }}">
                                            {{ $statusLabel($insight->status) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-slate-500 dark:text-slate-400">
                                        {{ $insight->published_at?->translatedFormat('d M Y') ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        <x-filament::section
            heading="Publikasi Terbaru"
            description="Lima publikasi terbaru yang terhubung ke identitas penulis ini."
            icon="heroicon-o-book-open"
        >
            @if ($latestPublications->isEmpty())
                <div class="py-8 text-center">
                    <x-filament::icon icon="heroicon-o-book-open" class="mx-auto h-10 w-10 text-blue-500" />
                    <p class="mt-3 font-semibold text-slate-700 dark:text-slate-300">Belum ada publikasi terhubung.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-slate-200 text-xs uppercase text-slate-500 dark:border-slate-700 dark:text-slate-400">
                            <tr>
                                <th class="px-3 py-3 font-bold">Judul</th>
                                <th class="px-3 py-3 font-bold">Status</th>
                                <th class="px-3 py-3 font-bold">Tanggal Publish</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach ($latestPublications as $publication)
                                @php($editUrl = $this->getPublicationEditUrl($publication))
                                <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-700/60">
                                    <td class="px-3 py-4 font-semibold text-slate-900 dark:text-slate-100">
                                        @if ($editUrl)
                                            <a href="{{ $editUrl }}" class="hover:text-blue-600 dark:hover:text-blue-400">{{ $publication->title }}</a>
                                        @else
                                            {{ $publication->title }}
                                        @endif
                                    </td>
                                    <td class="px-3 py-4">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $statusClass($publication->status) }}">
                                            {{ $statusLabel($publication->status) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-slate-500 dark:text-slate-400">
                                        {{ $publication->published_at?->translatedFormat('d M Y') ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
