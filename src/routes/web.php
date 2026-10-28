<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Laporan\PrintLaporanController;
use App\Models\Modules\Pengajuan\Models\PengajuanBarang;
use App\Models\Modules\Perbaikan\Models\TiketPerbaikan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|----------------------------------------------------------------------
| Halaman cetak laporan (dialog print browser, bukan export file)
|----------------------------------------------------------------------
| Payload disimpan ke session oleh LaporanExportService::print() saat
| tombol "Print Preview" ditekan di halaman List laporan.
*/
Route::get('/laporan/cetak', PrintLaporanController::class)
    ->middleware(['auth', 'staff'])
    ->name('laporan.cetak');

/*
|----------------------------------------------------------------------
| Landing (tamu) & pengarah peran (user login)
|----------------------------------------------------------------------
| Tamu melihat halaman landing (welcome.blade.php) berisi profil layanan,
| statistik, dan tombol masuk. User yang sudah login tetap diarahkan ke
| halaman sesuai perannya: pemohon ke /pemohon, staff ke /admin; akun
| tanpa peran menerima 403.
*/
Route::get('/', function () {
    if (! auth()->check()) {
        /*
        | Statistik hanya berisi angka agregat publik (tanpa PII). Jika tabel
        | belum tersedia — misalnya pada koneksi database uji — tampilkan nol
        | tanpa meng-cache hasilnya agar tidak menimbun nilai gagal.
        */
        $tabelSiap = Schema::hasTable('tiket_perbaikan')
            && Schema::hasTable('pengajuan_barang')
            && Schema::hasTable('log_harian_teknisi');

        $statistik = $tabelSiap
            ? Cache::remember('landing.statistik', now()->addHour(), fn () => [
                'tiket' => TiketPerbaikan::count(),
                'tiket_selesai' => TiketPerbaikan::where('status', 'Close')->count(),
                'pengajuan' => PengajuanBarang::count(),
                'kegiatan' => DB::table('log_harian_teknisi')->count(),
            ])
            : ['tiket' => 0, 'tiket_selesai' => 0, 'pengajuan' => 0, 'kegiatan' => 0];

        return view('welcome', ['statistik' => $statistik]);
    }

    $homeUrl = auth()->user()->homeUrl();

    abort_if($homeUrl === null, 403, 'Akun Anda tidak memiliki akses.');

    return redirect()->to($homeUrl);
});
