@extends('layouts.app')

@section('title', 'Multimedia - Edulaw Project')

@section('content')
@php
    $multimediaCollection = $multimediaItems ?? collect();
    $mediaItems = $multimediaCollection instanceof \Illuminate\Pagination\AbstractPaginator
        ? $multimediaCollection->getCollection()
        : collect($multimediaCollection);

    $shortItems = collect($shortMultimedia ?? []);
    $selectedTypes = collect($selectedTypes ?? request('type', []))
        ->flatten()
        ->filter(fn ($value) => filled($value))
        ->values()
        ->all();
    $selectedType = $selectedTypes[0] ?? null;

    $indexUrl = route('multimedia.index');
    $contactUrl = route('contact.index');
    $collaborationUrl = route('collaboration.index');

    $mediaUrl = fn ($item) => $item?->media_url ?: $item?->embed_url;
    $isExternalUrl = fn ($url) => filled($url) && \Illuminate\Support\Str::startsWith($url, ['http://', 'https://']);
    $contentUrl = fn ($item) => $mediaUrl($item) ?: $indexUrl;
    $itemDate = fn ($item) => optional($item?->published_at)->translatedFormat('d M Y') ?: 'Belum terjadwal';
    $itemMeta = fn ($item) => $item?->duration ?: $itemDate($item);
    $itemDescription = fn ($item, int $limit = 132) => \Illuminate\Support\Str::limit(
        trim(strip_tags((string) $item?->description)) ?: 'Konten multimedia Edulaw Project tentang hukum, konstitusi, demokrasi, dan kebijakan publik.',
        $limit
    );

    $categoryFilters = [
        ['label' => 'Semua', 'type' => null],
        ['label' => 'Video', 'type' => 'video'],
        ['label' => 'Podcast', 'type' => 'podcast'],
        ['label' => 'Dokumentasi', 'type' => 'documentation'],
        ['label' => 'Shorts', 'type' => 'shorts'],
        ['label' => 'Webinar', 'type' => 'webinar'],
    ];

    $filterUrl = fn ($type) => filled($type)
        ? route('multimedia.index', ['type' => $type])
        : route('multimedia.index');

    $mediaTone = function ($type) {
        return match ($type) {
            'podcast' => [
                'visual' => 'from-brand-navy via-[#164d63] to-teal-500',
                'badge' => 'bg-teal-50 text-teal-700 border-teal-100',
                'dot' => 'bg-teal-500',
            ],
            'documentation', 'gallery' => [
                'visual' => 'from-brand-navy via-slate-700 to-slate-500',
                'badge' => 'bg-slate-50 text-slate-700 border-slate-200',
                'dot' => 'bg-slate-500',
            ],
            'shorts', 'reels' => [
                'visual' => 'from-brand-navy via-[#7b2948] to-brand-coral',
                'badge' => 'bg-rose-50 text-rose-700 border-rose-100',
                'dot' => 'bg-brand-coral',
            ],
            'webinar' => [
                'visual' => 'from-brand-navy via-[#123d68] to-brand-amber',
                'badge' => 'bg-brand-amber/15 text-brand-navy border-brand-amber/30',
                'dot' => 'bg-brand-amber',
            ],
            default => [
                'visual' => 'from-brand-navy via-[#123d68] to-[#28659d]',
                'badge' => 'bg-blue-50 text-blue-700 border-blue-100',
                'dot' => 'bg-blue-500',
            ],
        };
    };

    $serials = [
        [
            'title' => 'Diksi',
            'description' => 'Membedah istilah hukum agar lebih dekat dengan bahasa publik.',
            'label' => 'Serial istilah hukum',
            'href' => route('multimedia.index', ['type' => 'shorts']),
            'accent' => 'bg-teal-50 text-teal-700 border-teal-100',
        ],
        [
            'title' => 'Gali Putusan',
            'description' => 'Membaca putusan penting dan dampaknya bagi hak warga negara.',
            'label' => 'Analisis putusan',
            'href' => route('multimedia.index', ['type' => 'video']),
            'accent' => 'bg-blue-50 text-blue-700 border-blue-100',
        ],
        [
            'title' => 'Hukum dalam 60 Detik',
            'description' => 'Penjelasan cepat tentang konsep hukum yang sering muncul di ruang publik.',
            'label' => 'Format pendek',
            'href' => route('multimedia.index', ['type' => 'shorts']),
            'accent' => 'bg-rose-50 text-rose-700 border-rose-100',
        ],
        [
            'title' => 'Edulaw Talks',
            'description' => 'Percakapan bersama akademisi, peneliti, dan praktisi hukum.',
            'label' => 'Podcast dan webinar',
            'href' => route('multimedia.index', ['type' => 'podcast']),
            'accent' => 'bg-brand-amber/15 text-brand-navy border-brand-amber/30',
        ],
        [
            'title' => 'Kelas Konstitusi',
            'description' => 'Kelas tematik tentang konstitusi, demokrasi, dan kebijakan publik.',
            'label' => 'Kelas edukasi',
            'href' => route('multimedia.index', ['type' => 'webinar']),
            'accent' => 'bg-slate-50 text-slate-700 border-slate-200',
        ],
    ];

    $topics = [
        ['title' => 'Konstitusi', 'tone' => 'text-brand-navy bg-blue-50'],
        ['title' => 'Mahkamah Konstitusi', 'tone' => 'text-teal-700 bg-teal-50'],
        ['title' => 'Pemilu dan Demokrasi', 'tone' => 'text-brand-coral bg-brand-coral-soft'],
        ['title' => 'Hak Konstitusional', 'tone' => 'text-brand-navy bg-brand-amber/15'],
        ['title' => 'Hukum Digital', 'tone' => 'text-slate-700 bg-slate-100'],
        ['title' => 'Kebijakan Publik', 'tone' => 'text-teal-700 bg-teal-50'],
    ];
