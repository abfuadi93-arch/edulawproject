<form
    method="POST"
    action="{{ url('/kontak') }}"
    class="rounded-4xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8"
>
    @csrf

    <div>
        <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-navy">
            Form Kontak
        </p>

        <h2 class="mt-3 text-2xl font-extrabold tracking-tight text-brand-ink sm:text-3xl">
            Kirim Pesan
        </h2>

        <p class="mt-3 text-sm leading-6 text-slate-600">
            Isi formulir berikut untuk pertanyaan umum, informasi program, publikasi,
            atau kebutuhan komunikasi lainnya.
        </p>
    </div>

    <div class="mt-8 grid gap-5">
        {{-- Nama --}}
        <div>
            <label for="contact_name" class="block text-sm font-bold text-brand-ink">
                Nama Lengkap <span class="text-brand-navy">*</span>
            </label>

            <input
                id="contact_name"
                name="name"
                type="text"
                value="{{ old('name') }}"
                placeholder="Masukkan nama lengkap Anda"
                required
                class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-brand-ink shadow-sm outline-none transition placeholder:text-slate-400 focus:border-brand-blue focus:ring-4 focus:ring-brand-mist"
            >

            @error('name')
                <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="contact_email" class="block text-sm font-bold text-brand-ink">
                Email <span class="text-brand-navy">*</span>
            </label>

            <input
                id="contact_email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                placeholder="nama@email.com"
                required
                class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-brand-ink shadow-sm outline-none transition placeholder:text-slate-400 focus:border-brand-blue focus:ring-4 focus:ring-brand-mist"
            >

            @error('email')
                <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Subjek --}}
        <div>
            <label for="contact_subject" class="block text-sm font-bold text-brand-ink">
                Subjek <span class="text-brand-navy">*</span>
            </label>

            <select
                id="contact_subject"
                name="subject"
                required
                class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-brand-ink shadow-sm outline-none transition focus:border-brand-blue focus:ring-4 focus:ring-brand-mist"
            >
                <option value="">Pilih subjek pesan</option>
                <option value="Pertanyaan Umum" @selected(old('subject') === 'Pertanyaan Umum')>
                    Pertanyaan Umum
                </option>
                <option value="Informasi Program" @selected(old('subject') === 'Informasi Program')>
                    Informasi Program
                </option>
                <option value="Riset & Publikasi" @selected(old('subject') === 'Riset & Publikasi')>
                    Riset &amp; Publikasi
                </option>
                <option value="Multimedia" @selected(old('subject') === 'Multimedia')>
                    Multimedia
                </option>
                <option value="Teknis Website" @selected(old('subject') === 'Teknis Website')>
                    Teknis Website
                </option>
                <option value="Lainnya" @selected(old('subject') === 'Lainnya')>
                    Lainnya
                </option>
            </select>

            @error('subject')
                <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Pesan --}}
        <div>
            <label for="contact_message" class="block text-sm font-bold text-brand-ink">
                Pesan <span class="text-brand-navy">*</span>
            </label>

            <textarea
                id="contact_message"
                name="message"
                rows="5"
                placeholder="Tulis pesan Anda di sini..."
                required
                class="mt-2 block w-full resize-y rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-brand-ink shadow-sm outline-none transition placeholder:text-slate-400 focus:border-brand-blue focus:ring-4 focus:ring-brand-mist"
            >{{ old('message') }}</textarea>

            @error('message')
                <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Privacy note --}}
    <div class="mt-5 flex gap-3 rounded-2xl bg-brand-paper p-4 text-sm leading-6 text-slate-700">
        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-silver text-brand-ink">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 3 5 6v6c0 4.4 2.8 7.4 7 9 4.2-1.6 7-4.6 7-9V6l-7-3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                <path d="m9.5 12 1.7 1.7 3.6-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>

        <p>
            Data yang Anda berikan akan digunakan untuk menanggapi pesan dan kebutuhan
            komunikasi terkait Edulaw Project. Lihat
            <a href="{{ url('/kebijakan-privasi') }}" class="font-bold text-brand-ink hover:text-brand-black">
                Kebijakan Privasi
            </a>
            kami.
        </p>
    </div>

    {{-- Actions --}}
    <div class="mt-7 grid gap-3 sm:grid-cols-[1fr_auto] sm:items-center">
        <button
            type="submit"
            class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-brand-black px-6 py-3.5 text-sm font-extrabold text-white shadow-sm transition hover:bg-brand-navy hover:text-white"
        >
            Kirim Pesan
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>

        <a
            href="https://wa.me/6281529927677"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 py-3.5 text-sm font-extrabold text-brand-ink shadow-sm transition hover:border-brand-silver hover:bg-brand-paper sm:w-auto"
        >
            Chat via WhatsApp
        </a>
    </div>

    <p class="mt-4 text-xs text-slate-500">
        <span class="text-brand-navy">*</span> Wajib diisi
    </p>
</form>