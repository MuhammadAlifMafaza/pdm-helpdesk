<?php

namespace App\Services\Laporan\Word\Builders;

use PhpOffice\PhpWord\Element\Section;

class LayoutBuilder
{
    public function line(
        Section $section
    ): void {
        $section->addText(
            '',
            [],
            [
                'borderBottomSize' => 12,
                'borderBottomColor' => '000000',
                'spaceAfter' => 150,
            ]
        );
    }

    public function space(
        Section $section,
        int $count = 1
    ): void {
        $section->addTextBreak($count);
    }
}
