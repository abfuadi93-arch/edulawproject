@extends('layouts.app')

@section('title', 'Beranda - Edulaw Project')

@section('content')
    <x-home.hero :hero="$homeHero" :values="$homeValues" />
    <x-home.audience :intro="$homeAudienceIntro" :audiences="$homeAudiences" />
    <x-home.insights :featuredInsight="$featuredInsight" :insights="$latestInsights" />
    <x-home.publications :publications="$latestPublications" />
    <x-home.programs :programs="$latestPrograms" />
    <x-home.multimedia-opportunities
        :featuredMultimedia="$featuredMultimedia"
        :multimediaItems="$latestMultimedia"
        :opportunities="$latestOpportunities"
    />
    <x-home.cta :block="$sharedCta" />
@endsection
