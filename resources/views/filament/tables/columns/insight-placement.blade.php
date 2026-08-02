@php
    $placements = collect([
        \App\Filament\Resources\Insights\InsightResource::isPublishReady($record) ? ['label' => 'Siap Tayang', 'color' => 'success'] : null,
        $record->featured ? ['label' => 'Unggulan', 'color' => 'primary'] : null,
        $record->editor_pick ? ['label' => 'Pilihan Editor', 'color' => 'warning'] : null,
    ])->filter();
@endphp

@if ($placements->isEmpty())
    <span class="edulaw-insight-placement-empty">&mdash;</span>
@else
    <div class="edulaw-insight-placement-list">
        @foreach ($placements as $placement)
            <x-filament::badge :color="$placement['color']">
                {{ $placement['label'] }}
            </x-filament::badge>
        @endforeach
    </div>
@endif
