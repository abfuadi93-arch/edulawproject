@php
    $user = filament()->auth()->user();
    $role = $user?->getRoleNames()->first();
@endphp

@if ($user)
    <div class="edulaw-topbar-user-context" aria-hidden="true">
        <span class="edulaw-topbar-user-name">{{ $user->name }}</span>
        <span class="edulaw-topbar-user-role">{{ $role ? \Illuminate\Support\Str::headline($role) : 'Edulaw Admin' }}</span>
    </div>
@endif
