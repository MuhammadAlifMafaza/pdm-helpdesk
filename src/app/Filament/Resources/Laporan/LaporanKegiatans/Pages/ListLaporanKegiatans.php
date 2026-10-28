<?php

namespace App\Filament\Resources\Laporan\LaporanKegiatans\Pages;

use App\Exports\LaporanKegiatanExport;
use App\Filament\Resources\Laporan\LaporanKegiatans\LaporanKegiatanResource;
use App\Services\Laporan\LaporanExportService;
use App\Services\Laporan\LaporanPeriodFormatter;
use App\Services\Laporan\Pdf\LaporanKegiatanPdf;
use App\Services\Laporan\Word\LaporanKegiatanWord;
// Import Class Exporter (Kita akan membuatnya setelah ini)
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ListRecords;

class ListLaporanKegiatans extends ListRecords
{
    protected static string $resource = LaporanKegiatanResource::class;

    protected function getSelectedReportPeriod(): string
    {
        return LaporanPeriodFormatter::format($this->getTableFilterState('periode') ?? []);
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                // Tombol Export Excel
                Action::make('excel')
                    ->label('Excel')
                    ->icon('heroicon-o-document-chart-bar')
                    ->color('success')
                    ->action(function () {
                        return app(LaporanExportService::class)->exportExcel(
                            $this->getFilteredTableQuery(),
                            LaporanKegiatanExport::class,
                            'Laporan-Kegiatan-Teknisi-'.now()->format('(d-m-Y)').'.xlsx',
                            $this->getSelectedReportPeriod()
                        );
                    }),

                // Tombol Export Word
                Action::make('word')
                    ->disabled()
                    ->label('Word (coming soon)')
                    ->icon('heroicon-o-document-text')
                    // ->color('info')
                    ->color('gray')
                    ->action(function () {
                        return app(LaporanExportService::class)->exportWord(
                            query: $this->getFilteredTableQuery(),
                            wordExporter: LaporanKegiatanWord::class,
                            periode: $this->getSelectedReportPeriod(),
                        );
                    }),

                // Tombol Export PDF
                Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('danger')
                    ->action(function () {
                        return app(LaporanExportService::class)->exportPDF(
                            query: $this->getFilteredTableQuery(),
                            exportClass: LaporanKegiatanPdf::class,
                            periode: $this->getSelectedReportPeriod(),
                        );
                    }),

                // Tombol Print Preview
                Action::make('print')
                    ->label('Print Preview')
                    ->icon('heroicon-o-printer')
                    ->color('warning')
                    ->action(function () {
                        $url = app(LaporanExportService::class)->print(
                            query: $this->getFilteredTableQuery(),
                            exportClass: LaporanKegiatanPdf::class,
                            periode: $this->getSelectedReportPeriod(),
                        );

                        return redirect($url);
                    }),
            ])
                ->label('Export Dokumen')
                ->icon('heroicon-o-arrow-down-tray')
                ->button(),
        ];
    }
}
