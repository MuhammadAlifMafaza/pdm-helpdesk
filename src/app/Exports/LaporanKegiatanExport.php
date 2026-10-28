<?php

namespace App\Exports;

class LaporanKegiatanExport extends BaseLaporanExport
{
    protected function reportTitle(): string
    {
        return 'LAPORAN KEGIATAN TEKNISI';
    }

    protected function documentNumber(): string
    {
        return 'No. 005/IWIMA/KTPI-P3SDI/0426';
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Petugas',
            'Lokasi',
            'Deskripsi Kegiatan',
        ];
    }

    public function map($laporan): array
    {
        return [
            $this->nextRowNumber(),
            $laporan->tanggal_kegiatan?->format('d/m/Y') ?? '-',
            $laporan->nama_teknisi ?? '-',
            $laporan->lokasi ?? '-',
            $laporan->deskripsi ?? '-',
        ];
    }
}
