<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

abstract class BaseLaporanExport implements FromQuery, ShouldAutoSize, WithCustomStartCell, WithEvents, WithHeadings, WithMapping, WithStyles
{
    protected Builder $query;

    protected int $rowNumber = 0;

    protected string $periode;

    public function __construct(Builder $query, string $periode = '-')
    {
        $this->query = $query;
        $this->periode = $periode;
    }

    public function query(): Builder
    {
        return $this->query;
    }

    protected function nextRowNumber(): int
    {
        return ++$this->rowNumber;
    }

    abstract protected function reportTitle(): string;

    abstract protected function documentNumber(): string;

    public function startCell(): string
    {
        return 'A6';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            6 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = Coordinate::stringFromColumnIndex(count($this->headings()));
                $lastRow = $sheet->getHighestRow();
                $tableRange = "A6:{$lastColumn}{$lastRow}";

                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->mergeCells("A2:{$lastColumn}2");
                $sheet->mergeCells("A3:{$lastColumn}3");
                $sheet->mergeCells("A4:{$lastColumn}4");
                $sheet->setCellValue('A1', $this->reportTitle());
                $sheet->setCellValue('A2', $this->documentNumber());
                $sheet->setCellValue('A3', 'Periode: '.$this->periode);
                $sheet->setCellValue('A4', 'Tgl. Dibuat: '.now()->locale('id')->translatedFormat('d F Y'));

                $sheet->getStyle("A1:{$lastColumn}4")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A2')->getFont()->setSize(12);
                $sheet->getStyle('A3:A4')->getFont()->setSize(10);
                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(6)->setRowHeight(32);

                $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle($tableRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A7:{$lastColumn}{$lastRow}")->getAlignment()->setWrapText(true);
                $sheet->freezePane('A7');
                $sheet->setAutoFilter($tableRange);
                $sheet->getPageSetup()->setOrientation('landscape');
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageMargins()->setTop(0.5)->setBottom(0.5)->setLeft(0.3)->setRight(0.3);
            },
        ];
    }
}
