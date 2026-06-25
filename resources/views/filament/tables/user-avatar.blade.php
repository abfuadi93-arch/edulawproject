@php
    /** @var \App\Models\User $record */
    $avatarUrl = $record->avatar_url;
    $initials = $record->initials;
@endphp

<div class="inline-grid h-9 w-9 place-items-center overflow-hidden rounded-full bg-blue-50 text-xs font-semibold text-blue-700 ring-1 ring-blue-100">
    @if ($avatarUrl)
        <img
            src="{{ $avatarUrl }}"
            alt="{{ $record->name }}"
            class="h-full w-full object-cover"
            onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
        >
    @endif
    <span @class(['hidden' => filled($avatarUrl)])>
        {{ $initials }}
    </span>
</div>
