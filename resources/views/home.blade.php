@extends('layouts.app')

@section('title', config('edulaw.site.meta_title') ?: 'Literasi, Riset, dan Edukasi Hukum | Edulaw Project')
@section('meta_description', config('edulaw.site.meta_description') ?: 'Temukan edukasi, riset, publikasi, program, dan analisis hukum yang membantu masyarakat memahami isu hukum secara jernih dan tepercaya.')
@section('canonical_url', route('home'))

@section('content')
    <x-home.hero :hero="$homeHero" :values="$homeValues" />
    <x-home.audience />
    <x-home.programs :programs="$latestPrograms" />
    <x-home.insights :featuredInsight="$featuredInsight" :insights="$latestInsights" />
    <x-home.publications :publications="$latestPublications" />
    <x-home.opportunities :opportunities="$latestOpportunities" />
    <x-home.multimedia :items="$latestMultimedia" />
    <x-home.about :stats="$credibilityStats" />
    <x-home.cta :block="$sharedCta" />
@endsection

@push('head')
    <x-structured-data :data="\App\Support\StructuredData::website()" />
@endpush
