@extends('layouts.app')

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $technicalValues = [
        'user',
        'admin',
        'administrator',
        'super admin',
        'role user',
        'old role',
        'old username',
        'username',
    ];

    $cleanPublicValue = function (?string $value, ?string $fallback = null) use ($technicalValues): ?string {
        $value = trim(strip_tags((string) $value));
        $normalized = Str::of($value)->lower()->squish()->toString();

        if ($value === '' || in_array($normalized, $technicalValues, true)) {
            return $fallback;
        }

        return $value;
    };

    $photoUrl = $author->photo_url;
    $publicPosition = $cleanPublicValue($author->position, 'Contributor');
    $publicInstitution = $cleanPublicValue($author->institution, 'Edulaw Project');
    $publicRole = match ($author->profile_role_key) {
        'founder', 'co_founder', 'manager', 'team' => 'Tim Edulaw',
        default => 'Tim Edulaw',
    };
    $heroBadge = $publicRole === 'Tim Edulaw' ? 'TIM EDULAW' : 'PROFIL PENULIS';
    $bioFallback = $publicRole === 'Tim Edulaw'
        ? "{$author->name} merupakan bagian dari Edulaw Project yang berkontribusi dalam produksi editorial, pembaruan regulasi, dan tulisan literasi hukum."
        : 'Profil penulis akan diperbarui secara berkala seiring dengan publikasi dan kontribusi penulis di Edulaw Project.';
    $bio = trim(strip_tags((string) $author->bio));
    $bioSummary = Str::limit($bio !== '' ? $bio : $bioFallback, 210);
    $metaDescription = Str::limit(
        $author->meta_description ?: ($bio !== '' ? $bio : collect([$publicPosition, $publicInstitution])->filter()->join(' - ')),
        180
    );
    $profileTitle = $author->seo_title ?: $author->name . ' - Profil';
@endphp

@section('title', $profileTitle)
@section('meta_description', $metaDescription)
@section('canonical_url', route('profiles.show', $author->slug))
@section('og_type', 'profile')
@section('og_image', $photoUrl ?: asset('images/hero/hero-edulaw.jpg'))
@section('og_image_alt', 'Foto profil ' . $author->name)

@push('head')
    <x-structured-data :data="\App\Support\StructuredData::person($author)" />
    <x-structured-data :data="\App\Support\StructuredData::breadcrumbs([
        ['name' => 'Beranda', 'url' => route('home')],
        ['name' => 'Tentang dan Tim', 'url' => route('about').'#tim'],
        ['name' => $author->name, 'url' => route('profiles.show', $author->slug)],
    ])" />
@endpush

