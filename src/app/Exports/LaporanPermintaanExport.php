<?php

namespace App\Exports;

// Pastikan mengimpor Model yang benar jika dibutuhkan untuk PhpDoc/Referensi
class LaporanPermintaanExport extends BaseLaporanExport
{
    protected function reportTitle(): string
    {
        return 'LAPORAN PERMINTAAN BARANG';
    }

    protected function documentNumber(): string
    {
        return 'No. 004/IWIMA/KTPI-P3SDI/0426';
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Pengajuan',
            'Pemohon',
            'Nama Barang',
            'Jumlah',
            'Status',
            'Hasil Persetujuan',
            'Waktu Mulai',
            'Waktu Selesai',
            'Durasi Pengerjaan',
        ];
    }

    public function map($laporan): array
    {
        return [
            $this->nextRowNumber(),
            $laporan->kode_pengajuan ?? '-',
            $laporan->nama_pemohon ?? '-',
            $laporan->pengajuan?->nama_barang ?? $laporan->nama_barang ?? '-',
            $laporan->pengajuan?->jumlah ?? '-',
            $laporan->pengajuan?->status_label ?? $laporan->status_label ?? '-',
            $laporan->outcome_label ?? $laporan->outcome ?? '-',
            $laporan->pengajuan?->waktu_mulai?->format('d-m-Y H:i') ?? '-',
            $laporan->pengajuan?->waktu_selesai?->format('d-m-Y H:i') ?? '-',
            $laporan->pengajuan?->durasi_pengerjaan ?? '-',
        ];
    }
}
