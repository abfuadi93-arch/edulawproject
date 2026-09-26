@props([
    'stats' => [],
    'backgroundImage' => asset('images/hero/channels/programs-1600.webp'),
])

@php
    $homeUrl = \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/');
@endphp

<x-shared.primary-hero
    title="Program Edulaw"
    eyebrow="Kanal Program"
    description="Program Edulaw Project dirancang sebagai ruang belajar, diskusi, riset, dan kolaborasi untuk memperkuat literasi hukum publik yang setara, relevan, dan berdampak."
    :background-image="$backgroundImage"
    background-alt="Kegiatan program edukasi hukum Edulaw Project"
    :breadcrumbs="[
        ['label' => 'Beranda', 'url' => $homeUrl],
        ['label' => 'Program'],
    ]"
    :stats="$stats"
    panel-label="Statistik Program"
    uniform-height
/>
