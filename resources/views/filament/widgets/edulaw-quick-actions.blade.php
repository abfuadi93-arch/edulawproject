<x-filament-widgets::widget>
    <section class="edulaw-admin-card">
        <div class="grid gap-4 p-5 lg:grid-cols-[1fr_auto] lg:items-center">
            <div class="min-w-0">
                <h2 class="text-xl font-black tracking-tight text-slate-950">
                    Hello, {{ $displayName }}
                </h2>
                <p class="mt-1 max-w-2xl text-sm leading-6 edulaw-admin-muted">
                    Manage editorials, publications, programs and website activity.
                </p>

                <div class="mt-4 max-w-2xl">
                    <label class="sr-only" for="dashboard-search">Dashboard search</label>
                    <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                        <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-4 w-4 shrink-0 text-slate-400" />
                        <input
                            id="dashboard-search"
                            type="search"
                            placeholder="Search editorial, publication, program, author, category..."
                            class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:ring-0"
                            readonly
                        >
                        <span class="hidden rounded-md border border-slate-200 bg-slate-50 px-2 py-1 text-[10px] font-black uppercase tracking-wide text-slate-400 sm:inline-flex">
                            Soon
                        </span>
                    </div>
                </div>
            </div>

            @if ($canCreateInsight || $canCreatePublication || $canCreateProgram)
                <div class="grid gap-2 sm:grid-cols-3 lg:min-w-[34rem]">
                    @if ($canCreateInsight)
                        <a href="{{ $insightCreateUrl }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-3 py-2.5 text-sm font-black text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700">
                            <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
                            New Editorial
                        </a>
                    @endif

                    @if ($canCreatePublication)
                        <a href="{{ $publicationCreateUrl }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-black text-slate-950 shadow-sm transition hover:border-emerald-200 hover:text-emerald-700">
                            <x-filament::icon icon="heroicon-o-document-plus" class="h-4 w-4" />
                            New Publication
                        </a>
                    @endif

                    @if ($canCreateProgram)
                        <a href="{{ $programCreateUrl }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-black text-slate-950 shadow-sm transition hover:border-orange-200 hover:text-orange-700">
                            <x-filament::icon icon="heroicon-o-academic-cap" class="h-4 w-4" />
                            New Program
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </section>
</x-filament-widgets::widget>
