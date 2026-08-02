<div class="edulaw-profile-identity">
    {!! $avatar !!}
    <div class="edulaw-table-identity">
        <span class="edulaw-table-identity__title">{{ $name ?: 'Tanpa nama' }}</span>
        @if (filled($position ?? null))
            <span class="edulaw-table-identity__meta">{{ $position }}</span>
        @endif
        @if (filled($email ?? null))
            <span class="edulaw-table-identity__meta">{{ $email }}</span>
        @endif
    </div>
</div>
