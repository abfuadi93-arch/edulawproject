@extends('layouts.app')

@section('title', config('edulaw.site.meta_title') ?: 'Literasi, Riset, dan Edukasi Hukum | Edulaw Project')
@section('meta_description', config('edulaw.site.meta_description') ?: 'Temukan edukasi, riset, publikasi, program, dan analisis hukum yang membantu masyarakat memahami isu hukum secara jernih dan tepercaya.')
@section('canonical_url', route('home'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/home-polish.css') }}?v={{ filemtime(public_path('css/home-polish.css')) }}">
@endpush

@section('content')
    <div class="home-hero-impact">
        <x-home.hero :hero="$homeHero" :values="$homeValues" />
        <x-home.impact :stats="$credibilityStats" />
    </div>
    <div class="home-program-curation">
        <div class="section-shell home-program-curation-grid">
            <x-home.programs :programs="$latestPrograms" />
            <x-home.featured-editorial :insight="$featuredInsight" />
        </div>
    </div>
    <x-home.latest-insights :insight="$featuredInsight" :insights="$latestInsights" />
    <x-home.topics :topics="$homeTopics" />
    <x-home.publications :publications="$latestPublications" />
    <x-home.opportunities :opportunities="$latestOpportunities" />
    <x-home.multimedia :featured="$homepageFeaturedMultimedia" :items="$homepageSecondaryMultimedia" />
    <x-home.about :stats="$credibilityStats" />
    <x-home.cta :block="$sharedCta" />
@endsection

@push('head')
    <x-structured-data :data="\App\Support\StructuredData::website()" />
@endpush
