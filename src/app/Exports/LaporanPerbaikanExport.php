<?php

namespace App\Exports;

class LaporanPerbaikanExport extends BaseLaporanExport
{
    protected function reportTitle(): string
    {
        return 'LAPORAN PERBAIKAN PC TEKNISI';
    }

    protected function documentNumber(): string
    {
        return 'No. 003/IWIMA/KTPI-P3SDI/0426';
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Tiket',
            'Pemohon',
            'Lokasi',
            'Tgl Masuk',
            'Tgl Selesai',
            'Keterangan',
            'Status',
        ];
    }

    public function map($laporan): array
    {
        return [
            $this->nextRowNumber(),
            $laporan->tiket?->kode_tiket ?? $laporan->kode_tiket ?? '-',
            $laporan->nama_pemohon ?? '-',
            $laporan->lokasi ?? '-',
            $laporan->tiket?->created_at?->format('d/m/Y') ?? $laporan->waktu_mulai?->format('d/m/Y') ?? '-',
            $laporan->tiket?->waktu_selesai?->format('d/m/Y') ?? $laporan->waktu_selesai?->format('d/m/Y') ?? '-',
            $laporan->tiket?->keluhan ?? $laporan->service_category ?? '-',
            $laporan->tiket?->status_label ?? $laporan->status_label ?? '-',
        ];
    }
}
