<?php

namespace App\Services\Laporan\Word\Builders;

use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\SimpleType\Jc;

class TitleBuilder
{
    public function build(
        Section $section,
        string $title,
        ?string $documentNumber = null,
    ): void {

        $section->addText(
            strtoupper($title),
            [
                'bold' => true,
                'size' => 15,
            ],
            [
                'alignment' => Jc::CENTER,
                'spaceAfter' => 120,
            ]
        );

        if ($documentNumber) {

            $section->addText(
                $documentNumber,
                [
                    'size' => 11,
                ],
                [
                    'alignment' => Jc::CENTER,
                    'spaceAfter' => 250,
                ]
            );

        }
    }
}
