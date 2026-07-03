@extends('layouts.app')

@section('title', $author->name . ' - Profil Edulaw Project')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($author->bio ?: $author->affiliation_label), 160))
@section('og_title', $author->name . ' - Profil Edulaw Project')
@section('og_description', \Illuminate\Support\Str::limit(strip_tags($author->bio ?: $author->affiliation_label), 180))
@section('og_image', $author->photo_url ?: asset('images/hero/hero-edulaw.jpg'))
@section('twitter_title', $author->name . ' - Profil Edulaw Project')
@section('twitter_description', \Illuminate\Support\Str::limit(strip_tags($author->bio ?: $author->affiliation_label), 180))
@section('twitter_image', $author->photo_url ?: asset('images/hero/hero-edulaw.jpg'))

@push('styles')
<style>
    .profile-page {
        --profile-navy: #06132a;
        --profile-white: #ffffff;
        --profile-paper: #f6f8fb;
        --profile-amber: #d99a25;
        background: var(--profile-paper);
        color: var(--profile-navy);
    }

    .profile-card {
        border: 1px solid rgba(6, 19, 42, .12);
        background: var(--profile-white);
        box-shadow: 0 1px 2px rgba(6, 19, 42, .05);
    }

    .profile-item-card {
        border: 1px solid rgba(6, 19, 42, .12);
        background: var(--profile-white);
        transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
    }

    .profile-item-card:hover {
        border-color: rgba(6, 19, 42, .32);
        box-shadow: 0 18px 42px rgba(6, 19, 42, .08);
        transform: translateY(-2px);
    }

    .profile-label {
        color: var(--profile-navy);
    }

    .profile-accent {
        color: var(--profile-amber);
    }

    .profile-muted {
        color: rgba(6, 19, 42, .64);
    }

    .profile-soft {
        color: rgba(6, 19, 42, .44);
    }

    .profile-rule {
        border-color: rgba(6, 19, 42, .10);
    }

    .profile-chip {
        border: 1px solid rgba(6, 19, 42, .14);
        color: var(--profile-navy);
    }

    .profile-chip:hover {
        border-color: var(--profile-navy);
        background: var(--profile-navy);
        color: var(--profile-white);
    }

    .profile-empty {
        border: 1px dashed rgba(6, 19, 42, .22);
        background: rgba(255, 255, 255, .62);
        color: rgba(6, 19, 42, .64);
    }
</style>
@endpush

@section('content')
@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $photoUrl = $author->photo_url;
    $initials = Str::of($author->name)
        ->explode(' ')
        ->filter()
        ->map(fn ($part) => Str::substr($part, 0, 1))
        ->take(2)
        ->implode('') ?: 'E';

    $affiliation = collect([$author->position, $author->institution])->filter()->join(' · ');
    $bio = trim(strip_tags((string) $author->bio));
    $bioParagraphs = collect(preg_split('/\R{2,}/', $bio) ?: [])
        ->map(fn ($paragraph) => trim($paragraph))
        ->filter()
        ->values();

    $socialLinks = collect($author->social_links ?? [])
        ->filter(fn ($link) => filled($link['url'] ?? null))
        ->map(fn ($link) => [
            'platform' => $link['platform'] ?? 'Website',
            'url' => $link['url'],
        ])
        ->values();

    $publishedDate = function ($date): string {
        if (! $date) {
            return 'Belum dijadwalkan';
        }

        try {
            return $date instanceof Carbon
                ? $date->translatedFormat('d M Y')
                : Carbon::parse($date)->translatedFormat('d M Y');
        } catch (\Throwable $e) {
            return (string) $date;
        }
    };

    $excerpt = fn ($value, int $limit = 145): string => Str::limit(
        trim(strip_tags((string) $value)) ?: 'Ringkasan konten sedang disiapkan.',
        $limit
    );
@endphp

