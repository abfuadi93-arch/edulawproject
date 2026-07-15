<x-filament-widgets::widget>
    <section class="edulaw-admin-card">
        <header class="border-b border-slate-200 p-5">
            <p class="text-sm font-black text-slate-950">
                Recent Activity
            </p>
            <p class="mt-1 text-xs leading-5 edulaw-admin-muted">
                Timeline of content updates and public submissions.
            </p>
        </header>

        <div class="min-h-72 p-5">
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

                <a href="{{ $activity['url'] }}" class="group relative flex gap-4 pb-5 last:pb-0">
                    @if (! $loop->last)
                        <span class="absolute left-5 top-10 h-[calc(100%-2.5rem)] w-px bg-slate-200"></span>
                    @endif

                    <span class="z-10 grid h-10 w-10 shrink-0 place-items-center rounded-full text-xs font-black ring-1 {{ $toneClass }}">
                        {{ $activity['initials'] }}
                    </span>

                    <span class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition group-hover:bg-slate-50">
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
                <div class="grid min-h-72 place-items-center text-center">
                    <div>
                        <div class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-slate-100 text-slate-500">
                            <x-filament::icon icon="heroicon-o-clock" class="h-6 w-6" />
                        </div>
                        <p class="mt-4 text-sm font-black text-slate-950">
                            No recent activity.
                        </p>
                    </div>
                </div>
            @endforelse
        </div>
    </section>
</x-filament-widgets::widget>
