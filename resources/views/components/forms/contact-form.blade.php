<form
    method="POST"
    action="{{ url('/kontak') }}"
    class="rounded-[14px] bg-white p-5 sm:p-7"
>
    @csrf

    <div>
        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-teal">
            Form Kontak
        </p>

        <h2 class="mt-2 font-display text-2xl font-black text-brand-navy sm:text-3xl">
            Kirim Pesan
        </h2>

        <p class="mt-3 text-sm leading-6 text-slate-600">
            Isi formulir berikut untuk pertanyaan umum, informasi program, publikasi,
            atau kebutuhan komunikasi lainnya.
        </p>
    </div>

    <div class="mt-6 grid gap-4">
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
                class="mt-2 block min-h-11 w-full rounded-lg border border-slate-200 bg-[#f8fafc] px-3.5 py-2.5 text-sm text-brand-ink outline-none transition placeholder:text-slate-400 focus:border-brand-navy focus:bg-white focus:ring-2 focus:ring-brand-navy/10"
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
                class="mt-2 block min-h-11 w-full rounded-lg border border-slate-200 bg-[#f8fafc] px-3.5 py-2.5 text-sm text-brand-ink outline-none transition placeholder:text-slate-400 focus:border-brand-navy focus:bg-white focus:ring-2 focus:ring-brand-navy/10"
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
                class="mt-2 block min-h-11 w-full rounded-lg border border-slate-200 bg-[#f8fafc] px-3.5 py-2.5 text-sm text-brand-ink outline-none transition focus:border-brand-navy focus:bg-white focus:ring-2 focus:ring-brand-navy/10"
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
                class="mt-2 block w-full resize-y rounded-lg border border-slate-200 bg-[#f8fafc] px-3.5 py-3 text-sm leading-6 text-brand-ink outline-none transition placeholder:text-slate-400 focus:border-brand-navy focus:bg-white focus:ring-2 focus:ring-brand-navy/10"
            >{{ old('message') }}</textarea>

            @error('message')
                <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Privacy note --}}
    <div class="mt-5 flex gap-3 rounded-[12px] bg-[#f7f8fa] p-4 text-sm leading-6 text-slate-700">
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
        class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg bg-brand-navy px-5 py-3 text-sm font-black text-white transition hover:bg-[#294f82]"
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
            class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-5 py-3 text-sm font-black text-brand-navy transition hover:border-brand-teal/40 hover:bg-brand-teal-soft sm:w-auto"
        >
            Chat via WhatsApp
        </a>
    </div>

    <p class="mt-4 text-xs text-slate-500">
        <span class="text-brand-navy">*</span> Wajib diisi
    </p>
</form>
