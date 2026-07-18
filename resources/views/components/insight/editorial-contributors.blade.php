@props(['contributors'])

@if ($contributors->isNotEmpty())
    @php
        $contributors = collect($contributors)->take(4);

        $publicRoleLabel = function ($author): string {
            $rawLabel = collect([
                $author->position ?? null,
                $author->job_title ?? null,
                $author->title ?? null,
            ])
                ->map(fn ($value): string => trim((string) $value))
                ->first(fn (string $value): bool => $value !== '');

            $normalized = Illuminate\Support\Str::of((string) $rawLabel)
                ->lower()
                ->replace(['-', '_'], ' ')
                ->squish()
                ->toString();

            if ($normalized === '' || in_array($normalized, ['admin', 'user', 'superadmin', 'super admin', 'writer', 'kontributoe'], true)) {
                return 'Kontributor Editorial';
            }

            if (in_array($normalized, ['redaksi edulaw', 'redaksi', 'editorial team', 'tim editorial'], true)) {
                return 'Tim Editorial';
            }

            if (in_array($normalized, ['tim riset', 'research team', 'riset'], true)) {
                return 'Tim Riset Edulaw';
            }

            return (string) $rawLabel;
        };
    @endphp

    <section class="border-y border-slate-200 bg-[#fbfaf7] pt-12 pb-12 sm:pt-14 sm:pb-14 lg:pt-12 lg:pb-12">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-brand-coral">Penulis dan peneliti</p>
                <h2 class="mt-2 font-display text-3xl font-bold text-brand-ink sm:text-4xl">Kontributor Editorial</h2>
            </div>

            <div class="mt-8 grid gap-x-6 gap-y-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($contributors as $author)
                    <a href="{{ route('profiles.show', $author->slug) }}" class="group flex min-w-0 items-center gap-3 rounded-md p-2 transition hover:bg-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber lg:block lg:p-0">
                        @if ($author->photo_url)
                            <img src="{{ $author->photo_url }}" alt="Foto profil {{ $author->name }}" loading="lazy" class="h-14 w-14 shrink-0 rounded-lg object-cover ring-1 ring-slate-200 lg:aspect-square lg:h-auto lg:w-full lg:rounded-xl">
                        @else
                            <span class="grid h-14 w-14 shrink-0 place-items-center rounded-lg bg-brand-navy text-lg font-bold text-white lg:aspect-square lg:h-auto lg:w-full lg:rounded-xl lg:text-4xl">{{ Illuminate\Support\Str::upper(Illuminate\Support\Str::substr($author->name, 0, 1)) }}</span>
                        @endif
                        <span class="min-w-0 lg:mt-3 lg:block">
                            <span class="line-clamp-2 block font-bold leading-snug text-brand-ink transition group-hover:text-brand-navy">{{ $author->name }}</span>
                            <span class="mt-1 line-clamp-2 block text-xs font-medium leading-5 text-slate-500">{{ $publicRoleLabel($author) }}</span>
                            <span class="mt-1.5 block text-[11px] font-bold text-brand-coral">{{ $author->published_insights_count }} tulisan terbit</span>
                            @if (! empty($author->interests[0] ?? null))
                                <span class="mt-1 line-clamp-1 block text-[11px] text-slate-500">{{ $author->interests[0] }}</span>
                            @endif
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
