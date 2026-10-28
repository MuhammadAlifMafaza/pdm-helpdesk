<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot petugas per kegiatan. Sebelumnya petugas disimpan di kolom teks
     * `nama_petugas` yang tidak pernah terisi ($fillable tidak mencakupnya)
     * dan merusak data saat edit (nilai select multiple ditulis sebagai
     * string tunggal). Semua data historis kolom tersebut NULL, sehingga
     * tidak diperlukan backfill ke pivot.
     *
     * Kolom FK memakai `integer()` biasa (bukan `foreignId()` dengan DB
     * constraint) karena PK legacy hasil import dump bertipe `int` signed,
     * sedangkan `users.id` pada fresh-install Laravel = `bigint unsigned`.
     * Tipe statis tidak bisa cocok untuk keduanya, jadi integritas relasi
     * dijaga lewat many-to-many Eloquent, plus unique index anti-duplikat.
     */
    public function up(): void
    {
        Schema::create('kegiatan_petugas', function (Blueprint $table) {
            $table->id();

            $table->integer('kegiatan_id');
            $table->integer('petugas_id');

            $table->timestamps();

            $table->unique(['kegiatan_id', 'petugas_id']);
            $table->index('kegiatan_id');
            $table->index('petugas_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan_petugas');
    }
};
