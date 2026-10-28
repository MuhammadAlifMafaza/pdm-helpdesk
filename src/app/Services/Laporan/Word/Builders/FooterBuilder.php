<?php

namespace App\Services\Laporan\Word\Builders;

use PhpOffice\PhpWord\Element\Footer;

class FooterBuilder
{
    public function build(
        Footer $footer
    ): void {

        /* Garis Atas Footer */
        $table = $footer->addTable([
            'borderBottomSize' => 0,
            'borderTopSize' => 8,
            'borderTopColor' => '777777',
        ]);

        $table->addRow();

        $table->addCell(15000)->addText('');

        /* Nomor Halaman */
        $footer->addPreserveText(
            'Halaman {PAGE} dari {NUMPAGES}',
            [
                'size' => 9,
                'italic' => true,
            ],
            [
                'alignment' => 'right',
            ]
        );

    }
}
