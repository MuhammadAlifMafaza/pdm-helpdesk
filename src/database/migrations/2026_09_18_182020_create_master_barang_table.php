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
        Schema::create('master_barang', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kategori_barang_id')
                ->constrained('master_kategori_barang')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->string('nama_barang', 255);
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['kategori_barang_id', 'is_active']);
            $table->index('nama_barang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_barang');
    }
};
