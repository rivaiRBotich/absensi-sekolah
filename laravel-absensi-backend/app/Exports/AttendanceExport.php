<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $attendances;

    public function __construct($attendances)
    {
        $this->attendances = $attendances;
    }

    public function collection()
    {
        return $this->attendances;
    }

    public function headings(): array
    {
        return ['Tanggal', 'NISN', 'Nama Siswa', 'Kelas', 'Waktu Masuk', 'Waktu Pulang', 'Status', 'Catatan'];
    }

    public function map($item): array
    {
        return [
            Carbon::parse($item->tanggal)->format('d-m-Y'),
            $item->student->nisn ?? '-',
            $item->student->name ?? '-',
            $item->student->schoolClass->name ?? '-',
            $item->scanned_at ? Carbon::parse($item->scanned_at)->format('H:i') : '-',
            $item->waktu_pulang ? Carbon::parse($item->waktu_pulang)->format('H:i') : '-',
            ucfirst($item->status),
            $item->note ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    
}
