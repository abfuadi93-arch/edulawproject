@props([
    'articleCount' => 0,
    'categoryCount' => 0,
])

<x-shared.primary-hero
    title="Gagasan Hukum untuk Ruang Publik"
    eyebrow="Kanal Editorial"
    description="Editorial Edulaw menghadirkan analisis hukum, regulasi, dan kebijakan publik yang jernih untuk memperluas pemahaman bersama."
    background-image="https://images.unsplash.com/photo-1589578527966-fdac0f44566c?auto=format&fit=crop&w=1800&q=85"
    background-alt="Patung Lady Justice sebagai simbol hukum"
    background-position="center 38%"
    :breadcrumbs="[
        ['label' => 'Beranda', 'url' => route('home')],
        ['label' => 'Editorial'],
    ]"
    :stats="[
        ['value' => number_format((int) $articleCount, 0, ',', '.'), 'label' => 'Artikel Terbit'],
        ['value' => number_format((int) $categoryCount, 0, ',', '.'), 'label' => 'Kategori Editorial'],
    ]"
    panel-label="Statistik Editorial"
/>
