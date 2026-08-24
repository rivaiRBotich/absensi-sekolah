<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Absen;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AbsenController extends Controller
{
    // 1. Absen via RFID (ESP32)
    public function storeRfid(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required',
            'rfid_uid'  => 'required'
        ]);

        $tenant = Tenant::findOrFail($request->tenant_id);
        if ($tenant->status !== 'active') {
            return response()->json(['message' => 'Sekolah anda dinonaktifkan. Silakan hubungi admin.'], 403);
        }

        $student = Students::where('tenant_id', $request->tenant_id)
            ->where('rfid_uid', $request->rfid_uid)
            ->firstOrFail();

        $attendance = Absensi::create([
            'tenant_id'  => $tenant->id,
            'student_id' => $student->id,
            'status'     => 'hadir',
            'method'     => 'rfid',
            'scanned_at' => now(),
        ]);

        $this->sendWhatsAppNotification($student, 'Hadir via RFID');

        return response()->json([
            'message' => 'Absen RFID Berhasil',
            'student' => $student->name
        ]);
    }

    // 2. Absen via Face Recognition (Nuxt Web Kamera)
    public function storeFace(Request $request)
    {
        $request->validate([
            'tenant_id'      => 'required',
            'student_id'     => 'required',
            'captured_image' => 'required|image'
        ]);

        $tenant = Tenant::findOrFail($request->tenant_id);
        if ($tenant->status !== 'active') {
            return response()->json(['message' => 'Sekolah anda dinonaktifkan. Silakan hubungi admin.'], 403);
        }

        // Konsisten gunakan Model students
        $student = Students::where('tenant_id', $request->tenant_id)
            ->findOrFail($request->student_id);

        // Forward gambar ke Python FastAPI Service
        $response = Http::attach(
            'known_face_file', file_get_contents(storage_path('app/public/' . $student->face_image_path)), 'known.jpg'
        )->attach(
            'captured_face_file', file_get_contents($request->file('captured_image')->getRealPath()), 'captured.jpg'
        )->post('https://chose-connector-likelihood-question.trycloudflare.com/verify-face');

        if ($response->successful() && $response->json('verified')) {
            // Konsisten gunakan Model Absen
            Absensi::create([
                'tenant_id'  => $tenant->id,
                'student_id' => $student->id,
                'status'     => 'hadir',
                'method'     => 'face',
                'scanned_at' => now(),
            ]);

            $this->sendWhatsAppNotification($student, 'Hadir via Wajah');

            return response()->json(['message' => 'Absen Wajah Berhasil']);
        }

        return response()->json([
            'message' => $response->json('detail') ?? 'Verifikasi wajah gagal'
        ], 400);
    }

    // Helper Send WA
    private function sendWhatsAppNotification($student, $method)
    {
        if (empty($student->parent_phone)) {
            return;
        }

        Http::withHeaders([
            'Authorization' => 'YOUR_WA_GATEWAY_TOKEN'
        ])->post('https://api.fonnte.com/send', [
            'target'  => $student->parent_phone,
            'message' => "Halo, siswa {$student->name} telah melakukan absensi ({$method}) pada jam " . now()->format('H:i:s'),
        ]);
    }
}