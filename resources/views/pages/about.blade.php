@extends('layouts.app')

@section('title', 'Tentang Kami dan Tim | Edulaw Project')
@section('meta_description', 'Kenali visi, perjalanan, dan tim Edulaw Project yang mengembangkan literasi, riset, publikasi, serta edukasi hukum untuk kepentingan publik.')

@section('content')
@php
    use Illuminate\Support\Str;

    $paragraphs = fn (?string $body) => collect(preg_split("/\R{2,}/", trim((string) $body)))
        ->map(fn ($paragraph): string => trim((string) $paragraph))
        ->filter()
        ->values();
    $lookupKey = fn (?string $name): string => Str::of((string) $name)
        ->lower()
        ->replaceMatches('/[^a-z0-9]+/i', ' ')
        ->squish()
        ->toString();
    $editorialCopy = fn (?string $value): ?string => $value === null ? null : str_replace(
        ['Insight Editorial', 'Manager Insight Editorial', 'Officer Insight', 'Legal Insight', 'Edulaw Insight'],
        ['Editorial', 'Manager Editorial', 'Officer Editorial', 'Legal Editorial', 'Edulaw Editorial'],
        $value
    );

    $stats = collect($aboutStats ?? [])->map(fn ($block): array => [
        'value' => $block->subtitle,
        'label' => $block->title,
        'icon' => $block->icon ?: 'book',
    ]);
    if ($stats->isEmpty()) {
        $stats = collect([
            ['value' => '21+', 'label' => 'Program & Aktivitas', 'icon' => 'calendar'],
            ['value' => '3.800+', 'label' => 'Peserta', 'icon' => 'users'],
            ['value' => '23', 'label' => 'Kolaborator', 'icon' => 'handshake'],
            ['value' => '10+', 'label' => 'Komunitas & Jejaring', 'icon' => 'network'],
        ]);
    }
    $heroStats = $stats->take(4)->values();

    $vision = 'Menjadi wadah edukasi hukum yang berorientasi pada kesetaraan, kemanusiaan, dan kemajuan guna mewujudkan insan yuris yang siap mengabdi kepada kepentingan bangsa dan negara.';
    $missions = [
        'Menyediakan pendidikan hukum yang kontekstual, inklusif, dan mudah diakses.',
        'Mengembangkan riset hukum yang menjawab persoalan publik secara aktual.',
        'Mendorong kolaborasi lintas sektor untuk memperkuat literasi hukum publik.',
        'Mengadvokasi pengetahuan hukum yang mendukung keadilan dan demokrasi.',
    ];

    $focusAreas = collect($aboutFocusAreas ?? [])->map(fn ($block): array => [
        'title' => $editorialCopy($block->title),
        'description' => $editorialCopy($block->body),
        'icon' => $block->icon ?: 'book',
    ]);
    if ($focusAreas->isEmpty()) {
        $focusAreas = collect([
            ['title' => 'Literasi Hukum', 'description' => 'Pengetahuan hukum yang dapat dipahami dan dimiliki oleh publik.', 'icon' => 'book'],
            ['title' => 'Riset Kebijakan', 'description' => 'Kajian berbasis data dan evidence untuk kebijakan yang lebih baik.', 'icon' => 'chart'],
            ['title' => 'Edukasi', 'description' => 'Pembelajaran kontekstual dan inklusif bagi semua kalangan.', 'icon' => 'pen'],
            ['title' => 'Kolaborasi Publik', 'description' => 'Bekerja bersama berbagai pihak untuk perubahan sosial yang berkelanjutan.', 'icon' => 'users'],
        ]);
    }

    $timeline = collect($aboutTimeline ?? [])->map(fn ($block): array => [
        'year' => $block->eyebrow,
        'title' => $block->title,
        'description' => $editorialCopy($block->body),
    ]);
    if ($timeline->isEmpty()) {
        $timeline = collect([
            ['year' => '2021', 'title' => 'Gagasan Awal', 'description' => 'Forum kecil dibangun sebagai ruang membaca dan berdiskusi bersama.'],
            ['year' => '2022', 'title' => 'Pengembangan Komunitas', 'description' => 'Pembelajaran hukum kolaboratif mulai berkembang dan tertata.'],
            ['year' => '2023', 'title' => 'Edulaw Project Didirikan', 'description' => 'Edulaw resmi hadir sebagai platform edukasi hukum independen.'],
            ['year' => '2024', 'title' => 'Beragam Program', 'description' => 'Program literasi, editorial, dan kolaborasi publik diperluas.'],
            ['year' => '2025', 'title' => 'Transformasi Digital', 'description' => 'Ekosistem publikasi digital dikembangkan untuk memperluas akses.'],
            ['year' => '2026+', 'title' => 'Penguatan Dampak', 'description' => 'Memperkuat riset, jejaring, dan manfaat pengetahuan hukum bagi publik.'],
        ]);
    }

    $profileMap = collect($aboutProfiles ?? []);
    $organizationGroups = collect($aboutOrganizationProfilesByRole ?? $aboutProfilesByRole ?? []);
    $profileInterests = function ($profile): array {
        $interests = $profile->interests;
        if (is_string($interests)) {
            $decoded = json_decode($interests, true);
            $interests = json_last_error() === JSON_ERROR_NONE ? $decoded : preg_split('/,|\r\n|\r|\n/', $interests);
        }

        return collect(is_array($interests) ? $interests : [])
            ->flatten()
            ->map(fn ($interest): string => trim((string) $interest, " \t\n\r\0\x0B."))
            ->filter()
            ->values()
            ->all();
    };
    $profilePerson = function ($profile, ?string $fallbackRole = null) use ($editorialCopy, $profileInterests): array {
        $interests = $profileInterests($profile);
        $jobTitle = $editorialCopy($profile->position);
        $profileRole = $editorialCopy($profile->profile_type_label ?: $fallbackRole);

        return [
            'name' => $profile->name,
            'position' => $jobTitle ?: $profileRole,
            'job_title' => $jobTitle,
            'profile_role' => $profileRole,
            'organization_group' => $profile->organization_group,
            'interest_text' => collect($interests)->join(', '),
            'photo' => $profile->photo_url ?: asset('images/logo/icon-bg.png'),
            'profile' => $profile,
        ];
    };
    $organizationPositions = [
        'azmi fathu rohman' => 'Executive Director',
        'faraz almira arelia' => 'Director of Research',
        'umi zakia azzahro' => 'Director of Operations',
        'm riziq maulana' => 'Editorial Manager',
        'muhamad riziq maulana' => 'Editorial Manager',
        'festy nur fajruroh' => 'Communications Manager',
        'siti zahra' => 'Program & Administration Manager',
        'sizil azzahra sa d illah' => 'Program & Finance Manager',
        'siti mahmuda' => 'Senior Researcher',
        'annisa zahra nur umar' => 'Senior Researcher',
        'naufal rizqiyanto' => 'Senior Researcher',
        'lalu rizqi ramdani alfaen' => 'Senior Researcher',
        'fadila sharfina' => 'Senior Researcher',
        'laila andayani' => 'Senior Researcher',
        'rahmatika monati' => 'Senior Researcher',
        'amirudin nur wahid' => 'Junior Researcher',
        'mely noviyanti' => 'Junior Researcher',
        'putri yuliani' => 'Junior Researcher',
        'fadlah nur' => 'Junior Researcher',
    ];
    $withPosition = fn (array $person, string $fallback): array => array_merge($person, [
        'organization_position' => $organizationPositions[$lookupKey($person['name'] ?? null)]
            ?? ($person['job_title'] ?: $fallback),
    ]);
    $profileFor = fn (array $person) => $person['profile'] ?? $profileMap->get($lookupKey($person['name'] ?? null));

    $leaderDefinitions = [
        ['name' => 'Abdul Basid Fuadi', 'role' => 'Founder'],
        ['name' => 'Azmi Fathu Rohman', 'role' => 'Co-Founder'],
        ['name' => 'Faraz Almira Arelia', 'role' => 'Co-Founder'],
        ['name' => 'Umi Zakia Azzahro', 'role' => 'Co-Founder'],
    ];
    $leaders = collect($leaderDefinitions)->map(function (array $definition) use ($profileMap, $lookupKey, $profilePerson): array {
        $profile = $profileMap->get($lookupKey($definition['name']));
        if ($profile) {
            return array_merge($profilePerson($profile, $definition['role']), [
                'profile_role' => $definition['role'],
                'organization_position' => $definition['role'],
            ]);
        }

        return [
            'name' => $definition['name'],
            'position' => $definition['role'],
            'job_title' => null,
            'profile_role' => $definition['role'],
            'organization_position' => $definition['role'],
            'interest_text' => 'Membangun arah kelembagaan dan ekosistem pengetahuan hukum Edulaw Project.',
            'photo' => asset('images/logo/icon-bg.png'),
            'profile' => null,
        ];
    })->values();

    $roleProfiles = fn (string $role) => collect($organizationGroups->get($role, []));
    $directors = $roleProfiles('director')
        ->reject(fn ($profile): bool => $lookupKey($profile->name) === 'abdul basid fuadi')
        ->map(fn ($profile): array => $withPosition($profilePerson($profile, 'Director'), 'Director'))
        ->values();
    $managers = $roleProfiles('manager')
        ->map(fn ($profile): array => $withPosition($profilePerson($profile, 'Manager'), 'Manager'))
        ->values();
    $excludedKeys = $leaders->pluck('name')
        ->merge($managers->pluck('name'))
        ->map($lookupKey)
        ->all();
    $teamMembers = $roleProfiles('team')
        ->reject(fn ($profile): bool => in_array($lookupKey($profile->name), $excludedKeys, true))
        ->map(function ($profile) use ($profilePerson, $withPosition): array {
            $person = $profilePerson($profile, 'Contributor');

            return $withPosition($person, $person['job_title'] ?: 'Contributor');
        })
        ->values();
    $organizationGroupFor = function (array $person): string {
        $explicitGroup = \App\Models\Author::canonicalOrganizationGroup($person['organization_group'] ?? null);
        if ($explicitGroup) {
            return $explicitGroup;
        }

        $roleText = Str::lower(collect([
            $person['job_title'] ?? null,
            $person['organization_position'] ?? null,
            $person['position'] ?? null,
        ])->filter()->join(' '));

        return match (true) {
            Str::contains($roleText, ['research', 'riset', 'peneliti']) => 'research_team',
            Str::contains($roleText, ['internship', 'intern', 'magang']) => 'internship_member',
            Str::contains($roleText, ['writer', 'penulis']) => 'writer',
            Str::contains($roleText, ['speaker', 'moderator', 'narasumber', 'pembicara']) => 'speaker_moderator',
            default => 'contributor',
        };
    };
    $teamGroupDefinitions = collect([
        ['key' => 'research_team', 'label' => 'Research Team'],
        ['key' => 'internship_member', 'label' => 'Internship Member'],
        ['key' => 'writer', 'label' => 'Writer'],
        ['key' => 'speaker_moderator', 'label' => 'Speaker & Moderator'],
        ['key' => 'contributor', 'label' => 'Contributor'],
    ]);
    $teamGroups = $teamGroupDefinitions
        ->map(fn (array $group): array => array_merge($group, [
            'members' => $teamMembers
                ->filter(fn (array $person): bool => $organizationGroupFor($person) === $group['key'])
                ->values(),
        ]))
        ->filter(fn (array $group): bool => $group['members']->isNotEmpty())
        ->values();

    $heroParagraphs = $paragraphs($aboutHero?->body);
    $whyParagraphs = $paragraphs($aboutWhy?->body);
    $heroDescription = $heroParagraphs->first()
        ?: 'Edulaw Project adalah ruang belajar, diskusi, riset, dan kolaborasi untuk memperkuat literasi hukum publik.';
    $ctaMeta = (array) ($sharedCta?->meta ?? []);
    $ctaEyebrow = $sharedCta?->eyebrow ?? 'Kolaborasi';
    $ctaTitle = $sharedCta?->title ?? 'Bangun ruang literasi hukum bersama Edulaw Project.';
    $ctaBody = $sharedCta?->body ?? 'Kami membuka ruang kerja sama untuk edukasi, riset, publikasi, dan penguatan literasi hukum publik.';
    $ctaPrimaryUrl = $sharedCta?->resolved_url ?? route('collaboration.index');
    $ctaPrimaryLabel = $sharedCta?->url_label ?? 'Ajukan Kerja Sama';
    $ctaSecondaryUrl = \App\Support\EdulawSite::resolveUrl($ctaMeta['secondary_url'] ?? null, route('programs.index'));
    $ctaSecondaryLabel = $ctaMeta['secondary_label'] ?? 'Lihat Program';
