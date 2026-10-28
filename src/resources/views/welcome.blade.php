<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="{{ asset('branding/logo.svg') }}">
    <title>{{ config('app.name') }} &mdash; Institut Widya Pratama Pekalongan</title>
    @vite('resources/css/app.css')
</head>

<body class="relative overflow-x-hidden bg-gradient-to-br from-slate-100 via-blue-50 to-indigo-100 text-slate-800 antialiased">

    <!-- Dekorasi latar -->
    <div class="pointer-events-none absolute -top-24 -left-24 h-96 w-96 rounded-full bg-blue-300/20 blur-3xl"></div>
    <div class="pointer-events-none absolute top-1/3 -right-24 h-96 w-96 rounded-full bg-indigo-300/20 blur-3xl"></div>

    {{-- ====================== NAVBAR ====================== --}}
    <header class="sticky top-0 z-50 border-b border-white/50 bg-white/70 backdrop-blur-lg">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <a href="/" class="flex items-center gap-3">
                <img src="{{ asset('images/logo-iwima.png') }}" alt="Logo IWIMA" class="h-10 w-10 object-contain">
                <span class="text-lg font-bold text-slate-800">
                    Helpdesk <span class="text-blue-600">IWIMA</span>
                </span>
            </a>

            <div class="flex items-center gap-3">
                <a href="/admin/login"
                    class="hidden rounded-xl border border-slate-300 bg-white/60 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-white sm:inline-block">
                    Panel Admin
                </a>
                <a href="{{ route('login') }}"
                    class="rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-lg transition hover:shadow-xl">
                    Masuk
                </a>
            </div>
        </nav>
    </header>

    {{-- ====================== HERO ====================== --}}
    <section class="mx-auto max-w-7xl px-6 pb-16 pt-14 lg:pb-24 lg:pt-20">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div>
                <span class="inline-block rounded-full border border-blue-200 bg-blue-50 px-4 py-1 text-sm font-medium text-blue-700">
                    Layanan Teknis &amp; Perawatan Infrastruktur
                </span>

                <h1 class="mt-6 text-4xl font-bold leading-tight text-slate-800 lg:text-5xl">
                    Laporkan Masalah IT &amp; Ajukan Kebutuhan dalam
                    <span class="text-blue-600">Satu Portal</span>
                </h1>

                <p class="mt-5 text-lg leading-relaxed text-slate-500">
                    Sistem Helpdesk Institut Widya Pratama Pekalongan &mdash; pantau penanganan
                    tiket perbaikan dan pengajuan barang secara transparan, cepat, dan realtime.
                </p>

                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="{{ route('login') }}"
                        class="rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-7 py-3 font-semibold text-white shadow-lg transition hover:scale-[1.02] hover:shadow-xl">
                        Masuk Sekarang
                    </a>
                    <a href="/admin/login"
                        class="rounded-xl border border-slate-300 bg-white/70 px-7 py-3 font-semibold text-slate-700 backdrop-blur transition hover:bg-white">
                        Panel Admin
                    </a>
                </div>
            </div>

            <div class="hidden lg:block">
                <img src="{{ asset('images/kampus.png') }}" alt="Kampus Institut Widya Pratama Pekalongan"
                    class="w-full rounded-[40px] object-cover shadow-2xl">
            </div>
        </div>
    </section>

    {{-- ====================== STATISTIK ====================== --}}
    <section class="mx-auto max-w-7xl px-6 pb-16">
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-3xl border border-white/60 bg-white/70 p-6 text-center shadow-xl backdrop-blur-2xl">
                <div class="text-4xl font-bold text-blue-600">{{ number_format($statistik['tiket']) }}</div>
                <div class="mt-2 text-sm font-medium text-slate-500">Tiket Perbaikan</div>
            </div>
            <div class="rounded-3xl border border-white/60 bg-white/70 p-6 text-center shadow-xl backdrop-blur-2xl">
                <div class="text-4xl font-bold text-emerald-600">{{ number_format($statistik['tiket_selesai']) }}</div>
                <div class="mt-2 text-sm font-medium text-slate-500">Tiket Selesai</div>
            </div>
            <div class="rounded-3xl border border-white/60 bg-white/70 p-6 text-center shadow-xl backdrop-blur-2xl">
                <div class="text-4xl font-bold text-blue-600">{{ number_format($statistik['pengajuan']) }}</div>
                <div class="mt-2 text-sm font-medium text-slate-500">Pengajuan Barang</div>
            </div>
            <div class="rounded-3xl border border-white/60 bg-white/70 p-6 text-center shadow-xl backdrop-blur-2xl">
                <div class="text-4xl font-bold text-indigo-600">{{ number_format($statistik['kegiatan']) }}</div>
                <div class="mt-2 text-sm font-medium text-slate-500">Kegiatan Teknisi</div>
            </div>
        </div>
    </section>

    {{-- ====================== FITUR ====================== --}}
    <section class="mx-auto max-w-7xl px-6 pb-20">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-slate-800">Fitur Unggulan</h2>
            <p class="mt-3 text-slate-500">Kemudahan layanan helpdesk dalam satu sistem</p>
        </div>

        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Fitur 1: Tiket --}}
            <div class="rounded-3xl border border-white/60 bg-white/70 p-6 shadow-xl backdrop-blur-2xl transition hover:-translate-y-1 hover:shadow-2xl">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2.5" y="6" width="19" height="12" rx="2.5" />
                        <path d="M15 6v12" />
                    </svg>
                </div>
                <h3 class="mt-4 font-semibold text-slate-800">Lapor Tiket Perbaikan</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Catat gangguan perangkat dan infrastruktur, lalu pantau status penanganannya hingga selesai.
                </p>
            </div>

            {{-- Fitur 2: Pengajuan --}}
            <div class="rounded-3xl border border-white/60 bg-white/70 p-6 shadow-xl backdrop-blur-2xl transition hover:-translate-y-1 hover:shadow-2xl">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 8l9-4 9 4v8l-9 4-9-4V8z" />
                        <path d="M3 8l9 4 9-4" />
                        <path d="M12 12v8" />
                    </svg>
                </div>
                <h3 class="mt-4 font-semibold text-slate-800">Pengajuan Barang</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Ajukan kebutuhan barang dan keperluan teknis dengan alur persetujuan yang jelas dan terdokumentasi.
                </p>
            </div>

            {{-- Fitur 3: Realtime --}}
            <div class="rounded-3xl border border-white/60 bg-white/70 p-6 shadow-xl backdrop-blur-2xl transition hover:-translate-y-1 hover:shadow-2xl">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 9a6 6 0 1 1 12 0c0 4.5 1.5 5.5 1.5 5.5h-15S6 13.5 6 9z" />
                            <path d="M10 19a2.5 2.5 0 0 0 4 0" />
                    </svg>
                </div>
                <h3 class="mt-4 font-semibold text-slate-800">Status Realtime</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Notifikasi otomatis setiap ada perubahan status tiket atau pengajuan, tanpa perlu refresh halaman.
                </p>
            </div>

            {{-- Fitur 4: Laporan --}}
            <div class="rounded-3xl border border-white/60 bg-white/70 p-6 shadow-xl backdrop-blur-2xl transition hover:-translate-y-1 hover:shadow-2xl">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 3h8l4 4v14H6z" />
                        <path d="M14 3v4h4" />
                        <path d="M9 13h6" />
                        <path d="M9 17h6" />
                    </svg>
                </div>
                <h3 class="mt-4 font-semibold text-slate-800">Laporan &amp; Riwayat</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Riwayat lengkap aktivitas dan laporan berkala siap cetak dalam format PDF, Excel, dan Word.
                </p>
            </div>
        </div>
    </section>

    {{-- ====================== CARA KERJA ====================== --}}
    <section class="border-y border-white/60 bg-white/60 py-16 backdrop-blur">
        <div class="mx-auto max-w-7xl px-6">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-slate-800">Cara Kerja</h2>
                <p class="mt-3 text-slate-500">Tiga langkah mudah memanfaatkan layanan helpdesk</p>
            </div>

            <div class="mt-10 grid items-center gap-10 lg:grid-cols-2">
                <ol class="space-y-6">
                    <li class="flex gap-4">
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 font-bold text-white">1</span>
                        <div>
                            <h3 class="font-semibold text-slate-800">Masuk ke Sistem</h3>
                            <p class="mt-1 text-sm leading-relaxed text-slate-500">
                                Login menggunakan akun yang telah diberikan oleh administrator.
                            </p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 font-bold text-white">2</span>
                        <div>
                            <h3 class="font-semibold text-slate-800">Buat Laporan atau Pengajuan</h3>
                            <p class="mt-1 text-sm leading-relaxed text-slate-500">
                                Isi formulir tiket perbaikan atau pengajuan barang sesuai kebutuhan Anda.
                            </p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 font-bold text-white">3</span>
                        <div>
                            <h3 class="font-semibold text-slate-800">Pantau Hingga Selesai</h3>
                            <p class="mt-1 text-sm leading-relaxed text-slate-500">
                                Ikuti status penanganan secara realtime hingga laporan dinyatakan selesai.
                            </p>
                        </div>
                    </li>
                </ol>

                <div class="rounded-3xl border border-white/60 bg-white/70 p-6 shadow-xl backdrop-blur-2xl">
                    <img src="{{ asset('images/helpdesk-flow.svg') }}" alt="Alur Layanan Helpdesk"
                        class="w-full">
                </div>
            </div>
        </div>
    </section>

    {{-- ====================== KONTAK ====================== --}}
    <section class="mx-auto max-w-7xl px-6 py-16">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-slate-800">Hubungi Kami</h2>
            <p class="mt-3 text-slate-500">
                Kabid Teknis dan Perawatan Infrastruktur &mdash; Institut Widya Pratama Pekalongan
            </p>
        </div>

        <div class="mt-10 grid gap-6 sm:grid-cols-3">
            <div class="rounded-3xl border border-white/60 bg-white/70 p-6 text-center shadow-xl backdrop-blur-2xl">
                <div class="text-sm font-semibold uppercase tracking-wide text-blue-600">Alamat</div>
                <p class="mt-2 text-slate-600">Jl. Patriot No. 25, Pekalongan, Jawa Tengah</p>
            </div>
            <div class="rounded-3xl border border-white/60 bg-white/70 p-6 text-center shadow-xl backdrop-blur-2xl">
                <div class="text-sm font-semibold uppercase tracking-wide text-blue-600">Telepon</div>
                <p class="mt-2 text-slate-600">(0285) 427817</p>
            </div>
            <div class="rounded-3xl border border-white/60 bg-white/70 p-6 text-center shadow-xl backdrop-blur-2xl">
                <div class="text-sm font-semibold uppercase tracking-wide text-blue-600">Fax</div>
                <p class="mt-2 text-slate-600">(0285) 427815</p>
            </div>
        </div>
    </section>

    {{-- ====================== FOOTER ====================== --}}
    <footer class="border-t border-white/60 bg-white/70 backdrop-blur-lg">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-6 px-6 py-8 sm:flex-row">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-iwima.png') }}" alt="Logo IWIMA" class="h-9 w-9 object-contain">
                <div class="text-sm">
                    <div class="font-bold text-slate-800">Helpdesk IWIMA</div>
                    <div class="text-slate-500">Institut Widya Pratama Pekalongan</div>
                </div>
            </div>

            <nav class="flex items-center gap-6 text-sm font-medium text-slate-600">
                <a href="{{ route('login') }}" class="hover:text-blue-600">Masuk Pemohon</a>
                <a href="/admin/login" class="hover:text-blue-600">Panel Admin</a>
            </nav>

            <div class="text-sm text-slate-500">
                &copy; {{ date('Y') }} Institut Widya Pratama Pekalongan
            </div>
        </div>
    </footer>

</body>

</html>
