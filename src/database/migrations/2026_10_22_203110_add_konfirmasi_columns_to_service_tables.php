<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom konfirmasi penyelesaian pekerjaan.
     *
     * Nilai konfirmasi_status:
     * - null           : belum ada permintaan konfirmasi (atau sudah diproses)
     * - menunggu       : staff menandai selesai, menunggu konfirmasi pemohon
     * - disetujui      : pemohon mengonfirmasi pekerjaan selesai
     * - ditolak_pemohon: pemohon menolak konfirmasi dan meminta revisi
     *
     * Kolom status asli (Open / In Progress / Close) tidak diubah agar
     * seluruh laporan, chart, dan query status yang ada tetap berlaku.
     */
    private const TABLES = ['tiket_perbaikan', 'pengajuan_barang'];

    private const COLUMNS = ['konfirmasi_status', 'konfirmasi_at', 'konfirmasi_catatan'];

    public function up(): void
    {
        foreach (self::TABLES as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            $missing = array_filter(
                self::COLUMNS,
                fn (string $column): bool => ! Schema::hasColumn($tableName, $column)
            );

            if ($missing === []) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($missing): void {
                if (in_array('konfirmasi_status', $missing, true)) {
                    $table->string('konfirmasi_status', 30)->nullable()->after('status');
                }

                if (in_array('konfirmasi_at', $missing, true)) {
                    $table->timestamp('konfirmasi_at')->nullable()->after('konfirmasi_status');
                }

                if (in_array('konfirmasi_catatan', $missing, true)) {
                    $table->text('konfirmasi_catatan')->nullable()->after('konfirmasi_at');
                }
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            $existing = array_filter(
                self::COLUMNS,
                fn (string $column): bool => Schema::hasColumn($tableName, $column)
            );

            if ($existing === []) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($existing): void {
                $table->dropColumn($existing);
            });
        }
    }
};
