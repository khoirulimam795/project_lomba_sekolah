<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapLombaExport implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        protected array $rows,
        protected string $lombaNama,
        protected string $eventNama,
    ) {}

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['Peringkat', 'Pangkalan / Kontingen', 'Golongan', 'Nilai Akhir', 'Jumlah Juri'];
    }

    public function title(): string
    {
        // nama sheet max 31 karakter & tanpa karakter terlarang
        return mb_substr(preg_replace('/[\\\\\/\?\*\[\]:]/', '', $this->lombaNama), 0, 31) ?: 'Rekap';
    }

    public function columnWidths(): array
    {
        return ['A' => 10, 'B' => 34, 'C' => 14, 'D' => 13, 'E' => 13];
    }

    public function styles(Worksheet $sheet): array
    {
        // baris 1 = judul (disisipkan via event? di FromArray judul masuk sebagai row,
        // jadi kita style baris heading = baris 1, dan data mulai baris 2).
        // Untuk judul event/lomba, kita pakai WithTitle (nama sheet) + baris heading tebal.
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => '3F2A14'], 'size' => 11],
                'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2C94C']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
                'borders'   => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM, 'color' => ['rgb' => '3F2A14']]],
            ],
        ];
    }
}