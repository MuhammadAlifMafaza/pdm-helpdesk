<?php

namespace App\Services\Laporan\Pdf;

use Illuminate\Database\Eloquent\Builder;

class LaporanPerbaikanPdf extends BasePdfExporter
{
    // Nama file saat di-download
    protected string $filename = 'laporan-perbaikan.pdf';

    // Lokasi file blade HTML (resources/views/laporan/pdf/perbaikan.blade.php)
    protected string $view = 'laporan.pdf.perbaikan';

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
            'title' => 'LAPORAN PERBAIKAN PC TEKNISI',
            'documentNumber' => 'No. 003/IWIMA/KTPI-P3SDI/0426',
            'documentCode' => '1FM-01.07.16/R0',
            'periode' => $this->periode,
            'printedBy' => auth()->user()?->name ?? 'Sistem',
            'printDate' => now()->translatedFormat('d F Y H:i'),

            // Kita kirim data mentah (Collection) agar mudah di-looping di HTML
            'records' => $this->query->get(),

            // Data tanda tangan
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
