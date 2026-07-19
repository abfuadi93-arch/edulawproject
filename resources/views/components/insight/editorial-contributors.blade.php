@props(['contributors'])

@if ($contributors->isNotEmpty())
    @php
        $contributors = collect($contributors)->take(6);

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

    <section class="border-y border-slate-200 bg-[#fbfaf7] py-10 sm:py-12 lg:py-14">
        <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-2xl">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-brand-coral">Penulis dan peneliti</p>
                    <h2 class="mt-2 font-display text-3xl font-bold text-brand-ink sm:text-4xl">Kontributor Editorial</h2>
                </div>
                <a href="{{ route('about') }}#tim" class="inline-flex min-h-11 w-fit items-center justify-center rounded-full border border-brand-amber/50 bg-white px-4 text-sm font-bold text-brand-navy shadow-sm transition duration-200 hover:border-brand-amber hover:bg-brand-amber-soft focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-amber">Lihat Semua Kontributor</a>
            </div>

            <div class="mt-7 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($contributors as $author)
                    <a href="{{ route('profiles.show', $author->slug) }}" class="group flex min-w-0 items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm shadow-brand-ink/5 transition duration-200 hover:border-brand-amber/50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-amber" data-editorial-contributor="{{ $author->id }}">
                        <span class="relative grid h-14 w-14 shrink-0 place-items-center overflow-hidden rounded-full bg-brand-navy text-base font-bold text-white ring-1 ring-slate-200">
                            <span aria-hidden="true">{{ Illuminate\Support\Str::upper(Illuminate\Support\Str::substr($author->name, 0, 1)) }}</span>
                            @if ($author->photo_url)
                                <img src="{{ $author->photo_url }}" alt="Foto profil {{ $author->name }}" loading="lazy" class="absolute inset-0 h-full w-full object-cover" onerror="this.remove()">
                            @endif
                        </span>
                        <span class="min-w-0">
                            <span class="line-clamp-2 block font-bold leading-snug text-brand-ink transition group-hover:text-brand-navy">{{ $author->name }}</span>
                            <span class="mt-1 line-clamp-2 block text-xs font-medium leading-5 text-slate-500">{{ $publicRoleLabel($author) }}</span>
                            <span class="mt-1.5 block text-[11px] font-bold text-brand-coral">{{ $author->published_insights_count }} tulisan terbit</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
