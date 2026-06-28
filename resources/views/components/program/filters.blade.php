@props([
    'statusOptions' => [],
    'categoryOptions' => [],
    'formatOptions' => [],
    'levelOptions' => [],
    'selectedStatuses' => [],
    'selectedCategories' => [],
    'selectedFormats' => [],
    'selectedLevels' => [],
    'search' => '',
    'selectedSort' => 'terdekat',
    'selectedView' => 'grid',
])

@php
    $indexUrl = \Illuminate\Support\Facades\Route::has('programs.index') ? route('programs.index') : url('/program');
@endphp

<aside {{ $attributes->merge(['class' => 'lg:sticky lg:top-24 lg:self-start']) }}>
    <form action="{{ $indexUrl }}" method="GET" class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
        @if (filled($search))
            <input type="hidden" name="q" value="{{ $search }}">
        @endif
        <input type="hidden" name="sort" value="{{ $selectedSort }}">
        <input type="hidden" name="view" value="{{ $selectedView }}">

        <div class="mb-5 flex items-center justify-between gap-4">
            <h2 class="text-[11px] font-black uppercase tracking-[0.22em] text-brand-navy">
                Filter Program
            </h2>

            <a href="{{ $indexUrl }}" class="text-xs font-black text-slate-500 transition hover:text-brand-navy">
                Reset
            </a>
        </div>

        <div class="space-y-6">
            <fieldset>
                <legend class="text-sm font-black text-brand-ink">Status Program</legend>
                <div class="mt-3 space-y-2">
                    @foreach ($statusOptions as $option)
                        <label class="flex cursor-pointer items-center gap-3 text-sm font-semibold text-slate-600">
                            <input
                                type="checkbox"
                                name="status[]"
                                value="{{ $option['value'] }}"
                                @checked(in_array($option['value'], $selectedStatuses, true))
                                class="h-4 w-4 rounded border-slate-300 text-brand-navy focus:ring-brand-navy"
                            >
                            <span>{{ $option['label'] }} <span class="text-slate-400">({{ $option['count'] ?? 0 }})</span></span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <fieldset class="border-t border-slate-200 pt-5">
                <legend class="text-sm font-black text-brand-ink">Kategori</legend>
                <div class="mt-3 space-y-2">
                    @foreach ($categoryOptions as $option)
                        <label class="flex cursor-pointer items-center gap-3 text-sm font-semibold text-slate-600">
                            <input
                                type="checkbox"
                                name="category[]"
                                value="{{ $option['value'] }}"
                                @checked(in_array($option['value'], $selectedCategories, true))
                                class="h-4 w-4 rounded border-slate-300 text-brand-navy focus:ring-brand-navy"
                            >
                            <span>{{ $option['label'] }} <span class="text-slate-400">({{ $option['count'] ?? 0 }})</span></span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <fieldset class="border-t border-slate-200 pt-5">
                <legend class="text-sm font-black text-brand-ink">Format</legend>
                <div class="mt-3 space-y-2">
                    @foreach ($formatOptions as $option)
                        <label class="flex cursor-pointer items-center gap-3 text-sm font-semibold text-slate-600">
                            <input
                                type="checkbox"
                                name="format[]"
                                value="{{ $option['value'] }}"
                                @checked(in_array($option['value'], $selectedFormats, true))
                                class="h-4 w-4 rounded border-slate-300 text-brand-navy focus:ring-brand-navy"
                            >
                            <span>{{ $option['label'] }} <span class="text-slate-400">({{ $option['count'] ?? 0 }})</span></span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <fieldset class="border-t border-slate-200 pt-5">
                <legend class="text-sm font-black text-brand-ink">Level</legend>
                <div class="mt-3 space-y-2">
                    @foreach ($levelOptions as $option)
                        <label class="flex cursor-pointer items-center gap-3 text-sm font-semibold text-slate-600">
                            <input
                                type="checkbox"
                                name="level[]"
                                value="{{ $option['value'] }}"
                                @checked(in_array($option['value'], $selectedLevels, true))
                                class="h-4 w-4 rounded border-slate-300 text-brand-navy focus:ring-brand-navy"
                            >
                            <span>{{ $option['label'] }} <span class="text-slate-400">({{ $option['count'] ?? 0 }})</span></span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        </div>

        <div class="mt-7 space-y-3">
            <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-brand-navy px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-brand-navy/18 transition hover:-translate-y-0.5 hover:bg-[#102B4B]">
                Terapkan Filter
            </button>

            <a href="{{ $indexUrl }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-black text-brand-navy transition hover:border-brand-navy hover:bg-slate-50">
                Reset Filter
            </a>
        </div>
    </form>
</aside>
