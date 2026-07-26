@extends('layouts.app')

@section('title', config('edulaw.site.meta_title') ?: 'Edulaw Project')
@section('meta_description', config('edulaw.site.meta_description') ?: 'Edukasi, riset, dan literasi hukum untuk kepentingan publik.')
@section('canonical_url', route('home'))
@section('og_url', route('home'))
@section('twitter_url', route('home'))

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
    @php
        $organizationSameAs = collect([
            \App\Support\EdulawSite::resolveUrl(config('edulaw.social.instagram_url')),
            \App\Support\EdulawSite::resolveUrl(config('edulaw.social.linkedin_url')),
        ])->filter()->values()->all();

        $organizationSchema = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('edulaw.site.name'),
            'url' => route('home'),
            'logo' => \App\Support\EdulawSite::assetUrl(config('edulaw.site.logo')),
            'sameAs' => $organizationSameAs ?: null,
        ]);
    @endphp
    <script type="application/ld+json">{!! json_encode($organizationSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
