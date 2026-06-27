@extends('layouts.app')

@section('title', 'Pencarian - Edulaw Project')

@section('content')
<main class="bg-brand-paper">
    <x-shared.page-header
        title="Pencarian"
        description="Cari konten website yang terhubung langsung dengan data panel admin."
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Pencarian'],
        ]"
    />

    <section class="py-14 lg:py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('search.index') }}" class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <div class="grid gap-3 sm:grid-cols-[1fr_auto]">
                    <input type="search" name="q" value="{{ $query }}" placeholder="Cari insight, publikasi, program, opportunity, multimedia..." class="rounded-xl border border-slate-200 px-4 py-2 text-sm">
                    <button type="submit" class="rounded-xl bg-brand-black px-5 py-2 text-sm font-bold text-white">Cari</button>
                </div>
            </form>

            @if ($query !== '')
                <div class="mt-8">
                    <p class="text-sm text-slate-600">{{ $results->count() }} hasil untuk <strong>{{ $query }}</strong></p>

                    <div class="mt-4 space-y-4">
                        @forelse ($results as $result)
                            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="edulaw-badge edulaw-badge-navy normal-case tracking-normal">{{ $result['type'] }}</span>
                                    <span class="edulaw-badge edulaw-badge-muted normal-case tracking-normal">{{ $result['meta'] }}</span>
                                </div>
                                <h3 class="mt-3 text-lg font-extrabold text-brand-ink">{{ $result['title'] }}</h3>
                                <p class="mt-2 text-sm text-slate-600">{{ $result['excerpt'] }}</p>
                                <p class="mt-2 text-xs text-slate-500">{{ $result['date'] }}</p>
                                <a href="{{ $result['url'] }}" @if(!empty($result['external'])) target="_blank" rel="noopener noreferrer" @endif class="mt-3 inline-flex text-sm font-bold text-brand-navy">Buka Hasil →</a>
                            </article>
                        @empty
                            <div class="rounded-2xl bg-white p-6 text-sm text-slate-600 shadow-sm ring-1 ring-slate-200">Tidak ada hasil untuk kata kunci ini.</div>
                        @endforelse
                    </div>
                </div>
            @else
                <div class="mt-8 rounded-2xl bg-white p-6 text-sm text-slate-600 shadow-sm ring-1 ring-slate-200">Masukkan kata kunci untuk mulai mencari konten.</div>
            @endif
        </div>
    </section>
</main>
@endsection
