<?php

namespace App\Services\Laporan\Word\Builders;

use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\SimpleType\Jc;

class HeaderBuilder
{
    public function build(
        Section $section,
        string $documentCode = '1FM-01.07.16/R0'
    ): void {

        /* Word Header */
        $header = $section->addHeader();

        /* Table Layout */
        $table = $header->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
            'alignment' => Jc::CENTER,
        ]);

        $table->addRow();

        /* Logo */
        $logo = $table->addCell(1800);

        $logo->addImage(
            public_path('images/logo-iwima.png'),
            [
                'width' => 58,
                'height' => 58,
                'alignment' => Jc::CENTER,
            ]
        );

        /* Identity */
        $identity = $table->addCell(9800);

        $identity->addText(
            'INSTITUT WIDYA PRATAMA PEKALONGAN',
            [
                'bold' => true,
                'size' => 15,
            ],
            [
                'alignment' => Jc::CENTER,
            ]
        );

        $identity->addText(
            'KABID TEKNIS DAN PERAWATAN INFRASTRUKTUR',
            [
                'bold' => true,
                'size' => 11,
            ],
            [
                'alignment' => Jc::CENTER,
            ]
        );

        $identity->addText(
            'Jl. Patriot No.25 Pekalongan • (0285) 427817 • Fax (0285) 427815',
            [
                'size' => 9,
            ],
            [
                'alignment' => Jc::CENTER,
            ]
        );

        /* Document Code */
        $code = $table->addCell(2200);

        $code->addText(
            $documentCode,
            [
                'bold' => true,
                'size' => 9,
            ],
            [
                'alignment' => Jc::END,
            ]
        );

        /* Line */
        $header->addText(
            '',
            [],
            [
                'borderBottomSize' => 12,
                'borderBottomColor' => '000000',
                'spaceAfter' => 120,
            ]
        );

        /* Space */
        $section->addTextBreak();
    }
}
