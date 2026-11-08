<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RevenueReportExport extends DefaultValueBinder implements
    FromArray,
    WithHeadings,
    ShouldAutoSize,
    WithTitle,
    WithStyles,
    WithColumnFormatting,
    WithCustomValueBinder
{
    public function __construct(private array $report) {}

    public function title(): string
    {
        return 'Laporan Pendapatan';
    }

    public function headings(): array
    {
        return ['Tanggal', 'Kode Booking', 'Lapangan', 'Jam', 'Pelanggan', 'Nomor WhatsApp', 'Total (Rp)'];
    }

    public function array(): array
    {
        $rows = $this->report['bookings']->map(fn($booking) => [
            $booking->booking_date->format('d/m/Y'),
            $booking->code,
            $booking->court->name,
            substr($booking->start_time, 0, 5) . ' - ' . substr($booking->end_time, 0, 5),
            $booking->customer_name,
            $booking->customer_phone,
            $booking->total_price,
        ])->all();

        $rows[] = ['', '', '', '', '', 'TOTAL', $this->report['revenue']];

        return $rows;
    }

    public function columnFormats(): array
    {
        return [
            'G' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $totalRow = $this->report['count'] + 2; // baris 1 = judul kolom

        return [
            1 => ['font' => ['bold' => true]],
            $totalRow => ['font' => ['bold' => true]],
        ];
    }

    /**
     * Nomor WhatsApp (kolom F) harus tetap berupa teks, supaya Excel tidak
     * mengubah 6281234567890 menjadi 6,28E+12.
     */
    public function bindValue(Cell $cell, $value): bool
    {
        if ($cell->getColumn() === 'F') {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
