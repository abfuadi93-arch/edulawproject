@extends('layouts.app')

@section('title', 'Program Edukasi Hukum | Edulaw Project')
@section('meta_description', 'Temukan program edukasi, diskusi, pelatihan, dan kegiatan hukum Edulaw Project untuk memperluas pengetahuan serta partisipasi publik.')

@push('head')
    @php
        $programListSchemaItems = collect([$featuredProgram])
            ->concat($activePrograms)
            ->concat($archivePrograms)
            ->filter()
            ->unique('id')
            ->map(fn ($item): array => [
                'name' => $item->display_title,
                'url' => route('programs.show', $item->slug),
                'image' => $item->hero_image_url,
            ])
            ->values()
            ->all();
    @endphp
    @if ($programListSchemaItems !== [])
        <x-structured-data :data="\App\Support\StructuredData::itemList($programListSchemaItems, 'Program Edukasi Hukum')" />
    @endif
@endpush

@section('content')
@php
    $heroImage = 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1800&q=85';

    $collaborationUrl = \Illuminate\Support\Facades\Route::has('collaboration.index')
        ? route('collaboration.index')
        : url('/kolaborasi');

    $opportunitiesUrl = \Illuminate\Support\Facades\Route::has('opportunities.index')
        ? route('opportunities.index')
        : url('/opportunities');
@endphp

<main class="bg-white text-brand-ink">
    <x-program.hero
        :stats="$stats"
        :background-image="$heroImage"
    />

    <x-program.featured :program="$featuredProgram" />

    <section class="bg-white py-10 sm:py-12 lg:py-14">
        <div class="mx-auto grid max-w-[1320px] gap-6 px-5 sm:px-6 lg:grid-cols-[280px_minmax(0,1fr)] lg:px-8 xl:grid-cols-[280px_minmax(0,1fr)_260px]">
            <x-program.filters
                :status-options="$statusOptions"
                :category-options="$filterCategories"
                :format-options="$formatOptions"
                :level-options="$levelOptions"
                :selected-statuses="$selectedStatuses"
                :selected-categories="$selectedCategories"
                :selected-formats="$selectedFormats"
                :selected-levels="$selectedLevels"
                :search="$activeSearch"
                :selected-sort="$selectedSort"
                :selected-view="$selectedView"
            />

            <x-program.active-grid
                :programs="$activePrograms"
                :total="$activeTotal"
                :search="$activeSearch"
                :selected-sort="$selectedSort"
                :selected-view="$selectedView"
            />

            <x-program.sidebar
                :audiences="$audienceOptions"
                class="lg:col-span-2 xl:col-span-1"
            />
        </div>
    </section>

    <x-program.archive-slider
        :programs="$archivePrograms"
        :categories="$programCategories"
    />

    <x-shared.cta-section
        eyebrow="Kolaborasi Program"
        title="Bangun program literasi hukum bersama Edulaw Project."
        body="Kami membuka ruang kerja sama untuk kelas, diskusi publik, pelatihan, riset, dan pengembangan kapasitas hukum."
        :primary-url="$collaborationUrl"
        primary-label="Ajukan Kerja Sama"
        :secondary-url="$opportunitiesUrl"
        secondary-label="Lihat Opportunities"
    />
</main>
@endsection
