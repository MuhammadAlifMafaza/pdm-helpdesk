<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pengajuan_barang', function (Blueprint $table) {
            $table->foreignId('barang_id')
                ->nullable()
                ->after('user_id')
                ->constrained('master_barang')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->text('deskripsi_barang')
                ->nullable()
                ->after('nama_barang');

            $table->index('barang_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_barang', function (Blueprint $table) {
            //
        });
    }
};
