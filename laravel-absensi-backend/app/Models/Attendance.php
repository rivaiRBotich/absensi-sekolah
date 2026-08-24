<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';

    protected $fillable = [
        'tenant_id',
        'siswa_id',
        'tanggal',
        'time',
        'status',
        'note',
        'scanned_at',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'siswa_id');
    }
}