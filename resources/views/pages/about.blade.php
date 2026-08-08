@extends('layouts.app')

@section('title', 'Tentang Kami dan Tim | Edulaw Project')
@section('meta_description', 'Kenali visi, perjalanan, dan tim Edulaw Project yang mengembangkan literasi, riset, publikasi, serta edukasi hukum untuk kepentingan publik.')

@section('content')
@php
    use Illuminate\Support\Str;

    $stats = [
        ['value' => '21+', 'label' => 'Program', 'icon' => 'calendar'],
        ['value' => '300+', 'label' => 'Publikasi', 'icon' => 'book'],
        ['value' => '3.800+', 'label' => 'Peserta', 'icon' => 'users'],
        ['value' => '23', 'label' => 'Kolaborasi', 'icon' => 'handshake'],
        ['value' => '10+', 'label' => 'Diskusi Publik', 'icon' => 'chat'],
        ['value' => '1.200+', 'label' => 'Konten Edukasi', 'icon' => 'play'],
    ];

    $aboutVision = 'Menjadi wadah edukasi hukum yang berorientasi pada kesetaraan, kemanusiaan, dan kemajuan guna mewujudkan insan yuris yang siap mengabdi kepada kepentingan bangsa dan negara.';
    $aboutMissions = [
        'Menyediakan pendidikan hukum yang berkualitas dan setara bagi semua lapisan masyarakat.',
        'Mengembangkan keilmuan hukum yang berorientasi pada nilai-nilai kemanusiaan.',
        'Mendorong inovasi dan kreativitas di bidang hukum untuk menjawab tantangan kemajuan.',
        'Memperluas jaringan keilmuan melalui program kolaboratif.',
    ];

    $leaders = [];
    $directors = [];
    $managers = [];
    $teamMembers = [];
    $researchMembers = [];
    $otherTeamMembers = [];

    $focusAreas = [
        ['title' => 'Literasi Hukum', 'description' => 'Materi belajar yang ringkas, kontekstual, dan mudah digunakan.', 'icon' => 'book'],
        ['title' => 'Riset Kebijakan', 'description' => 'Kajian berbasis regulasi, putusan, data, dan kebutuhan publik.', 'icon' => 'chart'],
        ['title' => 'Editorial', 'description' => 'Esai dan analisis hukum dengan gaya akademik yang tetap terbaca.', 'icon' => 'pen'],
        ['title' => 'Kolaborasi Publik', 'description' => 'Ruang kerja bersama untuk diskusi, advokasi, dan penguatan komunitas.', 'icon' => 'users'],
    ];

    $timeline = [
        ['year' => '2021', 'title' => 'Gagasan Awal', 'description' => 'Forum virtual dan ruang diskusi kecil mulai dijalankan sebagai ruang membaca dan berdiskusi.'],
        ['year' => '2022', 'title' => 'Pengembangan Komunitas', 'description' => 'Penguatan forum dan pengembangan pembelajaran hukum kolaboratif mulai tertata.'],
        ['year' => '2023', 'title' => 'Edulaw Project Didirikan', 'description' => 'Pada 23 Juni 2023, Edulaw Project resmi hadir sebagai platform edukasi hukum independen.'],
        ['year' => '2024', 'title' => 'Ekspansi Program', 'description' => 'Diskusi Literasi Konstitusi, editorial, dan kolaborasi publik mulai berkembang.'],
        ['year' => '2025', 'title' => 'Transformasi Digital', 'description' => 'Pengembangan website dan ekosistem publikasi digital dilakukan untuk memperluas akses pengetahuan hukum.'],
    ];

    $timelineMeta = [
        ['title' => 'Didirikan', 'subtitle' => '23 Juni 2023', 'icon' => 'calendar'],
        ['title' => 'Karakter', 'subtitle' => 'Independen, edukatif, dan kolaboratif.', 'icon' => 'users'],
        ['title' => 'Fokus', 'subtitle' => 'Literasi hukum dan kebijakan publik', 'icon' => 'focus'],
    ];

    $paragraphs = fn (?string $body) => collect(preg_split("/\R{2,}/", trim((string) $body)))
        ->map(fn ($paragraph) => trim($paragraph))
        ->filter()
        ->values();
    $editorialCopy = fn (?string $value): ?string => $value === null ? null : str_replace(
        ['Insight Editorial', 'Manager Insight Editorial', 'Officer Insight', 'Legal Insight', 'Edulaw Insight', 'Insight', 'insight'],
        ['Editorial', 'Manager Editorial', 'Officer Editorial', 'Legal Editorial', 'Edulaw Editorial', 'Editorial', 'editorial'],
        $value
    );

    $statsBlocks = collect($aboutStats ?? []);
    $profileMap = collect($aboutProfiles ?? []);
    $organizationProfileGroups = collect($aboutOrganizationProfilesByRole ?? $aboutProfilesByRole ?? []);
    $focusBlocks = collect($aboutFocusAreas ?? []);
    $timelineBlocks = collect($aboutTimeline ?? []);
    $timelineMetaBlocks = collect($aboutTimelineMeta ?? []);
    $profileLookupKey = fn (?string $name): string => Str::of((string) $name)
        ->lower()
        ->replaceMatches('/[^a-z0-9]+/i', ' ')
        ->squish()
        ->toString();
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
    $profileFor = fn (array $person) => $person['profile'] ?? $profileMap->get($profileLookupKey($person['name'] ?? null));
    $organizationPositionFor = fn (array $person, ?string $fallback = null): string => $organizationPositions[$profileLookupKey($person['name'] ?? null)]
        ?? ($person['job_title']
            ?: ($fallback
                ?? $person['profile_role']
                ?? '-'));
    $withOrganizationPosition = fn (array $person, ?string $fallback = null): array => array_merge($person, [
        'organization_position' => $organizationPositionFor($person, $fallback),
    ]);
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
            'interests' => $interests,
            'interest_text' => collect($interests)->join(', '),
            'photo' => $profile->photo_url ?: asset('images/logo/icon-bg.png'),
            'profile' => $profile,
        ];
    };

    if ($statsBlocks->isNotEmpty()) {
        $stats = $statsBlocks->map(fn ($block) => [
            'value' => $block->subtitle,
            'label' => $block->title,
            'icon' => $block->icon ?: 'book',
        ])->all();
    }

    $organizationRoleProfiles = fn (string $role) => collect($organizationProfileGroups->get($role, []));
    $leaderDefinitions = [
        ['name' => 'Abdul Basid Fuadi', 'role' => 'Founder'],
        ['name' => 'Azmi Fathu Rohman', 'role' => 'Co-Founder'],
        ['name' => 'Faraz Almira Arelia', 'role' => 'Co-Founder'],
        ['name' => 'Umi Zakia Azzahro', 'role' => 'Co-Founder'],
    ];
    $leaders = collect($leaderDefinitions)
        ->map(function (array $definition) use ($profileMap, $profileLookupKey, $profilePerson): array {
            $profile = $profileMap->get($profileLookupKey($definition['name']));

            if ($profile) {
                return array_merge($profilePerson($profile, $definition['role']), [
                    'profile_role' => $definition['role'],
                ]);
            }

            return [
                'name' => $definition['name'],
                'position' => $definition['role'],
                'job_title' => null,
                'profile_role' => $definition['role'],
                'interests' => [],
                'interest_text' => '',
                'photo' => asset('images/logo/icon-bg.png'),
                'profile' => null,
            ];
        })
        ->values()
        ->all();
    $directors = $organizationRoleProfiles('director')
        ->reject(fn ($profile): bool => $profileLookupKey($profile->name) === 'abdul basid fuadi')
        ->map(fn ($profile): array => $profilePerson($profile, 'Director'))
        ->map(fn (array $person): array => $withOrganizationPosition($person, 'Director'))
        ->values()
        ->all();
    $leaderKeys = collect($leaders)
        ->map(fn ($person): string => $profileLookupKey($person['name'] ?? null))
        ->filter()
        ->all();
    $managers = $organizationRoleProfiles('manager')
        ->map(fn ($profile): array => $profilePerson($profile, 'Manager'))
        ->map(fn (array $person): array => $withOrganizationPosition($person, $person['job_title'] ?: 'Manager'))
        ->values()
        ->all();
    $managerKeys = collect($managers)
        ->map(fn ($person): string => $profileLookupKey($person['name'] ?? null))
        ->filter()
        ->all();
    $teamMembers = $organizationRoleProfiles('team')
        ->reject(fn ($profile): bool => in_array($profileLookupKey($profile->name), array_merge($leaderKeys, $managerKeys), true))
        ->map(fn ($profile): array => $profilePerson($profile, 'Contributor'))
        ->map(fn (array $person): array => $withOrganizationPosition($person, $person['job_title'] ?: 'Contributor'))
        ->values()
        ->all();
    $contributorSearchText = fn (array $person): string => Str::lower(
        collect([
            $person['name'] ?? null,
            $person['job_title'] ?? null,
            $person['organization_position'] ?? null,
            $person['interest_text'] ?? null,
        ])->filter()->join(' ')
    );
    $matchesContributorRole = fn (array $person, array $keywords): bool => Str::contains(
        $contributorSearchText($person),
        $keywords
    );
    $contributorGroupFor = function (array $person) use ($matchesContributorRole): string {
        $explicitGroup = $person['organization_group'] ?? null;

        if (in_array($explicitGroup, ['research_team', 'internship_member', 'writer', 'speaker_moderator'], true)) {
            return $explicitGroup;
        }

        if ($matchesContributorRole($person, ['research', 'riset', 'peneliti'])) {
            return 'research_team';
        }

        if ($matchesContributorRole($person, ['internship', 'intern', 'magang'])) {
            return 'internship_member';
        }

        if ($matchesContributorRole($person, ['writer', 'penulis'])) {
            return 'writer';
        }

        if ($matchesContributorRole($person, ['speaker', 'moderator', 'narasumber', 'pembicara'])) {
            return 'speaker_moderator';
        }

        return 'internship_member';
    };
    $isResearchMember = fn (array $person): bool => $contributorGroupFor($person) === 'research_team';
    $isInternshipMember = fn (array $person): bool => $contributorGroupFor($person) === 'internship_member';
    $isWriterMember = fn (array $person): bool => $contributorGroupFor($person) === 'writer';
    $isSpeakerOrModeratorMember = fn (array $person): bool => $contributorGroupFor($person) === 'speaker_moderator';
    $researchPositionRank = function (array $person): int {
        return match (Str::lower((string) ($person['organization_position'] ?? ''))) {
            'senior researcher' => 1,
            'junior researcher' => 2,
            default => 3,
        };
    };
    $researchMembers = collect($teamMembers)
        ->filter($isResearchMember)
        ->sortBy(fn (array $person): string => sprintf(
            '%02d-%s',
            $researchPositionRank($person),
            Str::lower($person['name'] ?? '')
        ))
        ->values()
        ->all();
    $otherTeamMembers = collect($teamMembers)
        ->reject($isResearchMember)
        ->values()
        ->all();
    $otherContributorMembers = collect($otherTeamMembers);
    $internshipMembers = $otherContributorMembers
        ->filter($isInternshipMember)
        ->values();
    $writerMembers = $otherContributorMembers
        ->reject($isInternshipMember)
        ->filter($isWriterMember)
        ->values();
    $speakerAndModeratorMembers = $otherContributorMembers
        ->reject($isInternshipMember)
        ->reject($isWriterMember)
        ->filter($isSpeakerOrModeratorMember)
        ->values();
    $classifiedContributorKeys = $internshipMembers
        ->concat($writerMembers)
        ->concat($speakerAndModeratorMembers)
        ->map(fn (array $person): string => $profileLookupKey($person['name'] ?? null))
        ->all();
    $internshipMembers = $internshipMembers
        ->concat($otherContributorMembers->reject(
            fn (array $person): bool => in_array($profileLookupKey($person['name'] ?? null), $classifiedContributorKeys, true)
        ))
        ->values()
        ->all();
    $contributorGroups = collect([
        ['id' => 'research-team', 'label' => 'Research Team', 'members' => $researchMembers],
        ['id' => 'internship-member', 'label' => 'Internship Member', 'members' => $internshipMembers],
        ['id' => 'writer', 'label' => 'Writer', 'members' => $writerMembers->all()],
        ['id' => 'speaker-moderator', 'label' => 'Speaker and Moderator', 'members' => $speakerAndModeratorMembers->all()],
    ])->filter(fn (array $group): bool => count($group['members']) > 0)->values()->all();
    $organizationTiers = [
        ['id' => 'director', 'label' => 'Director', 'groups' => [
            ['id' => 'director', 'label' => 'Director', 'members' => $directors],
        ]],
        ['id' => 'manager', 'label' => 'Manager', 'groups' => [
            ['id' => 'manager', 'label' => 'Manager', 'members' => $managers],
        ]],
        ['id' => 'contributor', 'label' => 'Contributor', 'groups' => $contributorGroups],
    ];
    $visibleOrganizationTiers = collect($organizationTiers)
        ->filter(fn (array $tier): bool => collect($tier['groups'])->contains(
            fn (array $group): bool => count($group['members']) > 0
        ))
        ->values();
    $orgHasProfiles = $visibleOrganizationTiers->isNotEmpty();

    if ($focusBlocks->isNotEmpty()) {
        $focusAreas = $focusBlocks->map(fn ($block) => [
            'title' => $editorialCopy($block->title),
            'description' => $editorialCopy($block->body),
            'icon' => $block->icon ?: 'book',
        ])->all();
    }

    if ($timelineBlocks->isNotEmpty()) {
        $timeline = $timelineBlocks->map(fn ($block) => [
            'year' => $block->eyebrow,
            'title' => $block->title,
            'description' => $editorialCopy($block->body),
        ])->all();
    }

    if ($timelineMetaBlocks->isNotEmpty()) {
        $timelineMeta = $timelineMetaBlocks->map(fn ($block) => [
            'title' => $block->title,
            'subtitle' => $block->subtitle,
            'icon' => $block->icon ?: 'focus',
        ])->all();
    }

    $aboutHeroParagraphs = $paragraphs($aboutHero?->body);
    $aboutWhyParagraphs = $paragraphs($aboutWhy?->body);
