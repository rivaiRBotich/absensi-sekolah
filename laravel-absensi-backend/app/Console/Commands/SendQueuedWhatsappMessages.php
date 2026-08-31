<?php

namespace App\Console\Commands;

use App\Models\WhatsappQueue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendQueuedWhatsappMessages extends Command
{
    protected $signature = 'whatsapp:send-queue';
    protected $description = 'Kirim pesan WA yang tertunda, maksimal 20 per eksekusi, dengan jeda antar pesan';

    public function handle()
    {
        $messages = WhatsappQueue::where('status', 'pending')
            ->orderBy('created_at')
            ->limit(20)
            ->get();

        if ($messages->isEmpty()) {
            $this->info('Tidak ada pesan pending.');
            return;
        }

        foreach ($messages as $msg) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ganti-dengan-token-rahasia'
                ])->post('http://localhost:3010/send-message', [
                    'target'  => $msg->target,
                    'message' => $msg->message,
                ]);

                if ($response->successful()) {
                    $msg->update(['status' => 'sent', 'sent_at' => now()]);
                    $this->info("Terkirim ke {$msg->target}");
                } else {
                    $msg->increment('attempts');
                    $msg->update(['status' => $msg->attempts >= 3 ? 'failed' : 'pending']);
                    $this->warn("Gagal kirim ke {$msg->target}: " . $response->body());
                }
            } catch (\Exception $e) {
                $msg->increment('attempts');
                $msg->update(['status' => $msg->attempts >= 3 ? 'failed' : 'pending']);
                Log::error('WA queue error: ' . $e->getMessage());
            }

            // jeda antar pesan biar tidak terkesan spam ke WA
            sleep(3);
        }
    }
}