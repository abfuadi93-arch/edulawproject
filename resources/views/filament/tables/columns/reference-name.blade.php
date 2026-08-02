<div class="edulaw-table-identity">
    <span class="edulaw-table-identity__title">{{ $name ?: '—' }}</span>
    @if (filled($slug ?? null))
        <span class="edulaw-table-identity__slug">{{ $slug }}</span>
    @endif
    @if (filled($description ?? null))
        <span class="edulaw-table-identity__meta edulaw-line-clamp-1">{{ $description }}</span>
    @endif
</div>
