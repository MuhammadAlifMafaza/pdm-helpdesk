<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom teks `nama_petugas` digantikan pivot `kegiatan_petugas`.
     * Seluruh data historis terverifikasi NULL (0 dari 10 baris terisi),
     * sehingga drop aman dan tidak kehilangan informasi.
     */
    public function up(): void
    {
        if (Schema::hasColumn('log_harian_teknisi', 'nama_petugas')) {
            Schema::table('log_harian_teknisi', function (Blueprint $table) {
                $table->dropColumn('nama_petugas');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('log_harian_teknisi', 'nama_petugas')) {
            Schema::table('log_harian_teknisi', function (Blueprint $table) {
                $table->text('nama_petugas')->nullable()->after('teknisi_id');
            });
        }
    }
};
