<?php

namespace App\Services\Laporan;

use App\Services\Laporan\Word\BaseWordExporter;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanExportService
{
    /* Export Excel */
    public function exportExcel(
        Builder $query,
        string $export,
        string $filename,
        string $periode = '-'
    ): BinaryFileResponse {

        return Excel::download(
            new $export($query, $periode),
            $filename
        );

    }

    /* Export Word */
    public function exportWord(
        Builder $query,
        string $wordExporter,
        string $periode = '-'
    ): BinaryFileResponse {

        /** @var BaseWordExporter $export */
        $export = new $wordExporter(
            $query,
            $periode
        );

        return $export->download();
    }

    /* Export PDF (Download File) */
    public function exportPDF(
        Builder $query,
        string $exportClass,
        string $periode = '-'
    ): BinaryFileResponse {  // <- Pastikan ini BinaryFileResponse
        $exporter = new $exportClass($query, $periode);

        return $exporter->download();
    }

    /* Print Preview → halaman cetak di browser (tanpa export file) */
    public function print(
        Builder $query,
        string $exportClass,
        string $periode = '-'
    ): string {
        $exporter = new $exportClass($query, $periode);
        $view = $exporter->printView();
        $data = $exporter->preparePrintData();

        /*
        |----------------------------------------------------------------------
        | Render sebelum menyimpan ke session agar accessor dan relasi Eloquent
        | dievaluasi sebelum payload model diserialisasi menjadi array.
        */
        session()->put('laporan.print', [
            'html' => view($view, $data)->render(),
        ]);

        // URL relatif agar tidak tergantung nilai APP_URL.
        return route('laporan.cetak', absolute: false);
    }
}
