<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('log_data_tiket_perbaikan', function (Blueprint $table) {

            $table->index(
                [
                    'tiket_id',
                    'kategori_log',
                    'data_baru',
                    'created_at',
                ],
                'log_tiket_status_lookup'
            );

            $table->index(
                [
                    'user_id',
                    'kategori_log',
                    'data_baru',
                    'created_at',
                ],
                'log_teknisi_status_lookup'
            );

            $table->index(
                [
                    'user_id',
                    'created_at',
                ],
                'log_teknisi_activity_lookup'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_perbaikan', function (Blueprint $table) {
            //
        });
    }
};
