@php
    $date = $record->published_at ?? $record->created_at;
    $dateLabel = $record->published_at ? 'Rilis' : 'Dibuat';
    $coverUrl = filled($record->cover_image) ? edulaw_file_url($record->cover_image) : null;
    $placements = collect([
        \App\Filament\Resources\Insights\InsightResource::isPublishReady($record) ? ['label' => 'Siap Tayang', 'color' => 'success'] : null,
        $record->featured ? ['label' => 'Unggulan', 'color' => 'primary'] : null,
        $record->editor_pick ? ['label' => 'Pilihan Editor', 'color' => 'warning'] : null,
    ])->filter()->values();
    $primaryPlacement = $placements->first();
    $remainingPlacements = $placements->slice(1);
    $updatedAt = $record->updated_at?->locale('id')->translatedFormat('d M Y, H:i');
@endphp

<div class="edulaw-insight-article">
    <div class="edulaw-insight-thumbnail" aria-hidden="true">
        @if ($coverUrl)
            <img src="{{ $coverUrl }}" alt="" loading="lazy">
        @else
            <x-filament::icon icon="heroicon-o-photo" />
        @endif
    </div>

    <div class="edulaw-insight-article-copy">
        <div class="edulaw-insight-title" title="{{ $record->title }}">
            {{ $record->title }}
        </div>

        <div
            class="edulaw-insight-meta"
            @if ($updatedAt) title="Diperbarui {{ $updatedAt }} · Urutan {{ $record->sort_order ?? '—' }}" @endif
        >
            {{ $record->display_author }}
            @if ($date)
                <span aria-hidden="true"> &middot; </span>{{ $dateLabel }} {{ $date->locale('id')->translatedFormat('d M Y') }}
            @endif
        </div>

        @if ($primaryPlacement)
            <div class="edulaw-insight-inline-placement" title="{{ $placements->pluck('label')->join(', ') }}">
                <x-filament::badge :color="$primaryPlacement['color']">
                    {{ $primaryPlacement['label'] }}
                </x-filament::badge>

                @if ($remainingPlacements->isNotEmpty())
                    <x-filament::badge color="gray">
                        +{{ $remainingPlacements->count() }}
                    </x-filament::badge>
                @endif
            </div>
        @endif
    </div>
</div>
