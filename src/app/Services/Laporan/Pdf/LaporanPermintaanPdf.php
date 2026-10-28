<?php

namespace App\Services\Laporan\Pdf;

use Illuminate\Database\Eloquent\Builder;

class LaporanPermintaanPdf extends BasePdfExporter
{
    // Nama file saat di-download
    protected string $filename = 'laporan-permintaan-barang.pdf';

    // Lokasi file blade HTML (resources/views/laporan/pdf/permintaan.blade.php)
    protected string $view = 'laporan.pdf.permintaan';

    protected string $periode;

    public function __construct(Builder $query, string $periode = '-')
    {
        // Panggil constructor dari BasePdfExporter
        parent::__construct($query);
        $this->periode = $periode;
    }

    /**
     * Siapkan data yang akan dikirim ke file Blade HTML
     */
    protected function prepareData(): array
    {
        return [
            'title' => 'LAPORAN PERMINTAAN BARANG',
            'documentNumber' => 'No. 004/IWIMA/KTPI-P3SDI/0426',
            'documentCode' => '1FM-01.07.16/R0',
            'periode' => $this->periode,
            'printedBy' => auth()->user()?->name ?? 'Sistem',
            'printDate' => now()->translatedFormat('d F Y H:i'),

            // Mengirimkan data dari query yang sudah di-filter di Filament
            'records' => $this->query->get(),

            // Data tanda tangan persis seperti Word
            'signatures' => [
                [
                    'title' => 'Ka. Bidang Teknisi & Perawatan Infrastruktur',
                    'name' => 'Edi Purwanto, S.Kom',
                ],
                [
                    'title' => 'Ka. UPT Laboratorium Komputer & Bahasa',
                    'name' => 'Wachid Darmawan, M.Kom',
                ],
            ],
        ];
    }
}
