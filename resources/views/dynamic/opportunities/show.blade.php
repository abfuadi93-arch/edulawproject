@extends('layouts.app')

@section('title', $opportunity->title.' - Edulaw Project')

@section('content')
@php
    $opportunityImage = $opportunity->poster_url ?: 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1600&q=85';
@endphp

<main class="bg-brand-paper">
    <x-shared.page-header
        :title="$opportunity->title"
        :description="$opportunity->excerpt"
        :eyebrow="$opportunity->display_type"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Opportunities', 'url' => route('opportunities.index')],
            ['label' => $opportunity->title],
        ]"
    />

    <section class="py-14 lg:py-20">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[minmax(0,1fr)_320px] lg:px-8">
            <article class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200">
                <img src="{{ $opportunityImage }}" alt="{{ $opportunity->title }}" class="h-72 w-full object-cover">

                <div class="p-6 sm:p-8">
                <h2 class="text-2xl font-extrabold text-brand-ink">Deskripsi</h2>
                <div class="prose prose-slate mt-5 max-w-none">
                    {!! $opportunity->description ?: nl2br(e($opportunity->excerpt)) !!}
                </div>

                @if (!empty($opportunity->eligibility))
                    <h3 class="mt-8 text-xl font-extrabold text-brand-ink">Persyaratan</h3>
                    <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-slate-700">
                        @foreach ($opportunity->eligibility as $item)
                            <li>{{ is_array($item) ? ($item['item'] ?? '-') : $item }}</li>
                        @endforeach
                    </ul>
                @endif

                @if (!empty($opportunity->benefits))
                    <h3 class="mt-8 text-xl font-extrabold text-brand-ink">Manfaat</h3>
                    <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-slate-700">
                        @foreach ($opportunity->benefits as $item)
                            <li>{{ is_array($item) ? ($item['item'] ?? '-') : $item }}</li>
                        @endforeach
                    </ul>
                @endif
                </div>
            </article>

            <aside class="space-y-4">
                <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <h3 class="text-sm font-black uppercase tracking-[0.15em] text-brand-navy">Ringkasan</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div><dt class="text-slate-500">Jenis</dt><dd class="font-bold text-brand-ink">{{ $opportunity->display_type }}</dd></div>
                        <div><dt class="text-slate-500">Status</dt><dd class="font-bold text-brand-ink">{{ $opportunity->display_status }}</dd></div>
                        <div><dt class="text-slate-500">Format</dt><dd class="font-bold text-brand-ink">{{ $opportunity->format ?: '-' }}</dd></div>
                        <div><dt class="text-slate-500">Lokasi</dt><dd class="font-bold text-brand-ink">{{ $opportunity->location ?: '-' }}</dd></div>
                        <div><dt class="text-slate-500">Batas akhir</dt><dd class="font-bold text-brand-ink">{{ optional($opportunity->deadline)->translatedFormat('d M Y') }}</dd></div>
                    </dl>

                    @if ($opportunity->application_link)
                        <a href="{{ $opportunity->application_link }}" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex w-full justify-center rounded-xl bg-brand-black px-4 py-2 text-sm font-bold text-white">Buka Pendaftaran</a>
                    @endif
                </div>
            </aside>
        </div>
    </section>

    <section class="bg-white py-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-extrabold text-brand-ink">Opportunity Terkait</h2>
            <div class="mt-6 grid gap-5 md:grid-cols-3">
                @forelse ($relatedOpportunities as $item)
                    <article class="rounded-2xl border border-slate-200 bg-white p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand-navy">{{ $item->display_type }}</p>
                        <h3 class="mt-2 font-extrabold text-brand-ink">{{ $item->title }}</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ $item->excerpt }}</p>
                        <a href="{{ route('opportunities.show', $item->slug) }}" class="mt-3 inline-flex text-sm font-bold text-brand-navy">Lihat Detail →</a>
                    </article>
                @empty
                    <p class="text-sm text-slate-600">Tidak ada opportunity terkait.</p>
                @endforelse
            </div>
        </div>
    </section>
</main>
@endsection
