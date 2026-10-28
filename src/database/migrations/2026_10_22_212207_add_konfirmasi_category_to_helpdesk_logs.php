<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel log yang menyimpan kategori_log sebagai ENUM MySQL.
     */
    private const TABLES = [
        'log_data_tiket_perbaikan',
        'log_data_pengajuan_barang',
    ];

    /**
     * Nilai ENUM baru untuk log konfirmasi penyelesaian.
     */
    private const CATEGORY = 'Konfirmasi';

    /**
     * Menambahkan nilai 'Konfirmasi' ke ENUM kategori_log.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach (self::TABLES as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            $definition = $this->enumDefinition($tableName);

            if ($definition === null || in_array(self::CATEGORY, $definition, true)) {
                continue;
            }

            $definition[] = self::CATEGORY;

            $this->modifyEnum($tableName, $definition);
        }
    }

    /**
     * Mengembalikan ENUM kategori_log ke definisi sebelumnya.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach (self::TABLES as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            $definition = $this->enumDefinition($tableName);

            if ($definition === null || ! in_array(self::CATEGORY, $definition, true)) {
                continue;
            }

            $definition = array_values(array_diff($definition, [self::CATEGORY]));

            $this->modifyEnum($tableName, $definition);
        }
    }

    /**
     * Menjalankan ALTER TABLE MODIFY untuk ENUM kategori_log.
     *
     * @param  array<int, string>  $definition
     */
    private function modifyEnum(string $tableName, array $definition): void
    {
        $enumValues = implode(
            ', ',
            array_map(
                fn (string $value): string => "'".$value."'",
                $definition
            )
        );

        DB::statement(
            "ALTER TABLE `{$tableName}` MODIFY `kategori_log` ENUM({$enumValues}) NOT NULL"
        );
    }

    /**
     * Membaca definisi ENUM kategori_log dari information_schema.
     *
     * @return array<int, string>|null
     */
    private function enumDefinition(string $tableName): ?array
    {
        $column = collect(DB::select(
            "SHOW COLUMNS FROM `{$tableName}` WHERE Field = 'kategori_log'"
        ))->first();

        if (! isset($column->Type) || ! str_starts_with($column->Type, 'enum(')) {
            return null;
        }

        return array_map(
            fn (string $value): string => trim($value, "'"),
            explode(',', substr($column->Type, 5, -1))
        );
    }
};
