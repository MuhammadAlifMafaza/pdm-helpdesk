<?php

namespace App\Services\Laporan\Word;

use Illuminate\Database\Eloquent\Builder;

class LaporanPerbaikanWord extends BaseWordExporter
{
    /* Query dari Filament Filter */
    protected Builder $query;

    /* Periode Laporan */
    protected string $periode = '-';

    protected string $filename = 'laporan_perbaikan.docx';

    // Document Code (Kode Dokumen)
    private const DOCUMENT_CODE = '1FM-01.07.16/R0';

    // Title Document (Judul Dokumen)
    private const DOCUMENT_TITLE = 'Laporan Perbaikan PC Teknisi';

    // Document Number (Nomer Dokumen)
    private const DOCUMENT_NUMBER = 'No. 003/IWIMA/KTPI-P3SDI/0426';

    // Data Signature (Tanda Tangan)
    private const SIGNATURES = [
        [
            'title' => 'Ka. Bidang Teknisi Perawatan dan Infrastruktur',
            'name' => 'Edi Purwanto, S.Kom',
        ],

        [
            'title' => 'Ka. UPT Laboratorium Komputer dan Bahasa',
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
        $this->buildHeader(
            documentCode: self::DOCUMENT_CODE
        );

        $this->buildDocumentTitle(
            title: self::DOCUMENT_TITLE,
            documentNumber: self::DOCUMENT_NUMBER
        );

        $this->buildDocumentInfo(
            periode: $this->periode,
            printedBy: auth()->user()?->name
        );

        $this->buildTable(
            headers: self::TABLE_HEADERS,
            rows: $this->prepareRows()
        );

        $this->buildSignature(self::SIGNATURES);

        $this->buildFooter();
    }

    /* Header Table */
    protected const TABLE_HEADERS = [
        'No',
        'Kode Tiket',
        'Pemohon',
        'Lokasi',
        'Kepemilikan',
        'Teknisi',
        'Status',
        'Kategori',
        'Waktu Mulai',
        'Waktu Selesai',
        'Durasi Pengerjaan',
    ];

    /* Prepare Data Tabel */
    protected function prepareRows(): array
    {
        return $this->query
            ->get()
            ->map(
                fn ($item, $index) => [

                    $index + 1,

                    $item->tiket?->kode_tiket ?? $item->kode_tiket,

                    $item->nama_pemohon,

                    $item->lokasi,

                    $item->kepemilikan,

                    $item->nama_teknisi ?: '-',

                    $item->status_label,

                    $item->service_category,

                    $item->tiket?->waktu_mulai ?? $item->waktu_mulai,

                    $item->tiket?->waktu_selesai ?? $item->waktu_selesai,

                    $item->tiket?->durasi_pengerjaan ?? $item->durasi,

                ]
            )
            ->toArray();
    }
}
