<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    public static function send(string $target, string $message): void
    {
        if (empty($target)) {
            return;
        }

        Http::withHeaders([
            'Authorization' => 'Bearer ganti-dengan-token-rahasia'
        ])->post('http://localhost:3010/send-message', [
            'target'  => $target,
            'message' => $message,
        ]);
    }
}