@extends('layouts.app')

@section('title', config('edulaw.site.meta_title') ?: 'Literasi, Riset, dan Edukasi Hukum | Edulaw Project')
@section('meta_description', config('edulaw.site.meta_description') ?: 'Temukan edukasi, riset, publikasi, program, dan analisis hukum yang membantu masyarakat memahami isu hukum secara jernih dan tepercaya.')
@section('canonical_url', route('home'))

@push('styles')
    <style>
        @media (min-width: 1024px) and (max-width: 1279px) {
            body header nav[aria-label="Navigasi utama"],
            body header nav[aria-label="Navigasi utama"] + div {
                display: none !important;
            }

            body header button[aria-controls="mobile-navigation"] {
                display: inline-flex !important;
            }

            body header #mobile-navigation:not([style*="display: none"]) {
                display: block !important;
            }
        }
    </style>
@endpush

@section('content')
    <x-home.hero :hero="$homeHero" :values="$homeValues" />
    <x-home.highlights
        :program="$latestPrograms->first()"
        :opportunity="$latestOpportunities->first()"
        :insight="$featuredInsight"
    />
    <x-home.audience />
    <x-home.programs :programs="$latestPrograms" :featured-insight="$featuredInsight" />
    <x-home.insights :featuredInsight="$featuredInsight" :insights="$latestInsights" />
    <x-home.publications :publications="$latestPublications" />
    <x-home.opportunities :opportunities="$latestOpportunities" />
    <x-home.about :stats="$credibilityStats" />
    <x-home.multimedia :featured="$homepageFeaturedMultimedia" :items="$homepageSecondaryMultimedia" />
    <x-home.cta :block="$sharedCta" />
@endsection

@push('head')
    <x-structured-data :data="\App\Support\StructuredData::website()" />
@endpush