@endphp

<main class="bg-[#f6f8fb] text-brand-ink">
    <x-shared.page-header
        title="Video, Podcast, dan Dokumentasi Literasi Hukum"
        :compact="true"
        eyebrow="Multimedia Edulaw"
        description="Kumpulan konten audiovisual Edulaw untuk memahami hukum, konstitusi, demokrasi, dan kebijakan publik secara lebih dekat dan mudah diakses."
        background-image="https://images.unsplash.com/photo-1551818255-e6e10975bc17?auto=format&fit=crop&w=1800&q=85"
        background-alt="Konten audiovisual literasi hukum Edulaw"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Multimedia'],
        ]"
    >
        <div class="flex flex-col gap-3 sm:flex-row">
            <a
                href="#konten-terbaru"
                class="inline-flex min-h-12 items-center justify-center rounded-full bg-brand-amber px-6 py-3 text-sm font-black text-brand-ink shadow-lg shadow-black/20 transition hover:-translate-y-0.5 hover:bg-[#D99A25]"
            >
                Lihat Konten Terbaru
            </a>

            <a
                href="#serial-edulaw"
                class="inline-flex min-h-12 items-center justify-center rounded-full border border-white/25 bg-white/10 px-6 py-3 text-sm font-black text-white backdrop-blur transition hover:-translate-y-0.5 hover:border-brand-amber hover:bg-white/15"
            >
                Jelajahi Serial
            </a>
        </div>
    </x-shared.page-header>

    <section class="border-b border-slate-200 bg-white py-5">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex gap-2 overflow-x-auto pb-1">
                @foreach ($categoryFilters as $filter)
                    @php
                        $isActive = blank($filter['type'])
                            ? blank($selectedType)
                            : $selectedType === $filter['type'];
                    @endphp

                    <a
                        href="{{ $filterUrl($filter['type']) }}"
                        class="shrink-0 rounded-full border px-4 py-2 text-xs font-black uppercase tracking-[0.12em] transition
                            {{ $isActive
                                ? 'border-brand-navy bg-brand-navy text-white shadow-sm'
                                : 'border-slate-200 bg-white text-slate-600 hover:border-brand-navy/30 hover:text-brand-navy' }}"
                    >
                        {{ $filter['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section id="konten-terbaru" class="py-12 lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                        Konten Terbaru
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-brand-ink sm:text-3xl">
                        Tonton dan dengarkan pembahasan hukum
                    </h2>

                    <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">
                        Tonton dan dengarkan pembahasan hukum dalam format yang lebih ringan dan visual.
                    </p>
                </div>

                @if ($multimediaCollection instanceof \Illuminate\Pagination\AbstractPaginator && $multimediaCollection->total())
                    <p class="rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-black uppercase tracking-[0.12em] text-slate-500 shadow-sm">
                        {{ $multimediaCollection->total() }} konten
                    </p>
                @endif
            </div>

            @if ($mediaItems->isNotEmpty())
                <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($mediaItems as $item)
                        @php
                            $tone = $mediaTone($item->type);
                            $thumbnail = $item->thumbnail_url;
                            $url = $contentUrl($item);
                        @endphp

                        <article class="group flex h-full overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-900/5 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/10">
                            <a
                                href="{{ $url }}"
                                @if ($isExternalUrl($url)) target="_blank" rel="noopener noreferrer" @endif
                                class="flex h-full w-full flex-col"
                            >
                                <div class="relative aspect-video overflow-hidden bg-linear-to-br {{ $tone['visual'] }}">
                                    @if ($thumbnail)
                                        <img
                                            src="{{ $thumbnail }}"
                                            alt="{{ $item->title }}"
                                            class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                        >
                                        <div class="absolute inset-0 bg-linear-to-t from-brand-navy/65 via-brand-navy/10 to-transparent"></div>
                                    @else
                                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(45,212,191,0.45),transparent_34%)]"></div>
                                    @endif

                                    <span class="absolute left-4 top-4 rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] {{ $tone['badge'] }}">
                                        {{ $item->display_type }}
                                    </span>

                                    <span class="absolute inset-0 m-auto flex h-12 w-12 items-center justify-center rounded-full bg-white/92 text-brand-navy shadow-lg transition group-hover:scale-105 group-hover:bg-brand-amber">
                                        <svg class="ml-0.5 h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M8 5v14l11-7L8 5Z"/>
                                        </svg>
                                    </span>
                                </div>

                                <div class="flex flex-1 flex-col p-5">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-brand-navy">
                                            {{ $item->display_platform }}
                                        </p>

                                        <p class="text-xs font-bold text-slate-400">
                                            {{ $itemMeta($item) }}
                                        </p>
                                    </div>

                                    <h3 class="mt-3 line-clamp-2 min-h-14 text-lg font-black leading-snug text-brand-ink transition group-hover:text-brand-navy">
                                        {{ $item->title }}
                                    </h3>

                                    <p class="mt-3 line-clamp-2 min-h-12 text-sm leading-6 text-slate-600">
                                        {{ $itemDescription($item) }}
                                    </p>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>

                @if ($multimediaCollection instanceof \Illuminate\Pagination\AbstractPaginator && $multimediaCollection->hasPages())
                    <div class="mt-10">
                        {{ $multimediaCollection->withQueryString()->links() }}
                    </div>
                @endif
            @else
                <div class="mt-8 rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm shadow-slate-900/5">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-mist text-brand-navy">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M8 5v14l11-7-11-7Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        </svg>
                    </div>

                    <h3 class="mt-5 text-xl font-black text-brand-ink">
                        Belum ada konten multimedia
                    </h3>

                    <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-slate-600">
                        Konten multimedia yang sudah dipublikasikan dari panel admin akan tampil di bagian ini.
                    </p>
                </div>
            @endif
        </div>
    </section>

    <section class="bg-white py-12 lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                        Video Singkat
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-brand-ink sm:text-3xl">
                        Format pendek untuk isu hukum sehari-hari
                    </h2>
                </div>

                <a href="{{ route('multimedia.index', ['type' => 'shorts']) }}" class="text-sm font-black text-brand-navy transition hover:text-brand-ink">
                    Lihat Shorts →
                </a>
            </div>

            @if ($shortItems->isNotEmpty())
                <div class="mt-8 grid grid-flow-col auto-cols-[minmax(220px,260px)] gap-5 overflow-x-auto pb-2 md:grid-flow-row md:grid-cols-4 md:overflow-visible">
                    @foreach ($shortItems as $item)
                        @php
                            $tone = $mediaTone($item->type);
                            $thumbnail = $item->thumbnail_url;
                            $url = $contentUrl($item);
                        @endphp

                        <article class="group overflow-hidden rounded-3xl border border-slate-200 bg-[#f8fafc] shadow-sm transition hover:-translate-y-1 hover:shadow-lg hover:shadow-slate-900/10">
                            <a
                                href="{{ $url }}"
                                @if ($isExternalUrl($url)) target="_blank" rel="noopener noreferrer" @endif
                                class="block"
                            >
                                <div class="relative aspect-[9/14] overflow-hidden bg-linear-to-br {{ $tone['visual'] }}">
                                    @if ($thumbnail)
                                        <img
                                            src="{{ $thumbnail }}"
                                            alt="{{ $item->title }}"
                                            class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                        >
                                        <div class="absolute inset-0 bg-linear-to-t from-brand-navy/75 via-brand-navy/15 to-transparent"></div>
                                    @endif

                                    <span class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-brand-ink">
                                        {{ $item->display_type }}
                                    </span>

                                    <span class="absolute inset-0 m-auto flex h-12 w-12 items-center justify-center rounded-full bg-white/92 text-brand-navy shadow-lg transition group-hover:bg-brand-amber">
                                        <svg class="ml-0.5 h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M8 5v14l11-7L8 5Z"/>
                                        </svg>
                                    </span>

                                    <div class="absolute bottom-0 left-0 right-0 p-4">
                                        <h3 class="line-clamp-2 text-lg font-black leading-snug text-white">
                                            {{ $item->title }}
                                        </h3>
                                    </div>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="mt-8 rounded-3xl border border-slate-200 bg-[#f8fafc] p-7">
                    <div class="grid gap-5 md:grid-cols-[auto_1fr] md:items-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-brand-navy shadow-sm">
                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M8 5v14l11-7-11-7Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            </svg>
                        </div>

                        <div>
                            <h3 class="text-xl font-black text-brand-ink">
                                Video singkat segera hadir
                            </h3>

                            <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-600">
                                Publikasikan konten bertipe Shorts atau Reels dari panel admin untuk mengisi section ini.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <section id="serial-edulaw" class="py-12 lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                    Serial Edulaw
                </p>

                <h2 class="mt-2 text-2xl font-black tracking-tight text-brand-ink sm:text-3xl">
                    Seri konten untuk belajar bertahap
                </h2>

                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Ruang tematik untuk mengembangkan konten multimedia hukum yang konsisten dan mudah diikuti.
                </p>
            </div>

            <div class="mt-8 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($serials as $serial)
                    <article class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-900/5 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/10">
                        <span class="inline-flex rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] {{ $serial['accent'] }}">
                            {{ $serial['label'] }}
                        </span>

                        <h3 class="mt-5 text-2xl font-black text-brand-ink">
                            {{ $serial['title'] }}
                        </h3>

                        <p class="mt-3 min-h-14 text-sm leading-7 text-slate-600">
                            {{ $serial['description'] }}
                        </p>

                        <a
                            href="{{ $serial['href'] }}"
                            class="mt-6 inline-flex items-center gap-2 text-sm font-black text-brand-navy transition group-hover:text-brand-ink"
                        >
                            Lihat Serial
                            <svg class="h-4 w-4 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white py-12 lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
                    Topik Multimedia
                </p>

                <h2 class="mt-2 text-2xl font-black tracking-tight text-brand-ink sm:text-3xl">
                    Jelajahi isu hukum secara visual
                </h2>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($topics as $topic)
                    <article class="group flex items-center gap-4 rounded-3xl border border-slate-200 bg-[#f8fafc] p-5 transition hover:-translate-y-1 hover:bg-white hover:shadow-lg hover:shadow-slate-900/8">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $topic['tone'] }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 5h14v14H5V5Zm4 4h6M9 13h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>

                        <div>
                            <h3 class="text-base font-black text-brand-ink">
                                {{ $topic['title'] }}
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                Kumpulan video dan dokumentasi terkait.
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <x-shared.cta-section
        eyebrow="Kolaborasi Multimedia"
        title="Punya gagasan diskusi atau kolaborasi konten hukum?"
        body="Edulaw membuka ruang kolaborasi untuk menghadirkan literasi hukum yang lebih dekat dengan publik."
        :primary-url="$collaborationUrl"
        primary-label="Ajukan Kolaborasi"
        :secondary-url="$contactUrl"
        secondary-label="Hubungi Edulaw"
        background-image="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1800&q=85"
        background-alt="Kolaborasi konten multimedia hukum"
    />
</main>
@endsection
