<x-filament-panels::page>
    @php
        $record = $this->getRecord()->loadMissing([
            'authors',
            'category',
            'tags',
            'creator',
            'activeEditorAssignment.editor',
        ]);
        $assignment = $record->activeEditorAssignment;
        $isWriter = auth()->id() === $record->created_by && ! auth()->user()?->can('view_all_editorial_submissions');
        $notes = $record->editorialNotes()
            ->when($isWriter, fn ($query) => $query->visibleToWriter())
            ->with('user:id,name')
            ->latest()
            ->get();
        $decisions = $record->editorialDecisions()->with('decidedBy:id,name')->get();
        $activities = $record->editorialActivities()->with('actor:id,name')->get();
        $revisions = $record->revisions()->latest('revision_number')->get();
    @endphp

    <div class="space-y-5">
        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan status editorial">
            <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Status</div>
                <div class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $record->status->label() }}</div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Tahap Workflow</div>
                <div class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $record->workflow_stage->label() }}</div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Editor Aktif</div>
                <div class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $assignment?->editor?->name ?? 'Belum ditugaskan' }}</div>
                <div class="text-xs text-gray-500">{{ $assignment?->status?->label() ?? 'Tidak ada assignment aktif' }}</div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Tenggat</div>
                <div class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $assignment?->due_at?->locale('id')->translatedFormat('d M Y, H:i') ?? 'Belum ditetapkan' }}</div>
            </div>
        </section>

        <div class="grid items-start gap-4 md:grid-cols-3">
            <main class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900 md:col-span-2">
                <header class="border-b border-gray-200 px-5 py-5 dark:border-white/10 md:px-7">
                    <div class="text-xs font-semibold uppercase tracking-wide text-primary-600 dark:text-primary-400">Naskah Insight</div>
                    <h2 class="mt-2 text-xl font-bold leading-tight text-gray-950 dark:text-white md:text-2xl">{{ $record->title }}</h2>
                    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                        <div><dt class="text-xs text-gray-500">Penulis</dt><dd class="mt-1 font-medium">{{ $record->authors->pluck('name')->join(', ') ?: '—' }}</dd></div>
                        <div><dt class="text-xs text-gray-500">Kategori</dt><dd class="mt-1 font-medium">{{ $record->category?->name ?? '—' }}</dd></div>
                        <div><dt class="text-xs text-gray-500">Tag</dt><dd class="mt-1 font-medium">{{ $record->tags->pluck('name')->join(', ') ?: '—' }}</dd></div>
                    </dl>
                </header>

                @if ($record->cover_image)
                    <div class="border-b border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/5 md:p-5">
                        <img src="{{ $record->cover_image_url }}" alt="Cover {{ $record->title }}" class="max-h-72 w-full rounded-lg object-cover object-center">
                    </div>
                @endif

                <div class="space-y-6 px-5 py-6 md:px-7 md:py-8">
                    @if ($record->excerpt)
                        <div class="rounded-lg border-l-4 border-primary-500 bg-primary-50 px-4 py-3 text-sm leading-relaxed text-gray-700 dark:bg-primary-500/10 dark:text-gray-200">
                            {{ $record->excerpt }}
                        </div>
                    @endif

                    <article class="prose prose-sm max-w-none leading-relaxed dark:prose-invert md:prose-base">
                        {!! str($record->content)->sanitizeHtml() !!}
                    </article>
                </div>
            </main>

            <aside class="space-y-4 md:sticky md:top-4">
                <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                    <h3 class="font-semibold text-gray-950 dark:text-white">Ringkasan Alur Editorial</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div><dt class="text-xs text-gray-500">Tanggal submission</dt><dd class="mt-1 font-medium">{{ $record->submitted_at?->locale('id')->translatedFormat('d M Y, H:i') ?? '—' }}</dd></div>
                        <div><dt class="text-xs text-gray-500">Aktivitas terakhir</dt><dd class="mt-1 font-medium">{{ $activities->first()?->created_at?->locale('id')->translatedFormat('d M Y, H:i') ?? $record->updated_at?->locale('id')->translatedFormat('d M Y, H:i') }}</dd></div>
                        <div><dt class="text-xs text-gray-500">Assignment dibuat</dt><dd class="mt-1 font-medium">{{ $assignment?->assigned_at?->locale('id')->translatedFormat('d M Y, H:i') ?? '—' }}</dd></div>
                        <div><dt class="text-xs text-gray-500">Catatan penugasan</dt><dd class="mt-1 text-gray-700 dark:text-gray-200">{{ $assignment?->assignment_note ?: '—' }}</dd></div>
                    </dl>
                </section>

                <details open class="group rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
                    <summary class="flex cursor-pointer list-none items-center justify-between px-4 py-3 font-semibold text-gray-950 dark:text-white">
                        <span>Catatan Review <span class="ml-1 text-xs font-normal text-gray-500">{{ $notes->count() }}</span></span>
                        <span class="text-gray-400 transition group-open:rotate-180">⌄</span>
                    </summary>
                    <div class="max-h-72 space-y-3 overflow-y-auto border-t border-gray-200 p-4 dark:border-white/10">
                        @forelse ($notes as $note)
                            <article class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                                <div class="text-xs font-medium text-gray-600 dark:text-gray-300">{{ $note->user?->name ?? 'Tim Editorial' }} · {{ $note->created_at->locale('id')->translatedFormat('d M Y, H:i') }}</div>
                                <p class="mt-2 whitespace-pre-line text-sm text-gray-700 dark:text-gray-200">{{ $note->note }}</p>
                            </article>
                        @empty
                            <p class="text-sm text-gray-500">Belum ada catatan review.</p>
                        @endforelse
                    </div>
                </details>

                <details class="group rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
                    <summary class="flex cursor-pointer list-none items-center justify-between px-4 py-3 font-semibold text-gray-950 dark:text-white">
                        <span>Keputusan Editorial <span class="ml-1 text-xs font-normal text-gray-500">{{ $decisions->count() }}</span></span>
                        <span class="text-gray-400 transition group-open:rotate-180">⌄</span>
                    </summary>
                    <div class="max-h-72 space-y-3 overflow-y-auto border-t border-gray-200 p-4 dark:border-white/10">
                        @forelse ($decisions as $decision)
                            <article class="border-l-2 border-primary-500 pl-3">
                                <div class="text-sm font-medium">{{ $decision->decision->label() }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ $decision->decidedBy?->name ?? 'Sistem' }} · {{ $decision->decided_at->locale('id')->translatedFormat('d M Y, H:i') }}</div>
                                @if ($decision->decision_note)<p class="mt-2 text-sm">{{ $decision->decision_note }}</p>@endif
                            </article>
                        @empty
                            <p class="text-sm text-gray-500">Belum ada keputusan editorial.</p>
                        @endforelse
                    </div>
                </details>

                <details class="group rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
                    <summary class="flex cursor-pointer list-none items-center justify-between px-4 py-3 font-semibold text-gray-950 dark:text-white">
                        <span>Audit History <span class="ml-1 text-xs font-normal text-gray-500">{{ $activities->count() }}</span></span>
                        <span class="text-gray-400 transition group-open:rotate-180">⌄</span>
                    </summary>
                    <div class="max-h-80 space-y-4 overflow-y-auto border-t border-gray-200 p-4 dark:border-white/10">
                        @forelse ($activities as $activity)
                            <article class="border-l-2 border-gray-200 pl-3 dark:border-white/10">
                                <div class="text-sm font-medium">{{ str($activity->event)->replace('_', ' ')->headline() }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ $activity->actor?->name ?? 'Sistem' }} · {{ $activity->created_at->locale('id')->translatedFormat('d M Y, H:i') }}</div>
                                <p class="mt-1 text-sm text-gray-700 dark:text-gray-200">{{ $activity->description }}</p>
                            </article>
                        @empty
                            <p class="text-sm text-gray-500">Belum ada aktivitas tercatat.</p>
                        @endforelse
                    </div>
                </details>

                <section class="rounded-xl border border-gray-200 bg-white p-4 text-sm shadow-sm dark:border-white/10 dark:bg-gray-900">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="font-semibold text-gray-950 dark:text-white">Versi Naskah</h3>
                            <p class="mt-1 text-gray-500">{{ $revisions->count() }} versi tersimpan</p>
                        </div>
                        <div class="text-right">
                            <h3 class="font-semibold text-gray-950 dark:text-white">Publikasi</h3>
                            <p class="mt-1 text-gray-500">{{ $record->status->label() }}</p>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-filament-panels::page>
