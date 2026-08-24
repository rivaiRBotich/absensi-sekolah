<?php

namespace App\Imports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToModel, WithHeadingRow {
    public function model(array $row) {
        return new Student([
            'nisn'     => $row['nisn'],
            'name'     => $row['nama'],
            'gender'   => strtoupper($row['gender']), // L atau P
            'class_id' => $row['class_id'],
        ]);
    }
}