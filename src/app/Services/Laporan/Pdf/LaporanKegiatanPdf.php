<?php

namespace App\Services\Laporan\Pdf;

use Illuminate\Database\Eloquent\Builder;

class LaporanKegiatanPdf extends BasePdfExporter
{
    // Nama file saat di-download
    protected string $filename = 'laporan_kegiatan_teknisi.pdf';

    // Lokasi file blade HTML (resources/views/laporan/pdf/kegiatan.blade.php)
    protected string $view = 'laporan.pdf.kegiatan';

    protected string $periode;

    public function __construct(Builder $query, string $periode = '-')
    {
        // Memanggil constructor dari BasePdfExporter
        parent::__construct($query);
        $this->periode = $periode;
    }

    /**
     * Menyiapkan data yang akan dikirim ke file Blade HTML
     */
    protected function prepareData(): array
    {
        return [
            'title' => 'LAPORAN KEGIATAN TEKNISI',
            'documentNumber' => 'No. 005/IWIMA/KTPI-P3SDI/0426',
            'documentCode' => '1FM-01.07.16/R0',
            'periode' => $this->periode,
            'printedBy' => auth()->user()?->name ?? 'Sistem',
            'printDate' => now()->translatedFormat('d F Y H:i'),

            // Mengirimkan data dari query yang difilter di tabel
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
