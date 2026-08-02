@php
    $initials = \Illuminate\Support\Str::of((string) $name)
        ->squish()
        ->explode(' ')
        ->filter()
        ->take(2)
        ->map(fn (string $part): string => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
        ->implode('') ?: 'EP';
@endphp

<div class="edulaw-profile-identity">
    <span class="edulaw-user-initials">{{ $initials }}</span>
    <div class="edulaw-table-identity">
        <span class="edulaw-table-identity__title">{{ $name ?: 'Tanpa nama' }}</span>
        <span class="edulaw-table-identity__meta">{{ $email ?: '—' }}</span>
        @if (filled($affiliation ?? null))
            <span class="edulaw-table-identity__meta">{{ $affiliation }}</span>
        @endif
    </div>
</div>
