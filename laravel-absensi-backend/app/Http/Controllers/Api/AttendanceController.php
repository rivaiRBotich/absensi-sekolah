<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendanceController extends Controller
{   
    private function buildAttendanceQuery(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id ?? null;

        $query = Attendance::with(['student.schoolClass'])
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        if ($request->filled('dari') || $request->filled('sampai')) {
            $dari   = $request->query('dari', now()->toDateString());
            $sampai = $request->query('sampai', now()->toDateString());
            $query->whereBetween('tanggal', [$dari, $sampai]);
        } else {
            $tanggal = $request->query('tanggal', now()->toDateString());
            $query->where('tanggal', $tanggal);
        }

        if ($request->filled('class_id')) {
            $query->whereHas('student', fn($q) => $q->where('class_id', $request->class_id));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('tanggal', 'desc')->orderBy('id', 'desc');
    }

    public function index(Request $request)
    {
        // $user = Auth::user();
        // $tenantId = $user->tenant_id ?? null;

        // $tanggal = $request->query('tanggal', now()->toDateString());

        // $query = Attendance::with(['student.schoolClass'])
        //     ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
        //     ->where('tanggal', $tanggal);

        // if ($request->filled('class_id')) {
        //     $query->whereHas('student', fn($q) => $q->where('class_id', $request->class_id));
        // }

        // if ($request->filled('search')) {
        //     $search = $request->search;
        //     $query->whereHas('student', function ($q) use ($search) {
        //         $q->where('name', 'like', "%{$search}%")
        //         ->orWhere('nisn', 'like', "%{$search}%");
        //     });
        // }

        // $data = $query->latest('id')->get()->map(function ($item) {
        //     return [
        //         'id'           => $item->id,
        //         'nama'         => $item->student->name ?? '-',
        //         'kelas'        => $item->student->schoolClass->name ?? '-',
        //         'waktu_masuk'  => $item->scanned_at ? Carbon::parse($item->scanned_at)->format('H:i') : '-',
        //         'waktu_pulang' => $item->waktu_pulang ? Carbon::parse($item->waktu_pulang)->format('H:i') : '-',
        //         'status'       => $item->status,
        //         'note'         => $item->note,
        //     ];
        // });

        // return response()->json($data);

            $data = $this->buildAttendanceQuery($request)->get()->map(function ($item) {
            return [
                'id'           => $item->id,
                'tanggal'      => $item->tanggal,
                'nama'         => $item->student->name ?? '-',
                'kelas'        => $item->student->schoolClass->name ?? '-',
                'waktu_masuk'  => $item->scanned_at ? Carbon::parse($item->scanned_at)->format('H:i') : '-',
                'waktu_pulang' => $item->waktu_pulang ? Carbon::parse($item->waktu_pulang)->format('H:i') : '-',
                'status'       => $item->status,
                'note'         => $item->note,
            ];
        });

        return response()->json($data);
    }

    public function exportExcel(Request $request)
    {
        $attendances = $this->buildAttendanceQuery($request)->get();
        return Excel::download(new AttendanceExport($attendances), 'rekap-absensi-' . now()->format('Y-m-d_His') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $attendances = $this->buildAttendanceQuery($request)->get();

        $pdf = Pdf::loadView('exports.attendance-pdf', [
            'attendances' => $attendances,
            'dari'        => $request->query('dari', $request->query('tanggal', now()->toDateString())),
            'sampai'      => $request->query('sampai', $request->query('tanggal', now()->toDateString())),
        ]);

        return $pdf->download('rekap-absensi-' . now()->format('Y-m-d_His') . '.pdf');
    }

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

        $student = Student::where('tenant_id', $request->tenant_id)
            ->where('rfid_uid', $request->rfid_uid)
            ->firstOrFail();

        $result = $this->handleAttendanceScan($tenant, $student, 'rfid');

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 409);
        }

        if ($result['tipe'] === 'masuk') {
            $this->sendWhatsAppNotification($student, 'Hadir via RFID');
        }

        return response()->json([
            'message' => $result['message'],
            'student' => $student->name
        ]);
    }

    // 2. Absen via Face Recognition (Pilih Nama/Manual)
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

        $student = Student::where('tenant_id', $request->tenant_id)
            ->findOrFail($request->student_id);

        $response = Http::attach(
            'known_face_file', file_get_contents(storage_path('app/public/' . $student->face_image_path)), 'known.jpg'
        )->attach(
            'captured_face_file', file_get_contents($request->file('captured_image')->getRealPath()), 'captured.jpg'
        )->post('http://localhost:8000/verify-face'); //ini laravel

        if (!($response->successful() && $response->json('verified'))) {
            return response()->json([
                'message' => $response->json('detail') ?? 'Verifikasi wajah gagal'
            ], 400);
        }

        $result = $this->handleAttendanceScan($tenant, $student, 'face');

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 409);
        }

        if ($result['tipe'] === 'masuk') {
            $this->sendWhatsAppNotification($student, 'Hadir via Wajah');
        }

        return response()->json(['message' => $result['message']]);
    }

    // 3. Absen via Face Recognition Otomatis (Tanpa Pilih Nama/Kelas)
    public function storeFaceAutomatic(Request $request)
    {
        $request->validate([
            'tenant_id'      => 'required',
            'captured_image' => 'required|image'
        ]);

        $tenant = Tenant::findOrFail($request->tenant_id);
        if ($tenant->status !== 'active') {
            return response()->json(['message' => 'Sekolah anda dinonaktifkan. Silakan hubungi admin.'], 403);
        }

        $students = Student::where('tenant_id', $request->tenant_id)
            ->whereNotNull('face_image_path')
            ->get();

        if ($students->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada data siswa dengan foto wajah di tenant ini.'
            ], 404);
        }

        $studentsData = $students->map(function($student) {
            return [
                'id' => $student->id,
                'nisn' => $student->nisn,
                'name' => $student->name,
                'gender' => $student->gender,
                'face_image_path' => $student->face_image_path,
            ];
        });

        try {
            $response = Http::attach(
                'captured_face_file',
                file_get_contents($request->file('captured_image')->getRealPath()),
                'captured.jpg'
            )->post('http://localhost:8001/recognize-face', [ //ini ke python
                'students_data' => json_encode($studentsData)
            ]);

            $result = $response->json();

            if (!($response->successful() && isset($result['verified']) && $result['verified'])) {
                return response()->json([
                    'message' => $result['message'] ?? 'Wajah tidak dikenali di sistem.'
                ], 400);
            }

            $matchedStudentData = $result['student'];
            $student = Student::findOrFail($matchedStudentData['id']);

            $scanResult = $this->handleAttendanceScan($tenant, $student, 'face');

            if (!$scanResult['success']) {
                return response()->json(['message' => $scanResult['message']], 409);
            }

            if ($scanResult['tipe'] === 'masuk') {
                $this->sendWhatsAppNotification($student, 'Hadir via Wajah (Otomatis)');
            }

            return response()->json([
                'message' => $scanResult['message'],
                'student' => $student
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal terhubung ke layanan Python: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logika inti absen masuk/pulang.
     * - Belum ada record hari ini -> absen MASUK, status 'hadir'.
     * - Sudah absen masuk, tapi belum waktu pulang -> DITOLAK.
     * - Sudah absen masuk, sudah lewat jam pulang, belum absen pulang -> absen PULANG.
     * - Sudah absen masuk & pulang -> DITOLAK (sudah lengkap hari ini).
     * - Status hari ini sudah 'izin'/'sakit'/'alpha' (di-set manual oleh admin/guru) -> DITOLAK,
     *   siswa tidak boleh menimpa keputusan admin lewat scan biasa.
     */
    private function handleAttendanceScan(Tenant $tenant, Student $student, string $method): array
    {
        $today = now()->toDateString();

        $attendance = Attendance::where('siswa_id', $student->id)
            ->where('tanggal', $today)
            ->first();

        // Belum ada record sama sekali hari ini -> absen masuk
        if (!$attendance) {
            $jamMasuk    = $tenant->jam_masuk ?? '07:00:00';
            $batasMasuk  = Carbon::parse($today . ' ' . $jamMasuk);

            // Kalau scan setelah jam_masuk -> status 'telat', kalau tidak -> 'hadir'
            $status = now()->gt($batasMasuk) ? 'telat' : 'hadir';

            Attendance::create([
                'tenant_id'  => $tenant->id,
                'siswa_id'   => $student->id,
                'tanggal'    => $today,
                'status'     => $status,
                'method'     => $method,
                'scanned_at' => now(),
            ]);

            return [
                'success' => true,
                'tipe'    => 'masuk',
                'message' => $status === 'telat'
                    ? 'Absen masuk berhasil dicatat. Anda tercatat TELAT (batas jam masuk: ' . Carbon::parse($jamMasuk)->format('H:i') . ').'
                    : 'Absen masuk berhasil dicatat.',
            ];
        }

        // Status sudah ditentukan manual oleh admin/guru (izin, sakit, alpha)
        // 'hadir' dan 'telat' dianggap status normal hasil scan, boleh lanjut ke absen pulang
        if (in_array($attendance->status, ['izin', 'sakit', 'alpha'])) {
            return [
                'success' => false,
                'message' => "Kehadiran hari ini sudah ditandai '{$attendance->status}' oleh admin/guru. Hubungi admin jika ini keliru.",
            ];
        }

        // Sudah absen masuk, cek apakah sudah absen pulang juga
        if ($attendance->waktu_pulang !== null) {
            return [
                'success' => false,
                'message' => 'Anda sudah absen masuk dan pulang hari ini.',
            ];
        }

        // Sudah absen masuk, belum absen pulang -> cek apakah sudah waktunya pulang
        $jamPulang = $tenant->jam_pulang ?? '15:00:00';
        $batasPulang = Carbon::parse($today . ' ' . $jamPulang);

        if (now()->lt($batasPulang)) {
            return [
                'success' => false,
                'message' => 'Anda sudah absen masuk. Absen pulang baru bisa dilakukan setelah jam ' . Carbon::parse($jamPulang)->format('H:i') . '. Kalau ingin pulang lebih cepat (izin), silakan hubungi admin/guru.',
            ];
        }

        $attendance->update(['waktu_pulang' => now()]);

        return [
            'success' => true,
            'tipe'    => 'pulang',
            'message' => 'Absen pulang berhasil dicatat.',
        ];
    }

    /**
     * Endpoint khusus admin/guru untuk mengubah status absensi secara manual
     * (misal: izin pulang cepat sebelum jam pulang, sakit, alpha manual, dsb).
     * Route ini HARUS tetap di dalam middleware auth:sanctum.
     */
    // public function updateStatusManual(Request $request, $id)
    // {
    //     $request->validate([
    //         'status' => 'required|in:hadir,izin,sakit,alpha',
    //         'note'   => 'nullable|string',
    //     ]);

    //     $attendance = Attendance::findOrFail($id);

    //     $attendance->update([
    //         'status'       => $request->status,
    //         'note'         => $request->note,
    //         // Kalau admin set 'izin' (misal izin pulang cepat), catat waktu_pulang sekarang
    //         'waktu_pulang' => $request->status === 'izin' ? now() : $attendance->waktu_pulang,
    //     ]);

    //     return response()->json([
    //         'message' => 'Status absensi berhasil diperbarui oleh admin/guru.',
    //         'data'    => $attendance,
    //     ]);
    // }

    public function updateStatusManual(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:hadir,izin,sakit,alpha,telat',
            'note'   => 'nullable|string',
        ]);

        $user = $request->user(); // sama dengan Auth::user()

        // Hanya role tertentu yang boleh ubah status absensi manual
        if (!in_array($user->role, ['super-admin', 'school-admin', 'school-guru'])) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengubah status absensi.'], 403);
        }

        $attendance = Attendance::findOrFail($id);

        // super-admin boleh akses tenant mana pun.
        // school-admin & school-guru HANYA boleh akses tenant miliknya sendiri.
        if ($user->role !== 'super-admin' && $attendance->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data absensi tenant lain.'], 403);
        }

        $attendance->update([
            'status'       => $request->status,
            'note'         => $request->note,
            'waktu_pulang' => $request->status === 'izin' ? now() : $attendance->waktu_pulang,
        ]);

        return response()->json([
            'message' => 'Status absensi berhasil diperbarui oleh admin/guru.',
            'data'    => $attendance,
        ]);
    }

    private function sendWhatsAppNotification($student, $method)
    {
        if (empty($student->parent_phone)) {
            return;
        }

        Http::withHeaders([
            'Authorization' => 'Bearer ganti-dengan-token-rahasia'
        ])->post('http://localhost:3010/send-message', [
            'target'  => $student->parent_phone,
            'message' => "Halo, siswa {$student->name} telah melakukan absensi ({$method}) pada jam " . now()->format('H:i:s'),
        ]);
    }

    public function summary(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id ?? null;

        if (!in_array($user->role, ['super-admin', 'school-admin', 'school-guru'])) {
            return response()->json(['message' => 'Tidak memiliki akses.'], 403);
        }

        $teacher = \App\Models\Teacher::where('user_id', $user->id)->with('managedClass')->first();
        $isWaliKelas = $teacher && $teacher->managedClass;

        // Guru biasa (bukan wali kelas) tidak boleh akses rekap bulanan sama sekali
        if ($user->role === 'school-guru' && !$isWaliKelas) {
            return response()->json(['message' => 'Fitur rekap bulanan hanya untuk wali kelas.'], 403);
        }

        $period = $request->query('period', 'month'); // week | month | 6months
        $today = now();

        [$dari, $sampai] = match ($period) {
            'week'    => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()],
            '6months' => [$today->copy()->subMonths(6)->startOfDay(), $today->copy()->endOfDay()],
            default   => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
        };

        $classId = $request->query('class_id');

        // Wali kelas dipaksa hanya lihat kelasnya sendiri, tidak bisa pilih kelas lain
        if ($user->role === 'school-guru' && $isWaliKelas) {
            $classId = $teacher->managedClass->id;
        }

        $students = Student::with('schoolClass')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->get();

        $data = $students->map(function ($student) use ($dari, $sampai) {
            $counts = Attendance::where('siswa_id', $student->id)
                ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            return [
                'siswa_id' => $student->id,
                'nama'     => $student->name,
                'kelas'    => $student->schoolClass->name ?? '-',
                'hadir'    => $counts['hadir'] ?? 0,
                'telat'    => $counts['telat'] ?? 0,
                'izin'     => $counts['izin'] ?? 0,
                'sakit'    => $counts['sakit'] ?? 0,
                'alpha'    => $counts['alpha'] ?? 0,
            ];
        });

        return response()->json([
            'periode' => ['dari' => $dari->toDateString(), 'sampai' => $sampai->toDateString(), 'label' => $period],
            'is_wali_kelas' => $isWaliKelas,
            'data' => $data,
        ]);
    }

    public function exportSummaryExcel(Request $request)
    {
        $summaryData = $this->getSummaryData($request); // refactor logic 'summary()' di atas jadi method terpisah, lihat catatan di bawah
        return Excel::download(new \App\Exports\AttendanceSummaryExport($summaryData['data']), 'rekap-siswa-' . now()->format('Y-m-d_His') . '.xlsx');
    }

    public function exportSummaryPdf(Request $request)
    {
        $summaryData = $this->getSummaryData($request);
        $pdf = Pdf::loadView('exports.attendance-summary-pdf', $summaryData);
        return $pdf->download('rekap-siswa-' . now()->format('Y-m-d_His') . '.pdf');
    }
}