<main class="profile-page">
    <x-shared.page-header
        :title="$author->name"
        :compact="true"
        eyebrow="Profil"
        :description="$affiliation ?: ($author->profile_type_label ?: 'Edulaw Project')"
        background-image="https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1800&q=85"
        background-alt="Profil Edulaw Project"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Profil'],
            ['label' => $author->name],
        ]"
    >
        <div class="flex flex-col items-end gap-4">
            @if ($photoUrl)
                <img
                    src="{{ $photoUrl }}"
                    alt="Foto profil {{ $author->name }}"
                    class="h-24 w-24 rounded-2xl object-cover ring-2 ring-white/35"
                >
            @else
                <div class="grid h-24 w-24 place-items-center rounded-2xl bg-white/12 text-2xl font-black text-white ring-1 ring-white/20">
                    {{ $initials }}
                </div>
            @endif

            <div class="flex flex-wrap justify-end gap-2">
                <span class="rounded-full border border-white/18 bg-white/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.14em] text-white/84">
                    {{ $author->profile_type_label ?: 'Profil Edulaw' }}
                </span>
                <span class="rounded-full border border-white/18 bg-white/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.14em] text-white/84">
                    {{ $insights->total() }} Tulisan
                </span>
                <span class="rounded-full border border-white/18 bg-white/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.14em] text-white/84">
                    {{ $publications->total() }} Publikasi
                </span>
            </div>
        </div>
    </x-shared.page-header>

    <section class="py-10 lg:py-14">
        <div class="mx-auto grid max-w-7xl gap-7 px-4 sm:px-6 lg:grid-cols-[320px_minmax(0,1fr)] lg:px-8">
            <aside class="self-start lg:sticky lg:top-28">
                <div class="profile-card rounded-2xl p-6">
                    <p class="profile-label text-[10px] font-black uppercase tracking-[0.2em]">
                        Biografi
                    </p>

                    @if ($bioParagraphs->isNotEmpty())
                        <div class="profile-muted mt-4 space-y-4 text-sm leading-7">
                            @foreach ($bioParagraphs as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    @else
                        <p class="profile-muted mt-4 text-sm leading-7">
                            Biografi profil ini belum tersedia.
                        </p>
                    @endif

                    <div class="profile-rule mt-6 grid gap-3 border-t pt-5">
                        @if ($author->position)
                            <div>
                                <span class="profile-soft block text-[10px] font-black uppercase tracking-[0.16em]">Posisi</span>
                                <strong class="mt-1 block text-sm">{{ $author->position }}</strong>
                            </div>
                        @endif

                        @if ($author->institution)
                            <div>
                                <span class="profile-soft block text-[10px] font-black uppercase tracking-[0.16em]">Institusi</span>
                                <strong class="mt-1 block text-sm">{{ $author->institution }}</strong>
                            </div>
                        @endif
                    </div>

                    @if ($socialLinks->isNotEmpty())
                        <div class="profile-rule mt-6 border-t pt-5">
                            <p class="profile-label text-[10px] font-black uppercase tracking-[0.2em]">
                                Tautan
                            </p>

                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($socialLinks as $link)
                                    <a
                                        href="{{ $link['url'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="profile-chip rounded-full px-3 py-1.5 text-xs font-black transition"
                                    >
                                        {{ $link['platform'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </aside>

            <div class="grid gap-8">
                <section class="profile-card rounded-2xl p-5 sm:p-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="profile-label text-xs font-black uppercase tracking-[0.18em]">
                                Daftar Tulisan
                            </p>
                            <h2 class="mt-2 text-2xl font-black tracking-tight">
                                Insight oleh {{ $author->name }}
                            </h2>
                        </div>

                        <span class="profile-muted text-sm font-bold">
                            {{ $insights->total() }} artikel
                        </span>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        @forelse ($insights as $insight)
                            <article class="profile-item-card group rounded-xl p-4">
                                <p class="profile-accent text-[10px] font-black uppercase tracking-[0.16em]">
                                    {{ $insight->display_category }}
                                </p>
                                <h3 class="mt-2 line-clamp-2 text-lg font-black leading-snug">
                                    <a href="{{ route('insights.show', $insight->slug) }}">
                                        {{ $insight->title }}
                                    </a>
                                </h3>
                                <p class="profile-muted mt-3 line-clamp-3 text-sm leading-6">
                                    {{ $excerpt($insight->excerpt ?: $insight->content) }}
                                </p>
                                <div class="profile-muted mt-4 flex flex-wrap items-center gap-2 text-xs font-bold">
                                    <span>{{ $publishedDate($insight->published_at) }}</span>
                                    <span class="h-1 w-1 rounded-full" style="background: rgba(6, 19, 42, .24);"></span>
                                    <span>{{ $insight->reading_time ? $insight->reading_time.' menit baca' : 'Insight' }}</span>
                                </div>
                            </article>
                        @empty
                            <div class="profile-empty rounded-xl p-6 text-sm leading-6 md:col-span-2">
                                Belum ada tulisan Insight yang dipublikasikan untuk profil ini.
                            </div>
                        @endforelse
                    </div>

                    @if ($insights->hasPages())
                        <div class="profile-rule mt-6 border-t pt-5">
                            {{ $insights->links() }}
                        </div>
                    @endif
                </section>

                <section class="profile-card rounded-2xl p-5 sm:p-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="profile-label text-xs font-black uppercase tracking-[0.18em]">
                                Daftar Publikasi
                            </p>
                            <h2 class="mt-2 text-2xl font-black tracking-tight">
                                Riset dan publikasi terkait
                            </h2>
                        </div>

                        <span class="profile-muted text-sm font-bold">
                            {{ $publications->total() }} publikasi
                        </span>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        @forelse ($publications as $publication)
                            <article class="profile-item-card group rounded-xl p-4">
                                <p class="profile-accent text-[10px] font-black uppercase tracking-[0.16em]">
                                    {{ $publication->type?->name ?: 'Publikasi' }}
                                </p>
                                <h3 class="mt-2 line-clamp-2 text-lg font-black leading-snug">
                                    <a href="{{ route('publications.show', $publication->slug) }}">
                                        {{ $publication->title }}
                                    </a>
                                </h3>
                                <p class="profile-muted mt-3 line-clamp-3 text-sm leading-6">
                                    {{ $excerpt($publication->excerpt ?: $publication->description) }}
                                </p>
                                <div class="profile-muted mt-4 flex flex-wrap items-center gap-2 text-xs font-bold">
                                    <span>{{ $publishedDate($publication->published_at) }}</span>
                                    @if ($publication->page_count)
                                        <span class="h-1 w-1 rounded-full" style="background: rgba(6, 19, 42, .24);"></span>
                                        <span>{{ $publication->page_count }} halaman</span>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="profile-empty rounded-xl p-6 text-sm leading-6 md:col-span-2">
                                Belum ada publikasi yang dipublikasikan untuk profil ini.
                            </div>
                        @endforelse
                    </div>

                    @if ($publications->hasPages())
                        <div class="profile-rule mt-6 border-t pt-5">
                            {{ $publications->links() }}
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </section>
</main>
@endsection
