<?php

namespace App\Services\Laporan\Word;

use Illuminate\Database\Eloquent\Builder;

class LaporanPermintaanWord extends BaseWordExporter
{
    /* Query dari Filament Filter */
    protected Builder $query;

    /* Periode Laporan */
    protected string $periode = '-';

    protected string $filename = 'laporan-permintaan_barang.docx';

    // Document Code (Bisa disesuaikan jika format suratnya berbeda)
    private const DOCUMENT_CODE = '1FM-02.07.16/R0';

    // Title Document
    private const DOCUMENT_TITLE = 'Laporan Permintaan Barang';

    // Document Number
    private const DOCUMENT_NUMBER = 'No. 004/IWIMA/KTPI-P3SDI/0426';

    // Data Signature (Tanda Tangan - Disamakan dengan Perbaikan)
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

    /* Constructor */
    public function __construct(
        Builder $query,
        string $periode = '-'
    ) {
        $this->query = clone $query;
        $this->periode = $periode;

        parent::__construct();
    }

    /* Build document content */
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

    /* Header Table untuk Permintaan Barang */
    protected const TABLE_HEADERS = [
        'No',
        'Kode Pengajuan',
        'Pemohon',
        'Nama Barang',
        'Jumlah',
        'Status',
        'Hasil',
        'Waktu Mulai',
        'Waktu Selesai',
        'Durasi Proses',
    ];

    /* Prepare Data Tabel */
    protected function prepareRows(): array
    {
        return $this->query
            ->get()
            ->map(
                fn ($item, $index) => [
                    $index + 1,                                        // 0. No
                    $item->kode_pengajuan,                             // 1. Kode
                    $item->nama_pemohon,                               // 2. Pemohon
                    $item->nama_barang,                                // 3. Nama Barang
                    $item->pengajuan?->jumlah ?? '-',                  // 4. Jumlah
                    $item->pengajuan?->status_label ?? $item->status_label, // 5. Status
                    $item->outcome_label,                              // 6. Hasil (Completed/Rejected)
                    $item->pengajuan?->waktu_mulai?->format('Y-m-d H:i') ?? '-', // 7. Mulai
                    $item->pengajuan?->waktu_selesai?->format('Y-m-d H:i') ?? '-', // 8. Selesai
                    $item->pengajuan?->durasi_pengerjaan ?? $item->durasi, // 9. Durasi
                ]
            )
            ->toArray();
    }
}
