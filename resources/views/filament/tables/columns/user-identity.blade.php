@php
    $initials = \Illuminate\Support\Str::of((string) $name)
        ->squish()
        ->explode(' ')
        ->filter()
        ->take(2)
        ->map(fn (string $part): string => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
        ->implode('') ?: 'EP';
    $meta = collect([$email ?? null, $affiliation ?? null])->filter()->join(' · ');
@endphp

<div class="edulaw-profile-identity">
    <span class="edulaw-user-initials">{{ $initials }}</span>
    <div class="edulaw-table-identity">
        <span class="edulaw-table-identity__title">{{ $name ?: 'Tanpa nama' }}</span>
        <span class="edulaw-table-identity__meta" title="{{ $meta ?: '—' }}">{{ $meta ?: '—' }}</span>
    </div>
</div>