@endphp

<main class="bg-white text-slate-950">
    {{-- Hero --}}
    <section class="relative overflow-hidden border-b border-slate-200 bg-white">
        <div class="absolute inset-y-0 left-0 hidden w-[32%] overflow-hidden lg:block">
            <img
                src="{{ $aboutHero?->image_url ?? 'https://images.unsplash.com/photo-1505664194779-8beaceb93744?auto=format&fit=crop&w=900&q=85' }}"
                alt="{{ $aboutHero?->image_alt ?? 'Perpustakaan hukum Edulaw Project' }}"
                class="h-full w-full object-cover"
            >
            <div class="absolute inset-0 bg-linear-to-r from-white/20 via-white/80 to-white"></div>
        </div>

        <div class="relative mx-auto grid max-w-7xl gap-8 px-4 py-8 sm:px-6 lg:grid-cols-[0.95fr_1.05fr] lg:px-8 lg:py-10">
            <div class="lg:pl-24">
                <p class="edulaw-badge edulaw-badge-md edulaw-badge-amber">
                    {{ $aboutHero?->eyebrow ?? 'Tentang Kami' }}
                </p>

                <h1 class="mt-2 text-4xl font-black leading-tight tracking-tight text-slate-950 sm:text-5xl lg:text-[3.6rem]">
                    {{ $aboutHero?->title ?? 'Edulaw Project' }}
                </h1>

                <div class="mt-4 max-w-xl space-y-4 text-sm leading-7 text-slate-700">
                    @forelse ($aboutHeroParagraphs as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @empty
                        <p>
                            Edulaw Project adalah platform literasi hukum di ruang digital yang berfokus pada penyajian edukasi hukum, riset kebijakan, publikasi, program pengembangan kapasitas, dan kanal pengembangan hukum yang aplikatif.
                        </p>

                        <p>
                            Melalui pendekatan kolaboratif dan berbasis data, kami membangun ekosistem pengetahuan hukum yang inklusif, kritis, dan berdampak.
                        </p>
                    @endforelse
                </div>

                <div class="mt-5 space-y-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-amber-soft text-brand-navy">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21s-7-4.35-9.33-9.1C.8 8.05 3.42 4 7.5 4A5.3 5.3 0 0 1 12 6.35 5.3 5.3 0 0 1 16.5 4c4.08 0 6.7 4.05 4.83 7.9C19 16.65 12 21 12 21Z"/></svg>
                        </span>
                        <p class="text-sm font-semibold text-slate-800">
                            Nilai inti: <span class="font-medium italic text-slate-600">Equal, Educative, Embrace.</span>
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-amber-soft text-brand-navy">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><path d="M8 3h8l3 3v15H5V3h3Z" stroke="currentColor" stroke-width="2"/><path d="M9 13h6M9 17h4M15 3v4h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </span>
                        <p class="text-sm font-semibold text-slate-800">
                            Berbasis Bukti: <span class="font-medium text-slate-600">rujukan, data, dan integritas.</span>
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-amber-soft text-brand-navy">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><path d="M12 21a9 9 0 1 0-9-9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 7v5l3 2M3 21l4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </span>
                        <p class="text-sm font-semibold text-slate-800">
                            Orientasi solusi: <span class="font-medium text-slate-600">rekomendasi yang dapat dieksekusi.</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-xl shadow-slate-900/5">
                <h2 class="text-center text-lg font-black text-slate-950">
                    Edulaw dalam Angka
                </h2>

                <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-3">
                    @foreach ($stats as $stat)
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-amber-soft text-brand-navy">
                                    @if ($stat['icon'] === 'calendar')
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><path d="M7 3v4M17 3v4M4 9h16M5 5h14v16H5V5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                    @elseif ($stat['icon'] === 'book')
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z" stroke="currentColor" stroke-width="2"/></svg>
                                    @elseif ($stat['icon'] === 'users')
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><path d="M16 11a4 4 0 1 0-8 0M4 21a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                    @elseif ($stat['icon'] === 'handshake')
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><path d="M8 12l3 3a2 2 0 0 0 3 0l4-4M3 12l5-5 4 4M21 12l-5-5-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                    @elseif ($stat['icon'] === 'chat')
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><path d="M21 12a8 8 0 0 1-8 8H7l-4 3 1.5-5A8 8 0 1 1 21 12Z" stroke="currentColor" stroke-width="2"/></svg>
                                    @else
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><path d="M8 5v14l11-7-11-7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
                                    @endif
                                </span>

                                <div>
                                    <p class="text-xs font-bold text-slate-600">
                                        {{ $stat['label'] }}
                                    </p>
                                    <p class="text-2xl font-black text-slate-950">
                                        {{ $stat['value'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if (count($leaders) > 0)
                    <div class="mt-5">
                        <h3 class="text-center text-base font-black text-slate-950">
                            Penggerak Edulaw Project
                        </h3>

                        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                            @foreach ($leaders as $leader)
                                @php
                                    $profile = $profileFor($leader);
                                    $leaderCardClass = 'group block min-w-0 rounded-2xl p-1.5 text-center transition hover:-translate-y-0.5 hover:bg-slate-50';
                                @endphp

                                @if ($profile)
                                    <a href="{{ route('profiles.show', $profile->slug) }}" class="{{ $leaderCardClass }}" aria-label="Lihat profil {{ $leader['name'] }}">
                                @else
                                    <div class="{{ $leaderCardClass }}">
                                @endif
                                    <img
                                        src="{{ $leader['photo'] }}"
                                        alt="{{ $leader['name'] }}"
                                        class="aspect-[4/3] w-full rounded-xl bg-brand-mist object-cover object-top shadow-sm"
                                    >
                                    <h4 class="mt-2 line-clamp-2 text-xs font-black leading-tight text-slate-950 underline-offset-4 group-hover:text-brand-navy group-hover:underline">
                                        {{ $leader['name'] }}
                                    </h4>
                                    <p class="mt-1 line-clamp-1 text-[10px] font-bold leading-4 text-slate-600">
                                        {{ $leader['profile_role'] ?: $leader['position'] }}
                                    </p>
                                    <p class="mt-1 line-clamp-2 text-[10px] font-medium leading-4 text-slate-500">
                                        Minat: {{ $leader['interest_text'] ?: '-' }}
                                    </p>
                                @if ($profile)
                                    </a>
                                @else
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </section>

    {{-- Vision & Mission --}}
    <section class="border-b border-slate-200 bg-white py-8 lg:py-9">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div>
                <div>
                    <p class="edulaw-badge edulaw-badge-md edulaw-badge-amber">
                        Vision &amp; Mission
                    </p>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950 lg:text-3xl">
                        Arah kerja Edulaw Project.
                    </h2>
                </div>
            </div>

            <div class="mt-5 grid gap-4 lg:grid-cols-[0.95fr_1.05fr]">
                <article class="relative overflow-hidden rounded-2xl bg-brand-navy p-5 text-white shadow-xl shadow-slate-900/10 lg:p-6">
                    <div class="absolute inset-x-0 top-0 h-1 bg-brand-amber"></div>

                    <div class="relative">
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-brand-amber">
                            Vision
                        </p>
                        <h3 class="mt-3 max-w-2xl text-xl font-black leading-snug tracking-tight text-white sm:text-2xl">
                            {{ $aboutVision }}
                        </h3>
                    </div>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-[#f7f8fa] p-5 shadow-sm lg:p-6">
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-brand-navy">
                        Mission
                    </p>

                    <div class="mt-3 divide-y divide-slate-200">
                        @foreach ($aboutMissions as $mission)
                            <div class="grid grid-cols-[2rem_1fr] gap-3 py-2.5 first:pt-0 last:pb-0">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-amber-soft text-xs font-black text-brand-navy">
                                    {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                <p class="self-center text-sm font-semibold leading-6 text-slate-700">
                                    {{ $mission }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </article>
            </div>
        </div>
    </section>

    {{-- Team --}}
    @if ($orgHasProfiles)
    <section id="tim" class="scroll-mt-20 border-b border-slate-200 bg-white py-12 lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-[#e57b66]">
                        People
                    </p>
                    <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-[#1f3c69]">
                        Penggerak Edulaw Project
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        Tim, peneliti, dan kontributor yang mengembangkan program serta pengetahuan hukum Edulaw.
                    </p>
                </div>
            </div>

            <div class="mt-10 space-y-10">
                @foreach ($visibleOrganizationTiers as $tier)
                    <div @class(['border-t border-slate-200 pt-8' => ! $loop->first])>
                        <div class="flex items-center gap-4">
                            <h3 id="tingkat-{{ $tier['id'] }}" class="shrink-0 text-xs font-extrabold uppercase tracking-[0.2em] text-[#1f3c69]">
                                {{ $tier['label'] }}
                            </h3>
                            <span class="h-px flex-1 bg-slate-200" aria-hidden="true"></span>
                        </div>

                        @foreach ($tier['groups'] as $group)
                            @if (count($group['members']) > 0)
                                <div @class(['mt-5' => $loop->first, 'mt-8' => ! $loop->first])>
                                    @if ($tier['id'] === 'contributor')
                                        <h4 id="substruktur-{{ $group['id'] }}" class="text-[11px] font-extrabold uppercase tracking-[0.18em] text-[#e57b66]">
                                            {{ $group['label'] }}
                                        </h4>
                                    @endif

                                    <div
                                        @class([
                                            'mt-3 grid gap-4',
                                            'grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 xl:gap-3' => $tier['id'] === 'contributor',
                                            'grid-cols-2 md:grid-cols-3 lg:grid-cols-4' => $tier['id'] === 'manager',
                                            'grid-cols-1 md:grid-cols-2' => $tier['id'] === 'director',
                                        ])
                                        aria-labelledby="{{ $tier['id'] === 'contributor' ? 'substruktur-'.$group['id'] : 'tingkat-'.$tier['id'] }}"
                                    >
                                        @foreach ($group['members'] as $member)
                                            @php($profile = $profileFor($member))

                                            <article @class([
                                                'group flex h-full min-w-0 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_14px_34px_-28px_rgba(15,23,42,.7)] transition duration-200 hover:-translate-y-0.5 hover:border-[#1f3c69]/25 hover:shadow-[0_20px_40px_-26px_rgba(15,23,42,.65)]',
                                                'flex-row' => $tier['id'] === 'director',
                                                'flex-col' => $tier['id'] !== 'director',
                                            ])>
                                                <div @class([
                                                    'relative aspect-square shrink-0 overflow-hidden bg-[linear-gradient(135deg,#dce8f3,#eef3f6_60%,#dceeea)]',
                                                    'w-1/2' => $tier['id'] === 'director',
                                                    'w-full' => $tier['id'] !== 'director',
                                                ])>
                                                    <img
                                                        src="{{ $member['photo'] }}"
                                                        alt="{{ $member['name'] }}"
                                                        class="absolute inset-0 size-full object-cover object-top transition duration-300 group-hover:scale-[1.02]"
                                                    >
                                                    @if ($tier['id'] !== 'director')
                                                        <span @class([
                                                            'absolute rounded bg-[#1f3c69]/90 font-extrabold uppercase tracking-wider text-white',
                                                            'bottom-2 left-2 px-2 py-1 text-[7px]' => $tier['id'] === 'contributor',
                                                            'bottom-3 left-3 px-2.5 py-1 text-[8px]' => $tier['id'] !== 'contributor',
                                                        ])>
                                                            {{ $tier['id'] === 'contributor' ? $group['label'] : $tier['label'] }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <div @class([
                                                    'flex min-w-0 flex-1 flex-col',
                                                    'justify-center p-5' => $tier['id'] === 'director',
                                                    'p-3' => $tier['id'] === 'contributor',
                                                    'p-4' => $tier['id'] === 'manager',
                                                ])>
                                                    @if ($tier['id'] === 'director')
                                                        <span class="mb-2 w-fit rounded bg-[#1f3c69]/90 px-2.5 py-1 text-[8px] font-extrabold uppercase tracking-wider text-white">
                                                            Director
                                                        </span>
                                                    @endif
                                                    <h5 @class(['font-extrabold leading-snug text-[#1f3c69]', 'text-xs' => $tier['id'] === 'contributor', 'text-sm' => $tier['id'] !== 'contributor'])>
                                                        {{ $member['name'] }}
                                                    </h5>
                                                    <p class="mt-1 text-[10px] leading-4 text-slate-500">
                                                        {{ $member['organization_position'] }}
                                                    </p>

                                                    @if ($profile)
                                                        <a href="{{ route('profiles.show', $profile->slug) }}" class="mt-auto inline-flex pt-3 text-[10px] font-extrabold text-[#1f3c69] underline-offset-4 hover:underline" aria-label="Lihat profil {{ $member['name'] }}">
                                                            Lihat Profil <span class="ml-1" aria-hidden="true">→</span>
                                                        </a>
                                                    @endif
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Why --}}
    <section class="border-b border-slate-200 bg-white py-8">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[0.55fr_1.45fr] lg:px-8">
            <div class="border-r border-slate-200 pr-8">
                <p class="edulaw-badge edulaw-badge-md edulaw-badge-amber">
                    {{ $aboutWhy?->eyebrow ?? 'Mengapa' }}
                </p>

                <h2 class="mt-2 text-3xl font-black leading-tight tracking-tight text-slate-950">
                    {{ $aboutWhy?->title ?? 'Mengapa Edulaw Hadir?' }}
                </h2>

                <div class="mt-5 flex justify-center text-indigo-200">
                    <svg class="h-16 w-16" viewBox="0 0 64 64" fill="none">
                        <path d="M32 8v48M16 18h32M20 18 12 38h16L20 18Zm24 0-8 20h16l-8-20Z" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>

            <div class="edulaw-readable self-center text-base text-slate-700">
                @forelse ($aboutWhyParagraphs as $paragraph)
                    <p>{{ $paragraph }}</p>
                @empty
                    <p>
                        Hukum sering hadir dalam bahasa yang teknis, tertutup, dan sulit dijangkau publik. Padahal,
                        kualitas demokrasi dan kewargaan sangat bergantung pada kemampuan masyarakat memahami hak,
                        kewajiban, serta arah kebijakan negara.
                    </p>

                    <p>
                        Edulaw Project hadir untuk menjembatani pengetahuan hukum, riset kebijakan, dan kebutuhan
                        masyarakat atas informasi yang jernih, reflektif, serta dapat digunakan dalam pembelajaran,
                        diskusi publik, dan advokasi berbasis pengetahuan.
                    </p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Focus --}}
    <section class="border-b border-slate-200 bg-white py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="edulaw-badge edulaw-badge-md edulaw-badge-amber">
                {{ $aboutFocusIntro?->eyebrow ?? 'Fokus Kerja' }}
            </p>

            <h2 class="mt-2 text-3xl font-black leading-tight tracking-tight text-slate-950">
                {{ $aboutFocusIntro?->title ?? 'Dari Literasi Hukum Menuju Pengetahuan Publik' }}
            </h2>

            <div class="mt-5 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                @foreach ($focusAreas as $item)
                    <article class="flex gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-brand-mist text-brand-navy">
                            @if ($item['icon'] === 'book')
                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z" stroke="currentColor" stroke-width="2"/></svg>
                            @elseif ($item['icon'] === 'chart')
                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none"><path d="M4 19V5m0 14h16M8 16v-4m5 4V8m5 8v-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            @elseif ($item['icon'] === 'pen')
                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none"><path d="m4 20 4-1 11-11a2.8 2.8 0 0 0-4-4L4 15v5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
                            @else
                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none"><path d="M16 11a4 4 0 1 0-8 0M4 21a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="2"/></svg>
                            @endif
                        </span>

                        <div>
                            <h3 class="text-sm font-black text-slate-950">
                                {{ $item['title'] }}
                            </h3>
                            <p class="mt-1 text-xs leading-5 text-slate-600">
                                {{ $item['description'] }}
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Timeline --}}
    <section class="bg-white py-8">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[1.3fr_0.7fr] lg:px-8">
            <div>
                <p class="edulaw-badge edulaw-badge-md edulaw-badge-amber">
                    {{ $aboutTimelineIntro?->eyebrow ?? 'Perjalanan Edulaw' }}
                </p>

                <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                    {{ $aboutTimelineIntro?->title ?? 'Dari forum kecil menuju ekosistem literasi hukum.' }}
                </h2>

                <div class="mt-5 space-y-3">
                    @foreach ($timeline as $item)
                        <div class="grid grid-cols-[80px_1fr] gap-4">
                            <div class="relative flex items-start gap-3">
                                <span class="mt-1 h-3 w-3 rounded-full bg-brand-sky"></span>
                                <span class="text-sm font-black text-brand-navy">
                                    {{ $item['year'] }}
                                </span>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-white px-5 py-3 shadow-sm">
                                <h3 class="text-sm font-black text-slate-950">
                                    {{ $item['title'] }}
                                </h3>
                                <p class="mt-1 text-xs leading-5 text-slate-600">
                                    {{ $item['description'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <aside class="self-end rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="space-y-6">
                    @foreach ($timelineMeta as $meta)
                        <div class="flex gap-4">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-sky-soft text-brand-navy">
                                @if ($meta['icon'] === 'calendar')
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none"><path d="M7 3v4M17 3v4M4 9h16M5 5h14v16H5V5Z" stroke="currentColor" stroke-width="2"/></svg>
                                @elseif ($meta['icon'] === 'users')
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none"><path d="M16 11a4 4 0 1 0-8 0M4 21a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="2"/></svg>
                                @else
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none"><path d="M12 3v18M3 12h18M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                @endif
                            </span>
                            <div>
                                <p class="text-xs text-slate-500">{{ $meta['title'] }}</p>
                                <p class="text-sm font-black text-slate-950">{{ $meta['subtitle'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </aside>
        </div>
    </section>

    {{-- CTA --}}
    <x-shared.cta-collaboration :block="$sharedCta" />
</main>
@endsection
