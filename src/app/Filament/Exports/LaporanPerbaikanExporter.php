<?php

namespace App\Filament\Exports;

use App\Models\Modules\Laporan\Models\LaporanPerbaikan;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class LaporanPerbaikanExporter extends Exporter
{
    protected static ?string $model = LaporanPerbaikan::class;

    public static function getColumns(): array
    {
        return [

            ExportColumn::make('kode_tiket')
                ->label('Kode Tiket'),

            ExportColumn::make('nama_pemohon')
                ->label('Pemohon'),

            ExportColumn::make('lokasi')
                ->label('Lokasi'),

            ExportColumn::make('kepemilikan')
                ->label('Kepemilikan'),

            ExportColumn::make('nama_teknisi')
                ->label('Teknisi'),

            ExportColumn::make('status_label')
                ->label('Status'),

            ExportColumn::make('service_category')
                ->label('Kategori'),

            ExportColumn::make('waktu_mulai')
                ->label('Mulai'),

            ExportColumn::make('waktu_selesai')
                ->label('Selesai'),

            ExportColumn::make('durasi')
                ->label('Durasi'),

        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your laporan perbaikan export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
