<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappQueue extends Model
{
    protected $table = 'whatsapp_queue';

    protected $fillable = ['target', 'message', 'status', 'attempts', 'sent_at'];
}
