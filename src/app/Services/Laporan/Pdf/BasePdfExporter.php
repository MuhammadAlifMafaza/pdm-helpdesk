<?php

namespace App\Services\Laporan\Pdf;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

abstract class BasePdfExporter
{
    protected Builder $query;

    protected string $filename = 'laporan.pdf';

    protected string $view;

    public function __construct(Builder $query)
    {
        $this->query = clone $query;
    }

    abstract protected function prepareData(): array;

    protected function generateFilename(): string
    {
        $timestamp = now()->format('d-m-Y_H-i');
        $name = str($this->filename)->beforeLast('.')->slug('-');

        return "{$name}_{$timestamp}.pdf";
    }

    protected function generatePdf()
    {
        $data = $this->prepareData();

        return Pdf::loadView($this->view, $data)->setPaper('A4', 'landscape');
    }

    /* |--------------------------------------------------------------------------
    | Fitur 1: Unduh File PDF
    |--------------------------------------------------------------------------
    */
    public function download(): BinaryFileResponse
    {
        // Bersihkan memori buffer
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        $tempFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.$this->generateFilename();

        // Simpan PDF ke folder temporary
        $this->generatePdf()->save($tempFile);

        // Download dan hapus setelah terkirim
        return response()->download(
            $tempFile,
            $this->generateFilename()
        )->deleteFileAfterSend(true);
    }

    /* |--------------------------------------------------------------------------
    | Fitur 2: Halaman Cetak di Browser
    |--------------------------------------------------------------------------
    | Data disiapkan oleh prepareData() yang sama dengan PDF, lalu dirender
    | di halaman cetak (view laporan.print.*) oleh PrintLaporanController.
    */
    public function preparePrintData(): array
    {
        return $this->prepareData();
    }

    /**
     * Nama view cetak yang setara dengan view PDF
     * ('laporan.pdf.kegiatan' → 'laporan.print.kegiatan').
     */
    public function printView(): string
    {
        return str_replace('.pdf.', '.print.', $this->view);
    }
}