@push('styles')
<style>
    .profile-page {
        --profile-navy: #102a4c;
        --profile-paper: #f6f8fb;
        --profile-white: #ffffff;
        --profile-gold: #d99a25;
        background: var(--profile-paper);
        color: var(--profile-navy);
    }

    .profile-shell {
        margin-inline: auto;
        width: min(100% - 2rem, 1180px);
    }

    .profile-hero {
        position: relative;
        overflow: hidden;
        background: var(--profile-navy);
        color: var(--profile-white);
    }

    .profile-hero::after {
        background:
            linear-gradient(90deg, rgba(217, 154, 37, .18), transparent 38%),
            linear-gradient(180deg, rgba(255, 255, 255, .08), transparent 54%);
        content: "";
        inset: 0;
        pointer-events: none;
        position: absolute;
    }

    .profile-hero__inner {
        display: grid;
        gap: 2rem;
        grid-template-columns: minmax(0, 1.08fr) minmax(300px, .92fr);
        min-height: 520px;
        padding-block: 4.75rem;
        position: relative;
        z-index: 1;
    }

    .profile-breadcrumb {
        align-items: center;
        color: rgba(255, 255, 255, .68);
        display: flex;
        flex-wrap: wrap;
        font-size: .78rem;
        font-weight: 800;
        gap: .55rem;
    }

    .profile-breadcrumb a {
        transition: color .2s ease;
    }

    .profile-breadcrumb a:hover {
        color: var(--profile-white);
    }

    .profile-badge {
        align-items: center;
        border: 1px solid rgba(217, 154, 37, .45);
        border-radius: 999px;
        color: rgba(255, 255, 255, .9);
        display: inline-flex;
        font-size: .68rem;
        font-weight: 900;
        gap: .45rem;
        letter-spacing: .16em;
        padding: .48rem .78rem;
        text-transform: uppercase;
        width: max-content;
    }

    .profile-badge::before {
        background: var(--profile-gold);
        border-radius: 999px;
        content: "";
        height: .45rem;
        width: .45rem;
    }

    .profile-hero__title {
        color: var(--profile-white);
        font-size: clamp(2.55rem, 6vw, 5.5rem);
        font-weight: 950;
        letter-spacing: 0;
        line-height: .92;
        margin-top: 1.15rem;
        max-width: 800px;
        text-shadow: 0 14px 34px rgba(0, 0, 0, .2);
    }

    .profile-hero__meta {
        color: rgba(255, 255, 255, .9);
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.7;
        margin-top: 1.15rem;
    }

    .profile-hero__position {
        color: var(--profile-gold);
    }

    .profile-hero__summary {
        color: rgba(255, 255, 255, .74);
        font-size: 1.05rem;
        line-height: 1.85;
        margin-top: 1.15rem;
        max-width: 660px;
    }

    .profile-photo-panel {
        align-self: center;
        margin-left: auto;
        max-width: 430px;
        position: relative;
        width: 100%;
    }

    .profile-photo-frame {
        aspect-ratio: 4 / 5;
        background: rgba(255, 255, 255, .08);
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 1.75rem;
        box-shadow: 0 28px 70px rgba(0, 0, 0, .28);
        overflow: hidden;
    }

    .profile-photo-frame img,
    .profile-photo-initials {
        display: block;
        height: 100%;
        object-fit: cover;
        width: 100%;
    }

    .profile-photo-initials {
        align-items: center;
        color: var(--profile-white);
        display: flex;
        font-size: 4rem;
        font-weight: 950;
        justify-content: center;
    }

    .profile-stats {
        bottom: -1.25rem;
        display: grid;
        gap: .65rem;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        left: 1.25rem;
        position: absolute;
        right: 1.25rem;
    }

    .profile-stat {
        background: rgba(255, 255, 255, .94);
        border: 1px solid rgba(255, 255, 255, .55);
        border-radius: .9rem;
        box-shadow: 0 12px 28px rgba(0, 0, 0, .14);
        min-width: 0;
        padding: .72rem .68rem;
    }

    .profile-stat strong {
        color: var(--profile-navy);
        display: block;
        font-size: 1.28rem;
        font-weight: 950;
        line-height: 1;
    }

    .profile-stat span {
        color: rgba(16, 42, 76, .58);
        display: block;
        font-size: .68rem;
        font-weight: 900;
        line-height: 1.25;
        margin-top: .35rem;
        text-transform: uppercase;
    }

    .profile-section {
        padding-block: 3rem 4.25rem;
    }

    .profile-layout {
        display: grid;
        gap: 1.75rem;
        grid-template-columns: 292px minmax(0, 1fr);
    }

    .profile-card {
        background: var(--profile-white);
        border: 1px solid rgba(16, 42, 76, .12);
        border-radius: 1.25rem;
        box-shadow: 0 14px 36px rgba(16, 42, 76, .06);
    }

    .profile-sidebar {
        align-self: start;
        display: grid;
        gap: 1rem;
        position: sticky;
        top: 6.5rem;
    }

    .profile-card__pad {
        padding: 1.35rem;
    }

    .profile-kicker {
        color: var(--profile-gold);
        font-size: .72rem;
        font-weight: 950;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .profile-heading {
        color: var(--profile-navy);
        font-size: clamp(1.55rem, 2vw, 2rem);
        font-weight: 950;
        letter-spacing: 0;
        line-height: 1.1;
    }

    .profile-copy {
        color: rgba(16, 42, 76, .68);
        font-size: .96rem;
        line-height: 1.85;
    }

    .profile-info-list {
        display: grid;
        gap: 1rem;
        margin-top: 1.15rem;
    }

    .profile-info-list dt {
        color: rgba(16, 42, 76, .44);
        font-size: .68rem;
        font-weight: 950;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .profile-info-list dd {
        color: var(--profile-navy);
        font-size: .92rem;
        font-weight: 850;
        line-height: 1.45;
        margin-top: .25rem;
    }

    .profile-chip-list {
        display: flex;
        flex-wrap: wrap;
        gap: .55rem;
        margin-top: .85rem;
    }

    .profile-chip {
        border: 1px solid rgba(217, 154, 37, .3);
        border-radius: 999px;
        color: var(--profile-navy);
        display: inline-flex;
        font-size: .76rem;
        font-weight: 850;
        line-height: 1;
        padding: .52rem .72rem;
    }

    .profile-main {
        display: grid;
        gap: 1.75rem;
        min-width: 0;
    }

    .profile-section-head {
        align-items: end;
        display: flex;
        gap: 1rem;
        justify-content: space-between;
        margin-bottom: 1.1rem;
    }

    .profile-section-link {
        color: var(--profile-gold);
        flex: none;
        font-size: .86rem;
        font-weight: 950;
        white-space: nowrap;
    }

    .profile-featured-article,
    .profile-compact-row,
    .profile-publication-card {
        border: 1px solid rgba(16, 42, 76, .12);
        border-radius: 1.15rem;
        color: inherit;
        min-width: 0;
        overflow: hidden;
        transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }

    .profile-featured-article:hover,
    .profile-compact-row:hover,
    .profile-publication-card:hover {
        border-color: rgba(16, 42, 76, .28);
        box-shadow: 0 18px 44px rgba(16, 42, 76, .08);
        transform: translateY(-2px);
    }

    .profile-featured-article {
        display: grid;
        grid-template-columns: minmax(190px, 240px) minmax(0, 1fr);
    }

    .profile-article-media {
        background: linear-gradient(135deg, var(--profile-navy), var(--profile-gold));
        overflow: hidden;
    }

    .profile-article-media img {
        display: block;
        height: 100%;
        object-fit: cover;
        width: 100%;
    }

    .profile-featured-article .profile-article-media {
        min-height: 190px;
    }

    .profile-featured-body {
        padding: 1.15rem;
    }

    .profile-card-meta {
        color: var(--profile-gold);
        font-size: .7rem;
        font-weight: 950;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .profile-card-title {
        color: var(--profile-navy);
        font-size: 1.12rem;
        font-weight: 950;
        letter-spacing: 0;
        line-height: 1.25;
        margin-top: .45rem;
    }

    .profile-card-desc {
        color: rgba(16, 42, 76, .64);
        display: -webkit-box;
        font-size: .9rem;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        line-height: 1.65;
        margin-top: .7rem;
        overflow: hidden;
    }

    .profile-card-foot {
        align-items: center;
        color: rgba(16, 42, 76, .5);
        display: flex;
        flex-wrap: wrap;
        font-size: .78rem;
        font-weight: 850;
        gap: .45rem;
        margin-top: .9rem;
    }

    .profile-compact-list {
        display: grid;
        gap: .72rem;
        margin-top: 1rem;
    }

    .profile-subhead {
        color: rgba(16, 42, 76, .5);
        font-size: .72rem;
        font-weight: 950;
        letter-spacing: .12em;
        margin-bottom: .1rem;
        text-transform: uppercase;
    }

    .profile-compact-row {
        align-items: center;
        display: grid;
        gap: .85rem;
        grid-template-columns: 96px minmax(0, 1fr);
        padding: .62rem;
    }

    .profile-compact-thumb {
        aspect-ratio: 6 / 5;
        border-radius: .82rem;
    }

    .profile-compact-title {
        color: var(--profile-navy);
        display: -webkit-box;
        font-size: .98rem;
        font-weight: 950;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        letter-spacing: 0;
        line-height: 1.28;
        overflow: hidden;
    }

    .profile-publication-list {
        display: grid;
        gap: .75rem;
    }

    .profile-publication-card {
        align-items: center;
        display: flex;
        gap: 1rem;
        justify-content: space-between;
        padding: 1rem;
    }

    .profile-publication-card__body {
        min-width: 0;
    }

    .profile-publication-link {
        align-items: center;
        border: 1px solid rgba(217, 154, 37, .34);
        border-radius: 999px;
        color: var(--profile-gold);
        display: inline-flex;
        flex: none;
        font-size: .78rem;
        font-weight: 950;
        min-height: 2.2rem;
        padding-inline: .85rem;
    }

    .profile-empty {
        align-items: center;
        border: 1px dashed rgba(16, 42, 76, .22);
        border-radius: 1.15rem;
        color: rgba(16, 42, 76, .66);
        display: grid;
        gap: .7rem;
        justify-items: center;
        padding: 1.35rem;
        text-align: center;
    }

    .profile-empty__icon {
        align-items: center;
        background: rgba(217, 154, 37, .12);
        border: 1px solid rgba(217, 154, 37, .22);
        border-radius: 999px;
        color: var(--profile-gold);
        display: flex;
        height: 3rem;
        justify-content: center;
        width: 3rem;
    }

    .profile-empty strong {
        color: var(--profile-navy);
        font-size: 1.05rem;
        font-weight: 950;
    }

    .profile-empty p {
        font-size: .9rem;
        line-height: 1.65;
        max-width: 520px;
    }

    @media (max-width: 980px) {
        .profile-hero__inner,
        .profile-layout {
            grid-template-columns: 1fr;
        }

        .profile-hero__inner {
            min-height: auto;
            padding-block: 3rem 4.25rem;
        }

        .profile-photo-panel {
            margin-left: 0;
            max-width: 360px;
        }

        .profile-sidebar {
            position: static;
        }
    }

    @media (max-width: 720px) {
        .profile-shell {
            width: min(100% - 1.25rem, 1180px);
        }

        .profile-hero__inner {
            gap: 1.6rem;
            padding-block: 2.35rem 3.9rem;
        }

        .profile-hero__title {
            font-size: clamp(2.4rem, 17vw, 4rem);
        }

        .profile-hero__summary {
            font-size: .96rem;
        }

        .profile-stats {
            gap: .5rem;
            left: .75rem;
            right: .75rem;
        }

        .profile-stat {
            border-radius: .85rem;
            padding: .75rem .58rem;
        }

        .profile-stat strong {
            font-size: 1.05rem;
        }

        .profile-stat span {
            font-size: .6rem;
        }

        .profile-section {
            padding-block: 2.1rem 3rem;
        }

        .profile-card__pad {
            padding: 1.05rem;
        }

        .profile-section-head {
            align-items: start;
            flex-direction: column;
            margin-bottom: .9rem;
        }

        .profile-featured-article {
            grid-template-columns: 1fr;
        }

        .profile-featured-article .profile-article-media {
            aspect-ratio: 16 / 10;
            min-height: 0;
        }

        .profile-compact-row {
            grid-template-columns: 82px minmax(0, 1fr);
        }

        .profile-publication-card {
            align-items: stretch;
            flex-direction: column;
        }

        .profile-publication-link {
            justify-content: center;
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
@php
    $initials = Str::of($author->name)
        ->explode(' ')
        ->filter()
        ->map(fn ($part) => Str::substr($part, 0, 1))
        ->take(2)
        ->implode('') ?: 'E';

    $bioParagraphs = collect(preg_split('/\R{2,}/', $bio) ?: [])
        ->map(fn ($paragraph) => trim($paragraph))
        ->filter()
        ->values();

    $joinedAt = $author->created_at ? $author->created_at->translatedFormat('M Y') : null;

    $parseTopics = function ($value) {
        if (is_array($value)) {
            return collect($value);
        }

        $value = trim((string) $value);

        if ($value === '') {
            return collect();
        }

        $decoded = json_decode($value, true);

        if (is_array($decoded)) {
            return collect($decoded);
        }

        return collect(preg_split('/[,;\n]+/', $value) ?: []);
    };

    $focusItems = $parseTopics($author->interests)
        ->merge($focusTopics ?? collect())
        ->map(fn ($topic) => trim(strip_tags((string) $topic)))
        ->filter()
        ->unique(fn ($topic) => Str::lower($topic))
        ->take(8)
        ->values();

    $socialLinks = collect($author->socialLinksMap());
    $profileLinks = collect([
        'Website' => $socialLinks->get('website'),
        'LinkedIn' => $socialLinks->get('linkedin'),
        'Google Scholar' => $socialLinks->get('google_scholar'),
        'ORCID' => filled($socialLinks->get('orcid'))
            ? (Str::startsWith($socialLinks->get('orcid'), ['http://', 'https://'])
                ? $socialLinks->get('orcid')
                : 'https://orcid.org/'.$socialLinks->get('orcid'))
            : null,
        'Scopus' => filled($socialLinks->get('scopus'))
            ? (Str::startsWith($socialLinks->get('scopus'), ['http://', 'https://'])
                ? $socialLinks->get('scopus')
                : 'https://www.scopus.com/authid/detail.uri?authorId='.urlencode($socialLinks->get('scopus')))
            : null,
        'Instagram' => $socialLinks->get('instagram'),
        'Twitter / X' => $socialLinks->get('twitter'),
        'YouTube' => $socialLinks->get('youtube'),
        'ResearchGate' => $socialLinks->get('researchgate'),
    ])->filter();

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

    $excerpt = fn ($value, int $limit = 150): string => Str::limit(
        trim(strip_tags((string) $value)) ?: 'Ringkasan konten sedang disiapkan.',
        $limit
    );
@endphp

<div class="profile-page">
    <section class="profile-hero">
        <div class="profile-shell profile-hero__inner">
            <div class="self-center">
                <nav class="profile-breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}">Beranda</a>
                    <span>/</span>
                    <span>Profil</span>
                    <span>/</span>
                    <span>{{ $author->name }}</span>
                </nav>

                <div class="mt-8">
                    <span class="profile-badge">{{ $heroBadge }}</span>
                    <h1 class="profile-hero__title">{{ $author->name }}</h1>
                    <p class="profile-hero__meta">
                        @if ($author->title)
                            <span>{{ $author->title }}</span><br>
                        @endif
                        <span class="profile-hero__position">{{ $publicPosition }}</span><br>
                        {{ $publicInstitution }}
                    </p>
                    <p class="profile-hero__summary">{{ $bioSummary }}</p>
                </div>
            </div>

            <div class="profile-photo-panel">
                <div class="profile-photo-frame">
                    @if ($photoUrl)
                        <img src="{{ $photoUrl }}" alt="Foto profil {{ $author->name }}">
                    @else
                        <div class="profile-photo-initials">{{ $initials }}</div>
                    @endif
                </div>

                <div class="profile-stats" aria-label="Statistik profil">
                    <div class="profile-stat">
                        <strong>{{ $totalInsights }}</strong>
                        <span>Tulisan</span>
                    </div>
                    <div class="profile-stat">
                        <strong>{{ $totalPublications }}</strong>
                        <span>Publikasi</span>
                    </div>
                    <div class="profile-stat">
                        <strong>{{ $author->is_active ? 'Aktif' : '-' }}</strong>
                        <span>{{ $publicRole }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="profile-section">
        <div class="profile-shell profile-layout">
            <aside class="profile-sidebar">
                <div class="profile-card profile-card__pad">
                    <p class="profile-kicker">Informasi Singkat</p>

                    <dl class="profile-info-list">
                        <div>
                            <dt>Posisi</dt>
                            <dd>{{ $publicPosition }}</dd>
                        </div>
                        <div>
                            <dt>Institusi</dt>
                            <dd>{{ $publicInstitution }}</dd>
                        </div>
                        @if ($author->location)
                            <div>
                                <dt>Lokasi</dt>
                                <dd>{{ $author->location }}</dd>
                            </div>
                        @endif
                        @if ($joinedAt)
                            <div>
                                <dt>Bergabung sejak</dt>
                                <dd>{{ $joinedAt }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt>Peran</dt>
                            <dd>{{ $publicRole }}</dd>
                        </div>
                    </dl>
                </div>

                @if ($profileLinks->isNotEmpty() || $author->email)
                    <div class="profile-card profile-card__pad">
                        <p class="profile-kicker">Kontak & Tautan</p>
                        <dl class="profile-info-list">
                            @if ($author->email)
                                <div>
                                    <dt>Email</dt>
                                    <!--email_off-->
                                    <dd><a href="mailto:{{ $author->email }}">{{ $author->email }}</a></dd>
                                    <!--/email_off-->
                                </div>
                            @endif
                            @foreach ($profileLinks as $label => $url)
                                <div>
                                    <dt>{{ $label }}</dt>
                                    <dd><a href="{{ $url }}" target="_blank" rel="noopener noreferrer">Buka profil</a></dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif

                @if ($focusItems->isNotEmpty())
                    <div class="profile-card profile-card__pad">
                        <p class="profile-kicker">Fokus Kajian</p>
                        <div class="profile-chip-list">
                            @foreach ($focusItems as $topic)
                                <span class="profile-chip">{{ $topic }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>

            <div class="profile-main">
                <section class="profile-card profile-card__pad">
                    <p class="profile-kicker">Biografi</p>
                    <h2 class="profile-heading mt-2">Tentang {{ $author->name }}</h2>

                    @if ($bioParagraphs->isNotEmpty())
                        <div class="edulaw-readable profile-copy mt-5">
                            @foreach ($bioParagraphs as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    @else
                        <p class="edulaw-readable profile-copy mt-5">{{ $bioFallback }}</p>
                    @endif
                </section>

                <section id="tulisan" class="profile-card profile-card__pad">
                    <div class="profile-section-head">
                        <div>
                            <p class="profile-kicker">Tulisan</p>
                            <h2 class="profile-heading mt-2">Tulisan Terbaru oleh {{ $author->name }}</h2>
                        </div>

                        @if ($totalInsights > 0)
                            <a class="profile-section-link" href="{{ route('insights.index', ['author' => $author->slug, 'archive' => 'latest']) }}#insight-archive">
                                Lihat semua tulisan &rarr;
                            </a>
                        @endif
                    </div>

                    @if ($insights->isNotEmpty())
                        @php
                            $featuredInsight = $insights->first();
                            $otherInsights = $insights->skip(1)->values();
                        @endphp

                        <a href="{{ route('insights.show', $featuredInsight->slug) }}" class="profile-featured-article">
                            <div class="profile-article-media">
                                @if ($featuredInsight->cover_image_url)
                                    <img src="{{ $featuredInsight->cover_image_url }}" alt="Sampul {{ $featuredInsight->title }}">
                                @endif
                            </div>
                            <div class="profile-featured-body">
                                <p class="profile-card-meta">{{ $featuredInsight->display_category }}</p>
                                <h3 class="profile-card-title">{{ $featuredInsight->title }}</h3>
                                <p class="profile-card-desc">{{ $excerpt($featuredInsight->excerpt ?: $featuredInsight->content, 165) }}</p>
                                <div class="profile-card-foot">
                                    <span>{{ $publishedDate($featuredInsight->published_at) }}</span>
                                    <span aria-hidden="true">&middot;</span>
                                    <span>{{ $featuredInsight->reading_time ? $featuredInsight->reading_time.' menit baca' : 'Editorial' }}</span>
                                </div>
                            </div>
                        </a>

                        @if ($otherInsights->isNotEmpty())
                            <div class="profile-compact-list">
                                <p class="profile-subhead">Tulisan lainnya</p>

                                @foreach ($otherInsights as $insight)
                                    <a href="{{ route('insights.show', $insight->slug) }}" class="profile-compact-row">
                                        <div class="profile-article-media profile-compact-thumb">
                                            @if ($insight->cover_image_url)
                                                <img src="{{ $insight->cover_image_url }}" alt="Sampul {{ $insight->title }}">
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <h3 class="profile-compact-title">{{ $insight->title }}</h3>
                                            <div class="profile-card-foot">
                                                <span>{{ $insight->display_category }}</span>
                                                <span aria-hidden="true">&middot;</span>
                                                <span>{{ $publishedDate($insight->published_at) }}</span>
                                                <span aria-hidden="true">&middot;</span>
                                                <span>{{ $insight->reading_time ? $insight->reading_time.' menit baca' : 'Editorial' }}</span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="profile-empty">
                            <span class="profile-empty__icon" aria-hidden="true">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                    <path d="M7 4h7l3 3v13H7V4Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    <path d="M14 4v4h4M9.5 12h5M9.5 15.5h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <strong>Belum ada tulisan</strong>
                            <p>Editorial yang ditulis oleh {{ $author->name }} akan tampil di sini.</p>
                        </div>
                    @endif
                </section>

                <section id="publikasi" class="profile-card profile-card__pad">
                    <div class="profile-section-head">
                        <div>
                            <p class="profile-kicker">Publikasi</p>
                            <h2 class="profile-heading mt-2">Publikasi oleh {{ $author->name }}</h2>
                        </div>

                        @if ($totalPublications > 0)
                            <span class="profile-section-link">{{ $totalPublications }} publikasi</span>
                        @endif
                    </div>

                    @if ($publications->isNotEmpty())
                        <div class="profile-publication-list">
                            @foreach ($publications as $publication)
                                <article class="profile-publication-card">
                                    <div class="profile-publication-card__body">
                                        <p class="profile-card-meta">{{ $publication->type?->name ?: 'Publikasi' }}</p>
                                        <h3 class="profile-card-title">{{ $publication->title }}</h3>
                                        <div class="profile-card-foot">
                                            <span>{{ $publishedDate($publication->published_at) }}</span>
                                            @if ($publication->page_count)
                                                <span aria-hidden="true">&middot;</span>
                                                <span>{{ $publication->page_count }} halaman</span>
                                            @endif
                                        </div>
                                    </div>
                                    <a href="{{ route('publications.show', $publication->slug) }}" class="profile-publication-link">
                                        Detail
                                    </a>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="profile-empty">
                            <span class="profile-empty__icon" aria-hidden="true">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                    <path d="M7 4h7l3 3v13H7V4Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    <path d="M14 4v4h4M9.5 12h5M9.5 15.5h3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <strong>Belum ada publikasi terkait</strong>
                            <p>Policy brief, kajian, atau publikasi yang melibatkan {{ $author->name }} akan tampil di sini.</p>
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </section>
</div>
@endsection
