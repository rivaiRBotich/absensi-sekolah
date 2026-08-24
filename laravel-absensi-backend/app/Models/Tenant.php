<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;
    
    protected $casts = [
        'hari_aktif' => 'array',
        'jam_masuk'  => 'string',
        'jam_pulang' => 'string',
    ];

    protected $fillable = [
        'name',
        'npsn',
        'address',
        'phone',
        'status',
        'due_date',
        'hari_aktif',
        'jam_masuk',
        'jam_pulang',
    ];

    /**
     * Satu Sekolah (Tenant) memiliki banyak User (Admin, Guru, Siswa)
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
