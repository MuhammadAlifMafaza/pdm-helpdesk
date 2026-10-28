<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Baseline: tabel `master_ruangan` historisnya dibuat lewat import SQL dump,
     * bukan migrasi. Guard `hasTable` membuat migrasi ini aman dijalankan
     * pada instalasi yang sudah memiliki tabel tersebut.
     */
    public function up(): void
    {
        if (Schema::hasTable('master_ruangan')) {
            return;
        }

        Schema::create('master_ruangan', function (Blueprint $table) {
            $table->increments('id');

            $table->string('nama_ruangan', 100);
            $table->string('nama_gedung', 100);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_ruangan');
    }
};
