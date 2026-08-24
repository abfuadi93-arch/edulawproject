@props([
    'title',
    'eyebrow',
    'description',
    'updatedAt',
    'summaryItems' => [],
    'sections' => [],
    'backgroundImage' => null,
    'backgroundAlt' => '',
    'asideEyebrow' => 'Pertanyaan?',
    'asideTitle' => 'Hubungi Edulaw Project.',
    'asideBody' => 'Sampaikan pertanyaan melalui kanal resmi Edulaw Project.',
])

<main class="overflow-x-clip bg-[#f7f8fa] text-brand-ink">
    <x-shared.page-header
        :title="$title"
        :compact="true"
        :eyebrow="$eyebrow"
        :channel-header="true"
        grid-class="gap-5 px-5 py-7 sm:w-full sm:px-6 sm:py-8 lg:min-h-[240px] lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center lg:px-8 lg:py-8"
        :description="$description"
        :background-image="$backgroundImage"
        :background-alt="$backgroundAlt"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => $title],
        ]"
    >
        <div class="inline-flex min-h-11 items-center rounded-lg border border-white/15 bg-white/10 px-4 py-2 text-sm font-bold text-white/85 backdrop-blur">
            Terakhir diperbarui: <span class="ml-1.5 text-brand-amber">{{ $updatedAt }}</span>
        </div>
    </x-shared.page-header>

    <section class="bg-white py-8 sm:py-9 lg:py-10" aria-label="Ringkasan {{ $title }}">
        <div class="section-shell">
            <div class="grid gap-3 md:grid-cols-3">
                @foreach ($summaryItems as $index => $item)
                    <article class="rounded-[14px] bg-[#f7f8fa] p-5 sm:p-6">
                        <div class="flex items-start gap-4">
                            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-brand-amber-soft text-[10px] font-black text-brand-navy">
                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <div class="min-w-0">
                                <h2 class="text-lg font-black leading-snug text-brand-navy">{{ $item['title'] }}</h2>
                                <p class="mt-2 text-[15px] leading-6 text-slate-600">{{ $item['description'] }}</p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-8 sm:py-9 lg:py-10">
        <div class="section-shell grid gap-6 lg:grid-cols-[minmax(0,1fr)_280px] lg:items-start">
            <article class="rounded-[14px] bg-white p-5 sm:p-7 lg:p-9">
                <div class="divide-y divide-slate-200">
                    @foreach ($sections as $sectionIndex => $section)
                        <section id="bagian-{{ $sectionIndex + 1 }}" class="scroll-mt-24 py-7 first:pt-0 last:pb-0">
                            <h2 class="font-display text-xl font-black leading-snug text-brand-navy sm:text-2xl">
                                {{ $section['title'] }}
                            </h2>

                            <div class="edulaw-readable mt-3 max-w-none text-slate-700">
                                @foreach ($section['content'] as $paragraph)
                                    <p>{{ $paragraph }}</p>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            </article>

            <aside class="lg:sticky lg:top-24">
                <nav class="rounded-[14px] bg-white p-5" aria-label="Daftar bagian {{ $title }}">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-teal">Daftar Bagian</p>
                    <ol class="mt-4 grid gap-1.5">
                        @foreach ($sections as $sectionIndex => $section)
                            <li>
                                <a href="#bagian-{{ $sectionIndex + 1 }}" class="flex min-h-10 items-start rounded-lg px-3 py-2 text-sm font-bold leading-5 text-slate-600 transition hover:bg-brand-teal-soft hover:text-brand-navy">
                                    {{ $section['title'] }}
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </nav>

                <x-shared.cta-card
                    class="mt-4"
                    :eyebrow="$asideEyebrow"
                    :title="$asideTitle"
                    :body="$asideBody"
                    :url="route('contact.index')"
                    label="Hubungi Kami"
                />
            </aside>
        </div>
    </section>
</main>
