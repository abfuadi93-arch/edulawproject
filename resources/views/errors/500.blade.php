@extends('layouts.app')

@section('title', 'Terjadi Kesalahan | Edulaw Project')
@section('meta_description', 'Layanan Edulaw Project sedang mengalami kendala sementara. Silakan muat ulang halaman atau kembali beberapa saat lagi.')
@section('robots', 'noindex,nofollow')

@section('content')
<main class="min-h-[calc(100vh-82px)] bg-[#fbf7ef]">
    <section class="relative isolate overflow-hidden">
        {{-- Background decoration --}}
        <div class="pointer-events-none absolute -right-32 -top-32 h-96 w-96 rounded-full bg-amber-200/50 blur-3xl"></div>
        <div class="pointer-events-none absolute -left-32 bottom-0 h-96 w-96 rounded-full bg-slate-200/70 blur-3xl"></div>

        <div class="relative mx-auto grid min-h-[calc(100vh-82px)] max-w-7xl items-center gap-12 px-4 py-16 sm:px-6 lg:grid-cols-[0.95fr_1.05fr] lg:px-8">
            {{-- Text --}}
            <div>
                <div class="edulaw-badge edulaw-badge-lg edulaw-badge-amber">
                    <span class="h-2 w-2 rounded-full bg-brand-amber"></span>
                    Error 500
                </div>

                <h1 class="mt-6 text-4xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-5xl lg:text-6xl">
                    Terjadi kesalahan pada server.
                </h1>

                <p class="mt-5 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                    Maaf, halaman belum dapat ditampilkan karena terjadi gangguan teknis.
                    Silakan coba beberapa saat lagi atau kembali ke halaman utama Edulaw Project.
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a
                        href="{{ url('/') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-950 px-6 py-3.5 text-sm font-extrabold text-white shadow-sm transition hover:bg-amber-500 hover:text-slate-950"
                    >
                        Kembali ke Beranda
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>

                    <a
                        href="{{ url('/kontak') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-6 py-3.5 text-sm font-extrabold text-slate-950 shadow-sm transition hover:border-amber-300 hover:bg-amber-50"
                    >
                        Hubungi Kami
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M7 4h3l1.5 4-2 1.2c.9 1.9 2.4 3.4 4.3 4.3l1.2-2L19 13v3c0 1.7-1.3 3-3 3A11 11 0 0 1 5 8c0-1.7 1.3-3 3-3Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>

                <div class="mt-10 rounded-4xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-amber-600">
                        Yang dapat Anda lakukan
                    </p>

                    <div class="mt-5 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl bg-[#fbf7ef] p-4">
                            <p class="text-sm font-extrabold text-slate-950">
                                Muat ulang halaman
                            </p>
                            <p class="mt-1 text-xs leading-5 text-slate-500">
                                Coba refresh browser beberapa saat lagi.
                            </p>
                        </div>

                        <div class="rounded-2xl bg-[#fbf7ef] p-4">
                            <p class="text-sm font-extrabold text-slate-950">
                                Kembali ke beranda
                            </p>
                            <p class="mt-1 text-xs leading-5 text-slate-500">
                                Akses kembali kanal utama Edulaw Project.
                            </p>
                        </div>

                        <div class="rounded-2xl bg-[#fbf7ef] p-4">
                            <p class="text-sm font-extrabold text-slate-950">
                                Laporkan kendala
                            </p>
                            <p class="mt-1 text-xs leading-5 text-slate-500">
                                Hubungi kami jika kendala terus terjadi.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Visual card --}}
            <div class="relative">
                <div class="overflow-hidden rounded-4xl-slate-950 p-6 text-white shadow-xl shadow-slate-900/10 sm:p-8">
                    <div class="relative overflow-hidden rounded-3xl bg-linear-to-br from-slate-900 via-slate-800 to-amber-400 p-8">
                        <div class="pointer-events-none absolute -right-16 -top-16 h-52 w-52 rounded-full bg-white/10 blur-2xl"></div>
                        <div class="pointer-events-none absolute -left-16 bottom-0 h-52 w-52 rounded-full bg-amber-300/20 blur-2xl"></div>

                        <div class="relative">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-amber-300">
                                Internal Server Error
                            </p>

                            <div class="mt-10 flex items-end gap-3">
                                <span class="text-8xl font-black leading-none tracking-tight text-white sm:text-9xl">
                                    5
                                </span>

                                <span class="mb-3 flex h-20 w-20 items-center justify-center rounded-full border border-white/20 bg-white/10 text-4xl font-black text-amber-300 backdrop-blur sm:h-24 sm:w-24 sm:text-5xl">
                                    0
                                </span>

                                <span class="text-8xl font-black leading-none tracking-tight text-white sm:text-9xl">
                                    0
                                </span>
                            </div>

                            <h2 class="mt-8 max-w-md text-2xl font-extrabold leading-tight sm:text-3xl">
                                Sistem sedang mengalami gangguan sementara.
                            </h2>

                            <p class="mt-4 max-w-md text-sm leading-6 text-white/70">
                                Tim pengelola dapat memeriksa log aplikasi dan konfigurasi server
                                untuk memastikan layanan kembali berjalan normal.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-amber-300">
                                Saran Teknis
                            </p>

                            <p class="mt-2 text-sm font-semibold leading-6 text-white/75">
                                Periksa file log Laravel jika error masih muncul.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-amber-300">
                                Bantuan
                            </p>

                            <a
                                href="{{ url('/kontak') }}"
                                class="mt-2 inline-flex items-center gap-2 text-sm font-extrabold text-white transition hover:text-amber-300"
                            >
                                Hubungi Edulaw
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
