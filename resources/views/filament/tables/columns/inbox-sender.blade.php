@php
    $metadata = collect([$email ?? null, $phone ?? null])->filter();
@endphp

<div class="edulaw-table-identity">
    <span class="edulaw-table-identity__title">{{ $name ?: 'Tanpa nama' }}</span>
    @foreach ($metadata as $item)
        <span class="edulaw-table-identity__meta">{{ $item }}</span>
    @endforeach
</div>
