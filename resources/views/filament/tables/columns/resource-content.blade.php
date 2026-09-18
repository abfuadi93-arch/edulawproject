@php
    $imageUrl = $imageUrl ?? null;
    $title = $title ?? '—';
    $metadata = collect($metadata ?? [])->filter(fn ($item) => filled($item))->join(' · ');
    $isPortrait = $isPortrait ?? false;
    $hasDocument = $hasDocument ?? false;
@endphp

<div @class(['edulaw-resource-content', 'edulaw-resource-content-portrait' => $isPortrait])>
    <div class="edulaw-resource-thumbnail" aria-hidden="true">
        <x-filament::icon icon="heroicon-o-photo" />
        @if ($imageUrl)
            <img src="{{ $imageUrl }}" alt="" loading="lazy" x-on:error="$el.style.display = 'none'">
        @endif
    </div>

    <div class="edulaw-resource-content-copy">
        <div class="edulaw-resource-content-title" title="{{ $title }}">
            {{ $title }}
        </div>

        @if ($metadata !== '' || $hasDocument)
            <div class="edulaw-resource-content-meta" title="{{ $metadata }}">
                @if ($hasDocument)
                    <x-filament::icon icon="heroicon-o-document-text" />
                @endif
                <span>{{ $metadata }}</span>
            </div>
        @endif
    </div>
</div>
