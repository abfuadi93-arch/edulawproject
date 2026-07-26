@php
    $hasCollaborationRoute = \Illuminate\Support\Facades\Route::has('collaboration.index');
    $editorialSubmissionRoute = collect([
        'editorial-submissions.create',
        'submissions.create',
        'insights.submit',
    ])->first(fn (string $routeName): bool => \Illuminate\Support\Facades\Route::has($routeName));
    $writerGuidelineRoute = collect([
        'writer-guidelines',
        'insights.guidelines',
    ])->first(fn (string $routeName): bool => \Illuminate\Support\Facades\Route::has($routeName));
    $editorialSubmissionUrl = $editorialSubmissionRoute ? route($editorialSubmissionRoute) : null;
@endphp

<section id="open-submission" class="home-section scroll-mt-24 bg-white" aria-labelledby="home-submission-title">
    <div class="section-shell">
        <div class="home-section-header">
            <div class="home-section-copy">
                <p class="home-section-eyebrow text-brand-coral">
                    Open Submission
                </p>

                <h2 id="home-submission-title" class="home-section-title">
                    Dua jalur kontribusi bersama Edulaw.
                </h2>

                <p class="home-section-description">
                    Pilih jalur editorial untuk gagasan tertulis atau jalur kelembagaan untuk merancang agenda bersama.
                </p>
            </div>
        </div>

        <div class="mt-8 grid gap-5 md:grid-cols-2">
            <article class="home-card flex h-full flex-col p-5 sm:p-6">
                <p class="text-xs font-bold text-brand-navy">
                    Kontribusi Editorial
                </p>
                <h3 class="mt-2 text-xl font-extrabold text-brand-ink">
                    Kirim Tulisan
                </h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Edulaw menerima opini atau analisis eksternal mengenai hukum, kebijakan, dan isu publik untuk melalui moderasi editorial sebelum diterbitkan.
                </p>
                <ul class="mt-4 space-y-2 text-sm leading-6 text-slate-600">
                    <li class="flex gap-2"><span aria-hidden="true">•</span><span>Topik relevan dan rujukan disampaikan dengan jelas.</span></li>
                    <li class="flex gap-2"><span aria-hidden="true">•</span><span>Redaksi dapat meminta perbaikan sebelum penerbitan.</span></li>
                </ul>

                @if ($editorialSubmissionUrl)
                    <a
                        href="{{ $editorialSubmissionUrl }}"
                        class="btn-dark mt-5 min-h-11 w-fit"
                    >
                        Kirim Tulisan
                    </a>
                @else
                    <p class="mt-5 text-xs font-semibold leading-5 text-slate-500">
                        Kanal pengiriman tulisan belum dibuka.
                    </p>
                @endif

                @if ($writerGuidelineRoute)
                    <a
                        href="{{ route($writerGuidelineRoute) }}"
                        class="mt-3 w-fit text-xs font-bold text-brand-navy underline decoration-brand-amber decoration-2 underline-offset-4 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-navy"
                    >
                        Pedoman Penulis
                    </a>
                @endif
            </article>

            <article class="home-card flex h-full flex-col border-brand-amber/40 bg-[#FBF8F1] p-5 sm:p-6">
                <p class="text-xs font-bold text-brand-navy">
                    Kolaborasi Kelembagaan
                </p>
                <h3 class="mt-2 text-xl font-extrabold text-brand-ink">
                    Ajukan Kolaborasi
                </h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Gunakan jalur ini untuk program, diskusi, pelatihan, riset, publikasi, atau kerja sama kelembagaan bersama Edulaw Project.
                </p>

                @if ($hasCollaborationRoute)
                    <a
                        href="{{ route('collaboration.index') }}"
                        class="btn-secondary mt-5 min-h-11 w-fit"
                    >
                        Ajukan Kolaborasi
                    </a>
                @endif
            </article>
        </div>
    </div>
</section>
