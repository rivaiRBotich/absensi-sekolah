<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AttendanceSummaryExport implements FromArray, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return collect($this->data)->map(fn($d) => [
            $d['nama'], $d['kelas'], $d['hadir'], $d['telat'], $d['izin'], $d['sakit'], $d['alpha']
        ])->toArray();
    }

    public function headings(): array
    {
        return ['Nama Siswa', 'Kelas', 'Hadir', 'Telat', 'Izin', 'Sakit', 'Alpha'];
    }
}