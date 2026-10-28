<?php

namespace App\Services\Laporan\Word;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Section;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Services\Laporan\Word\Builders\HeaderBuilder;
use App\Services\Laporan\Word\Builders\TableBuilder;
use App\Services\Laporan\Word\Builders\SignatureBuilder;
use App\Services\Laporan\Word\Builders\TitleBuilder;
use App\Services\Laporan\Word\Builders\DocumentInfoBuilder;
use App\Services\Laporan\Word\Builders\LayoutBuilder;
use App\Services\Laporan\Word\Builders\FooterBuilder;


abstract class BaseWordExporter
{
    protected PhpWord $word;
    protected $section;
    protected HeaderBuilder $headerBuilder;
    protected TableBuilder $tableBuilder;
    protected SignatureBuilder $signatureBuilder;
    protected TitleBuilder $titleBuilder;
    protected DocumentInfoBuilder $documentInfoBuilder;
    protected LayoutBuilder $layoutBuilder;
    protected FooterBuilder $footerBuilder;

    /**
     * Nama file default
     */
    protected string $filename = 'laporan.docx';

    public function __construct()
    {
        $this->initialize();
        $this->registerStyles();
        $this->headerBuilder = new HeaderBuilder();
        $this->tableBuilder = new TableBuilder();
        $this->signatureBuilder = new SignatureBuilder();
        $this->titleBuilder = new TitleBuilder();
        $this->documentInfoBuilder = new DocumentInfoBuilder();
        $this->layoutBuilder = new LayoutBuilder();
        $this->footerBuilder = new FooterBuilder();
        $this->build();
    }

    /* Generate seluruh isi dokumen */
    abstract protected function build(): void;
    
    protected function registerStyles(): void
    {
        $this->word->addTitleStyle(
            1,
            [
                'bold' => true,
                'size' => 16,
            ]
        );

        $this->word->addTitleStyle(
            2,
            [
                'bold' => true,
                'size' => 13,
            ]
        );

        $this->word->addTableStyle(

            'MainTable',
            [
                'borderSize' => 6,
                'borderColor' => '666666',
                'cellMarginTop' => 70,
                'cellMarginBottom' => 70,
                'cellMarginLeft' => 80,
                'cellMarginRight' => 80,
            ],

            [
                'bgColor' => 'D9EAD3',
            ]
        );
    }

    /* Header Dokumen */
    protected function buildHeader(
        string $documentCode = '1FM-01.07.16/R0',
    ): void {

        $this->headerBuilder->build(
            section: $this->section,
            documentCode: $documentCode,
        );

    }

    protected function buildDocumentTitle(
        string $title,
        ?string $documentNumber = null,
    ): void {

        $this->titleBuilder->build(
            section: $this->section,
            title: $title,
            documentNumber: $documentNumber,
        );

    }

    protected function buildDocumentInfo(
        ?string $periode = null,
        ?string $printedBy = null,
    ): void {

        $this->documentInfoBuilder->build(
            section: $this->section,
            periode: $periode,
            printedBy: $printedBy,
        );

    }

    /* Tabel Data */
    protected function buildTable(
        array $headers,
        array $rows
    ): void {

        $this->tableBuilder->build(
            section: $this->section,
            headers: $headers,
            rows: $rows
        );

    }

    /* Signature (TTD) */
    protected function buildSignature(
        array $signatures,
        string $city = 'Pekalongan'
    ): void {
        $this->signatureBuilder->build(
            section: $this->section,
            signatures: $signatures,
            city: $city,
        );
    }

    /* Layout Document */
    protected function line(): void
    {
        $this->layoutBuilder->line(
            $this->section
        );
    }

    protected function space(
        int $count = 1
    ): void {

        $this->layoutBuilder->space(
            $this->section,
            $count
        );

    }
    /**
     * Footer
     */
    protected function buildFooter(): void
    {
        $footer = $this->section->addFooter();

        $this->footerBuilder->build(
            $footer
        );
    }

    /* Inisialisasi PHPWord */
    protected function initialize(): void
    {
        Settings::setZipClass(Settings::ZIPARCHIVE);

        // [PENTING] Aktifkan kembali ini agar karakter khusus tidak merusak Word
        Settings::setOutputEscapingEnabled(true);

        $this->word = new PhpWord();
        $this->word->setDefaultFontName('Arial');
        $this->word->setDefaultFontSize(11);
        $this->section = $this->word->addSection([
            'orientation' => Section::ORIENTATION_LANDSCAPE,
            'marginTop' => 600,
            'marginBottom' => 600,
            'marginLeft' => 700,
            'marginRight' => 700,
        ]);
    }

    /* Save or Download File Word Document */
    protected function save(): string
    {
        $tempFile = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . uniqid('laporan_', true)
            . '.docx';

        IOFactory::createWriter(
            $this->word,
            'Word2007'
        )->save($tempFile);

        return $tempFile;
    }

    /* Generate Download Filename */
    protected function generateFilename(): string
    {
        $timestamp = now()->format('(d-m-Y)');

        $filename = str($this->filename)
            ->beforeLast('.')
            ->slug('-');

        return "{$filename}_{$timestamp}.docx";
    }

    /* Download File */
    public function download(): BinaryFileResponse
    {
        // [PENTING] Bersihkan output buffer dari spasi/karakter tersembunyi
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        return response()->download(
            $this->save(),
            $this->generateFilename(),
        )->deleteFileAfterSend(true);
    }
}
