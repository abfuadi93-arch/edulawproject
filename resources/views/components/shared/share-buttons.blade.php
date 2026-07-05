@props([
    'title' => null,
    'url' => null,
    'description' => null,
    'label' => 'Bagikan',
])

<x-share-buttons
    :title="$title"
    :url="$url"
    :description="$description"
    :label="$label"
    {{ $attributes }}
/>
