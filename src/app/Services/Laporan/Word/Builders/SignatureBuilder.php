<?php

namespace App\Services\Laporan\Word\Builders;

use Carbon\Carbon;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\SimpleType\Jc;

class SignatureBuilder
{
    public function build(
        Section $section,
        array $signatures,
        string $city = 'Pekalongan',
        Carbon|string|null $date = null,
    ): void {
        // [PENTING] 1. Cegah error jika array signatures kosong
        if (empty($signatures)) {
            return;
        }

        $date = $date instanceof Carbon
            ? $date
            : Carbon::parse($date ?? now());

        $section->addTextBreak(2);

        /*
        |--------------------------------------------------------------------------
        | Tanggal
        |--------------------------------------------------------------------------
        */

        $section->addText(
            "{$city}, " . $date->locale('id')->translatedFormat('d F Y'),
            [
                'size' => 11,
            ],
            [
                'alignment' => Jc::END,
                'spaceAfter' => 300,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Format 1 Tanda Tangan
        |--------------------------------------------------------------------------
        */
        if (count($signatures) === 1) {
            $signature = $signatures[0];

            // Menggunakan operator ?? untuk mencegah error jika key tidak ada
            $section->addText(
                $signature['title'] ?? 'Jabatan Tidak Diketahui',
                [
                    'bold' => true,
                    'size' => 11,
                ],
                [
                    'alignment' => Jc::CENTER,
                ]
            );

            $section->addTextBreak(5);

            $section->addText(
                $signature['name'] ?? 'Nama Tidak Diketahui',
                [
                    'bold' => true,
                    'underline' => 'single',
                    'size' => 11,
                ],
                [
                    'alignment' => Jc::CENTER,
                ]
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Format 2 Tanda Tangan (atau lebih, kita ambil 2 pertama)
        |--------------------------------------------------------------------------
        */

        $textRun = $section->addTextRun();

        // Jabatan
        $textRun->addText(
            $signatures[0]['title'] ?? 'Jabatan Kiri',
            [
                'bold' => true,
                'size' => 11,
            ]
        );

        $textRun->addText(str_repeat(' ', 40));

        $textRun->addText(
            $signatures[1]['title'] ?? 'Jabatan Kanan',
            [
                'bold' => true,
                'size' => 11,
            ]
        );

        $section->addTextBreak(5);

        // Nama
        $textRun = $section->addTextRun();

        $textRun->addText(
            $signatures[0]['name'] ?? 'Nama Kiri',
            [
                'bold' => true,
                'underline' => 'single',
                'size' => 11,
            ]
        );

        $textRun->addText(str_repeat(' ', 45));

        $textRun->addText(
            $signatures[1]['name'] ?? 'Nama Kanan',
            [
                'bold' => true,
                'underline' => 'single',
                'size' => 11,
            ]
        );
    }
}
