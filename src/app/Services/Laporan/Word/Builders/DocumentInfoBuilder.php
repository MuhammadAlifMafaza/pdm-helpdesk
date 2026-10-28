<?php

namespace App\Services\Laporan\Word\Builders;

use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\SimpleType\Jc;

class DocumentInfoBuilder
{
    public function build(
        Section $section,
        ?string $periode = null,
        ?string $printedBy = null,
    ): void {

        $table = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 30,
        ]);

        $table->addRow();

        $table->addCell(2500)->addText(
            'Periode',
            ['bold' => true]
        );

        $table->addCell(200)->addText(':');

        $table->addCell(6000)->addText(
            $periode ?? '-'
        );

        $table->addRow();

        $table->addCell()->addText(
            'Tanggal Cetak',
            ['bold' => true]
        );

        $table->addCell()->addText(':');

        $table->addCell()->addText(
            now()->translatedFormat('d F Y H:i')
        );

        $table->addRow();

        $table->addCell()->addText(
            'Dicetak Oleh',
            ['bold' => true]
        );

        $table->addCell()->addText(':');

        $table->addCell()->addText(
            $printedBy ?? '-'
        );

        $section->addTextBreak();
    }
}
