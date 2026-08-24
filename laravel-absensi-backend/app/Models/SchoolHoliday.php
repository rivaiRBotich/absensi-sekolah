<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SchoolHoliday extends Model
{
    protected $fillable = ['tenant_id', 'tanggal', 'keterangan'];
}