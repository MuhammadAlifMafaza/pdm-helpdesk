<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Baseline: tabel `log_harian_teknisi` historisnya dibuat lewat import SQL dump,
     * bukan migrasi. Guard `hasTable` membuat migrasi ini aman dijalankan
     * pada instalasi yang sudah memiliki tabel tersebut.
     *
     * Skema yang dibuat di sini adalah skema final (tanpa kolom `nama_petugas`,
     * karena petugas kini disimpan di pivot table `kegiatan_petugas`).
     */
    public function up(): void
    {
        if (Schema::hasTable('log_harian_teknisi')) {
            return;
        }

        Schema::create('log_harian_teknisi', function (Blueprint $table) {
            $table->increments('id');

            $table->foreignId('teknisi_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('ruangan_id')
                ->nullable()
                ->constrained('master_ruangan')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->date('tanggal');
            $table->text('deskripsi_kegiatan');

            $table->timestamps();
            $table->softDeletes();

            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_harian_teknisi');
    }
};
