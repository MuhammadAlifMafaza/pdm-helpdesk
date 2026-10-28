<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Baseline view `view_laporan_kegiatan` yang historisnya dibuat lewat SQL dump
     * dengan DEFINER=root@localhost (SQL SECURITY DEFINER) — merepotkan saat
     * instalasi berjalan sebagai user DB lain. Dibuat ulang tanpa DEFINER eksplisit
     * sehingga mengikuti user yang menjalankan migrasi.
     *
     * Hanya dijalankan di MySQL; view ini merujuk kolom `users.unit_bidang`
     * yang tidak ada pada skema sqlite bawaan testing.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE VIEW `view_laporan_kegiatan` AS
            SELECT
                `l`.`id` AS `id_log`,
                `u`.`name` AS `nama_teknisi`,
                `u`.`unit_bidang` AS `unit_bidang`,
                `l`.`tanggal` AS `tanggal`,
                `l`.`deskripsi_kegiatan` AS `deskripsi_kegiatan`,
                `l`.`created_at` AS `waktu_dibuat`
            FROM `log_harian_teknisi` `l`
            INNER JOIN `users` `u` ON `l`.`teknisi_id` = `u`.`id`
            SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP VIEW IF EXISTS `view_laporan_kegiatan`');
    }
};
