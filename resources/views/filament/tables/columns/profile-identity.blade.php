<div class="edulaw-profile-identity">
    {!! $avatar !!}
    <div class="edulaw-table-identity">
        <span class="edulaw-table-identity__title" title="{{ $name ?: 'Tanpa nama' }}">{{ $name ?: 'Tanpa nama' }}</span>
        <span class="edulaw-table-identity__meta" title="{{ $position ?: ($email ?: '—') }}">
            {{ $position ?: ($email ?: '—') }}
        </span>
        <span class="edulaw-author-profile__institution" title="{{ $institution ?: '—' }}">{{ $institution ?: '—' }}</span>
        <span class="edulaw-author-profile__counts">{{ $insightsCount }} artikel · {{ $publicationsCount }} publikasi</span>
    </div>
</div>
