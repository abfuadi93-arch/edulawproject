@props(['program'])

<section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="event-details-title">
    <p class="text-xs font-black uppercase tracking-[0.22em] text-brand-teal">Informasi Acara</p>
    <h2 id="event-details-title" class="mt-3 text-2xl font-black tracking-tight text-brand-navy">Jadwal & lokasi</h2>

    @if (in_array($program->event_status, ['EventCancelled', 'EventPostponed', 'EventRescheduled'], true))
        <p class="mt-4 rounded-xl bg-brand-mist px-4 py-3 text-sm font-bold leading-6 text-brand-navy">
            {{ match ($program->event_status) {
                'EventCancelled' => 'Acara dibatalkan. Jadwal di bawah adalah jadwal yang semula diumumkan.',
                'EventPostponed' => 'Acara ditunda. Jadwal di bawah adalah jadwal semula; tanggal baru belum diumumkan.',
                default => 'Acara dijadwalkan ulang. Jadwal di bawah merupakan jadwal terbaru.',
            } }}
        </p>
    @endif

    <dl class="mt-6 grid gap-6 sm:grid-cols-2">
        <div>
            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Tanggal Mulai</dt>
            <dd class="mt-2 font-black leading-7 text-brand-navy">
                @if ($program->eventDateValue())
                    <time datetime="{{ $program->eventDateValue() }}">{{ $program->eventDateLabel() }}</time>
                @else
                    Jadwal belum diumumkan
                @endif
            </dd>
        </div>
        @if ($program->eventDateValue(true))
            <div>
                <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Tanggal Selesai</dt>
                <dd class="mt-2 font-black leading-7 text-brand-navy"><time datetime="{{ $program->eventDateValue(true) }}">{{ $program->eventDateLabel(true) }}</time></dd>
            </div>
        @endif
        @if ($program->location || $program->online_url || $program->venue_address_label)
            <div class="min-w-0">
                <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Lokasi</dt>
                <dd class="mt-2 leading-7 text-slate-600">
                    @if ($program->location)<p class="font-black text-brand-navy">{{ $program->location }}</p>@endif
                    @if ($program->format !== 'online' && $program->venue_address_label)<p>{{ $program->venue_address_label }}</p>@endif
                    @if (in_array($program->format, ['online', 'hybrid'], true) && $program->online_url)
                        <a href="{{ $program->online_url }}" target="_blank" rel="noopener" class="mt-2 inline-block font-bold text-brand-teal underline underline-offset-4">Halaman Acara Daring ↗</a>
                    @endif
                </dd>
            </div>
        @endif
        <div class="min-w-0">
            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Penyelenggara</dt>
            <dd class="mt-2 font-black leading-7 text-brand-navy">
                @if ($program->organizer_website)
                    <a href="{{ $program->organizer_website }}" target="_blank" rel="noopener" class="underline decoration-brand-teal/40 underline-offset-4">{{ $program->organizer }} ↗</a>
                @else
                    {{ $program->organizer }}
                @endif
            </dd>
        </div>
    </dl>
</section>
