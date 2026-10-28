<?php

namespace App\Services\Laporan\Word;

use Illuminate\Database\Eloquent\Builder;
use App\Services\Laporan\Word\BaseWordExporter;

class LaporanKegiatanWord extends BaseWordExporter
{
    // Menggunakan custom filename agar rapi saat didownload
    protected string $filename = 'laporan_kegiatan_teknisi.docx';

    protected Builder $query;
    protected string $periode = '-';

    // Kode dan Judul Dokumen (Bisa kamu sesuaikan)
    private const DOCUMENT_CODE = '1FM-03.07.16/R0';
    private const DOCUMENT_TITLE = 'Laporan Kegiatan Teknisi';
    private const DOCUMENT_NUMBER = 'No. 005/IWIMA/KTPI-P3SDI/0426';

    // Tanda Tangan
    private const SIGNATURES = [
        [
            'title' => 'Ka. Bidang Teknisi & Perawatan Infrastruktur',
            'name' => 'Edi Purwanto, S.Kom',
        ],
        [
            'title' => 'Ka. UPT Laboratorium Komputer & Bahasa',
            'name' => 'Wachid Darmawan, M.Kom',
        ],
    ];

    public function __construct(Builder $query, string $periode = '-')
    {
        $this->query = clone $query;
        $this->periode = $periode;

        parent::__construct();
    }

    protected function build(): void
    {
        $this->buildHeader(documentCode: self::DOCUMENT_CODE);

        $this->buildDocumentTitle(
            title: self::DOCUMENT_TITLE,
            documentNumber: self::DOCUMENT_NUMBER
        );

        $this->buildDocumentInfo(
            periode: $this->periode,
            printedBy: auth()->user()?->name ?? 'Sistem'
        );

        $this->buildTable(
            headers: self::TABLE_HEADERS,
            rows: $this->prepareRows()
        );

        $this->buildSignature(self::SIGNATURES);

        $this->buildFooter();
    }

    // Header tabel yang lebih ringkas
    protected const TABLE_HEADERS = [
        'No',
        'Hari / Tanggal',
        'Nama Teknisi',
        'Deskripsi Kegiatan',
    ];

    protected function prepareRows(): array
    {
        return $this->query
            ->get()
            ->map(fn($item, $index) => [
                $index + 1,                                // 0. No

                // Format Hari & Tanggal memakai Carbon Locale Indonesia
                $item->tanggal_kegiatan
                ? $item->tanggal_kegiatan->locale('id')->translatedFormat('l, d F Y')
                : '-',                                 // 1. Tanggal

                $item->nama_teknisi,                       // 2. Teknisi
                $item->deskripsi ?? '-',                   // 3. Deskripsi
            ])
            ->toArray();
    }
}