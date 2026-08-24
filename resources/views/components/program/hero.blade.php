@props([
    'stats' => [],
    'backgroundImage' => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1800&q=85',
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
    :highlights="[
        'Belajar hukum secara kontekstual',
        'Berdiskusi dengan perspektif beragam',
        'Berkolaborasi untuk dampak publik',
    ]"
    :stats="$stats"
    panel-label="Statistik Program"
/>
