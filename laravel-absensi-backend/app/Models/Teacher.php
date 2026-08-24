<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Teacher extends Model
{
    protected $fillable = ['tenant_id', 'user_id', 'nip', 'name', 'email'];

    public function managedClass(): HasOne
    {
        return $this->hasOne(SchoolClass::class, 'teacher_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}