@endphp

<div class="overflow-x-clip bg-[#f7f8fa] text-brand-ink">
    <x-shared.primary-hero
        :title="$aboutHero?->title ?? 'Edulaw Project'"
        :eyebrow="$aboutHero?->eyebrow ?? 'Tentang Kami'"
        :description="$heroDescription"
        :background-image="$aboutHero?->image_url ?? 'https://images.unsplash.com/photo-1505664194779-8beaceb93744?auto=format&fit=crop&w=1800&q=85'"
        :background-alt="$aboutHero?->image_alt ?? 'Perpustakaan hukum Edulaw Project'"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Tentang'],
        ]"
        :stats="$heroStats"
        panel-label="Dampak Edulaw Project"
    />

    <section class="bg-white py-6 sm:py-7 lg:py-8" aria-labelledby="why-edulaw-heading">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-2 lg:items-center lg:gap-12">
                <div>
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-brand-coral">{{ $aboutWhy?->eyebrow ?? 'Mengapa Edulaw Hadir?' }}</p>
                    <h2 id="why-edulaw-heading" class="mt-2 font-display text-3xl font-black leading-tight text-brand-navy sm:text-4xl">{{ $aboutWhy?->title ?? 'Mengapa Edulaw Hadir?' }}</h2>
                    <div class="mt-5 space-y-3 text-base leading-7 text-slate-600">
                        @forelse ($whyParagraphs as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @empty
                            <p>Edulaw hadir karena persoalan hukum publik kerap kompleks dan sulit dipahami. Kesenjangan literasi hukum dapat memperlebar ketidakadilan serta melemahkan partisipasi masyarakat.</p>
                            <p>Kami menjembatani kesenjangan tersebut melalui pendidikan, riset berbasis bukti, dan kolaborasi lintas sektor agar pengetahuan hukum berpihak pada kepentingan publik.</p>
                        @endforelse
                    </div>
                    <blockquote class="mt-6 border-l-4 border-brand-amber pl-5 font-display text-xl font-black leading-snug text-brand-navy">“Pengetahuan hukum seharusnya menjadi milik semua, bukan hanya mereka yang berada di ruang kuasa.”</blockquote>
                </div>
                <div class="min-w-0">
                    <div class="rounded-xl bg-[#f7f8fa] p-3">
                        <h3 class="text-[11px] font-extrabold uppercase tracking-[0.12em] text-brand-navy">Founder &amp; Co-Founder</h3>
                        <div class="mt-2.5 grid grid-cols-4 gap-2">
                            @foreach ($leaders as $leader)
                                <x-about.leadership-card :person="$leader" :profile="$profileFor($leader)" :role="$leader['profile_role']" mini />
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-6 sm:py-7 lg:py-8" aria-labelledby="vision-mission-heading">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-brand-coral">Visi &amp; Misi</p>
            <h2 id="vision-mission-heading" class="mt-2 font-display text-3xl font-black text-brand-navy sm:text-4xl">Arah kerja Edulaw Project</h2>
            <div class="mt-5 grid items-stretch gap-4 lg:grid-cols-[9fr_11fr]">
                <article class="relative flex h-full flex-col overflow-hidden rounded-[14px] bg-brand-navy p-5 text-white sm:p-6 lg:p-7">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.18em] text-brand-amber">Visi</p>
                    <img src="{{ asset('images/logo/edulaw-logo-white.png') }}" alt="Edulaw Project" class="absolute right-5 top-5 h-7 w-auto object-contain opacity-90 sm:right-6 sm:top-6 sm:h-8" loading="lazy">
                    <h3 class="mt-12 text-balance font-display text-xl font-black leading-snug text-white sm:text-2xl lg:mt-auto lg:pt-12">{{ $vision }}</h3>
                </article>
                <article class="h-full rounded-[14px] bg-white p-5 sm:p-6">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.18em] text-brand-navy">Misi</p>
                    <div class="mt-3 divide-y divide-slate-200">
                        @foreach ($missions as $mission)
                            <div class="grid grid-cols-[32px_minmax(0,1fr)] gap-3 py-3 first:pt-0 last:pb-0">
                                <span class="grid size-8 place-items-center rounded-full bg-brand-amber-soft text-[11px] font-black text-brand-navy">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <p class="self-center text-base font-semibold leading-7 text-slate-700">{{ $mission }}</p>
                            </div>
                        @endforeach
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="bg-white py-6 sm:py-7 lg:py-8" aria-labelledby="about-pillars-heading">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-brand-coral">{{ $aboutFocusIntro?->eyebrow ?? 'Pilar Edulaw' }}</p>
            <h2 id="about-pillars-heading" class="mt-2 w-full font-display text-3xl font-black leading-tight text-brand-navy sm:text-4xl">Pendidikan, Riset &amp; Kolaborasi untuk Dampak Nyata</h2>
            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($focusAreas->take(4) as $item)
                    <article class="rounded-[14px] border border-slate-200 bg-white p-5">
                        <div class="flex items-center gap-3">
                            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand-amber-soft text-brand-navy">
                                @if ($item['icon'] === 'book')
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z" stroke="currentColor" stroke-width="1.8"/></svg>
                                @elseif ($item['icon'] === 'chart')
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 19V5m0 14h16M8 16v-4m5 4V8m5 8v-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                @elseif ($item['icon'] === 'pen')
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m4 20 4-1 11-11a2.8 2.8 0 0 0-4-4L4 15v5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
                                @else
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M16 11a4 4 0 1 0-8 0M4 21a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="1.8"/></svg>
                                @endif
                            </span>
                            <h3 class="min-w-0 text-lg font-black leading-tight text-brand-navy">{{ $item['title'] }}</h3>
                        </div>
                        <p class="mt-4 text-base leading-7 text-slate-600">{{ $item['description'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-6 sm:py-7 lg:py-8" aria-labelledby="about-timeline-heading">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-brand-coral">{{ $aboutTimelineIntro?->eyebrow ?? 'Perjalanan Edulaw' }}</p>
            <h2 id="about-timeline-heading" class="mt-2 font-display text-3xl font-black text-brand-navy sm:text-4xl">Perjalanan Edulaw</h2>
            <p class="mt-2 text-base leading-7 text-slate-600">{{ $aboutTimelineIntro?->title ?? 'Dari Forum Kecil Menuju Ekosistem Literasi Hukum' }}</p>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($timeline->take(6) as $item)
                    <article class="relative min-w-0 overflow-hidden rounded-[13px] border border-slate-200 bg-white p-5 pl-6">
                        <span class="absolute inset-y-0 left-0 w-1 bg-brand-amber"></span>
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-base font-black text-brand-coral">{{ $item['year'] }}</p>
                            <span class="text-xs font-extrabold tabular-nums text-slate-400">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <h3 class="mt-3 text-base font-black leading-snug text-brand-navy">{{ $item['title'] }}</h3>
                        <p class="mt-2 text-[15px] leading-6 text-slate-600">{{ $item['description'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="tim" class="scroll-mt-20 bg-white py-6 sm:py-7 lg:py-8" aria-labelledby="about-team-heading">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-brand-coral">Penggerak</p>
            <h2 id="about-team-heading" class="mt-2 font-display text-3xl font-black text-brand-navy sm:text-4xl">Penggerak Edulaw Project</h2>
            <p class="mt-2 w-full text-base leading-7 text-slate-600">Orang-orang yang membangun arah institusi, mengembangkan program, serta menjaga kualitas pengetahuan hukum Edulaw.</p>

            @if ($directors->isNotEmpty())
                <div class="mt-10">
                    <div class="flex items-center gap-4"><h3 class="shrink-0 text-xs font-extrabold uppercase tracking-[0.18em] text-brand-navy">Director / Leadership</h3><span class="h-px flex-1 bg-slate-200"></span></div>
                    <div class="mt-5 grid gap-6 md:grid-cols-2">
                        @foreach ($directors as $director)
                            <x-about.leadership-card :person="$director" :profile="$profileFor($director)" role="Director" compact />
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($managers->isNotEmpty())
                <div class="mt-12">
                    <div class="flex items-center gap-4"><h3 class="shrink-0 text-xs font-extrabold uppercase tracking-[0.18em] text-brand-navy">Manager</h3><span class="h-px flex-1 bg-slate-200"></span></div>
                    <div class="mt-5 grid grid-cols-2 gap-5 lg:grid-cols-4">
                        @foreach ($managers as $manager)
                            <x-about.team-card :person="$manager" :profile="$profileFor($manager)" />
                        @endforeach
                    </div>
                </div>
            @endif

            @foreach ($teamGroups as $group)
                <div class="mt-10">
                    <div class="flex items-center gap-4">
                        <h3 class="shrink-0 text-xs font-extrabold uppercase tracking-[0.18em] text-brand-navy">{{ $group['label'] }}</h3>
                        <span class="h-px flex-1 bg-slate-200"></span>
                        <span class="shrink-0 text-xs font-bold text-slate-500">{{ $group['members']->count() }} profil</span>
                    </div>
                    <div class="mt-5 grid grid-cols-2 gap-5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                        @foreach ($group['members'] as $member)
                            <x-about.team-card :person="$member" :profile="$profileFor($member)" />
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <x-shared.cta-section
        heading-id="about-collaboration-heading"
        :eyebrow="$ctaEyebrow"
        :title="$ctaTitle"
        :body="$ctaBody"
        :primary-url="$ctaPrimaryUrl"
        :primary-label="$ctaPrimaryLabel"
        :secondary-url="$ctaSecondaryUrl"
        :secondary-label="$ctaSecondaryLabel"
    />
</div>
@endsection
