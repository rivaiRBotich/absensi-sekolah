<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id ?? null;

        $today = Carbon::today()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth();

        // 1. Total Siswa (khusus tenant yang sedang login)
        $studentQuery = Student::query()->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
        $totalSiswa   = (clone $studentQuery)->count();
        $siswaBaru    = (clone $studentQuery)->where('created_at', '>=', $startOfMonth)->count();

        // 2. Statistik Absensi Hari Ini (pakai kolom 'tanggal', bukan scanned_at)
        $attendanceQuery = Attendance::query()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('tanggal', $today);

        $hadir = (clone $attendanceQuery)->where('status', 'hadir')->count();
        $izin  = (clone $attendanceQuery)->where('status', 'izin')->count();
        $sakit = (clone $attendanceQuery)->where('status', 'sakit')->count();
        $alpha = (clone $attendanceQuery)->where('status', 'alpha')->count();
        $telat = (clone $attendanceQuery)->where('status', 'telat')->count(); 

        $persentaseHadir = $totalSiswa > 0 ? round(($hadir / $totalSiswa) * 100, 1) : 0;

        // 3. Aktivitas Absensi Terbaru (5 data terakhir)
        $recentAttendances = Attendance::with(['student.schoolClass'])
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('tanggal', $today)
            ->latest('id') // urut berdasarkan id, karena scanned_at bisa null untuk Alfa
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'nama'   => $item->student->name ?? '-',
                    'kelas'  => $item->student->schoolClass->name ?? '-',
                    'waktu'  => $item->scanned_at ? Carbon::parse($item->scanned_at)->format('H:i') . ' WIB' : '-',
                    'status' => ucfirst($item->status),
                ];
            });

        return response()->json([
            'stats' => [
                'totalSiswa'      => $totalSiswa,
                'siswaBaru'       => $siswaBaru,
                'hadir'           => $hadir,
                'persentaseHadir' => $persentaseHadir,
                'izin'            => $izin,
                'sakit'           => $sakit,
                'alpha'           => $alpha,
            ],
            'recent_attendances' => $recentAttendances
        ]);
    }
}


// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Models\Student;
// use App\Models\Attendance;
// use Carbon\Carbon;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;

// class DashboardController extends Controller
// {
//     public function index(Request $request)
//     {
//         $user = Auth::user();
//         $tenantId = $user->tenant_id ?? null;

//         $today = Carbon::today()->toDateString();
//         $startOfMonth = Carbon::now()->startOfMonth();

//         // 1. Total Siswa (khusus tenant yang sedang login)
//         $studentQuery = Student::query()->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
//         $totalSiswa   = (clone $studentQuery)->count();
//         $siswaBaru    = (clone $studentQuery)->where('created_at', '>=', $startOfMonth)->count();

//         // 2. Statistik Absensi Hari Ini
//         $attendanceQuery = Attendance::query()
//             ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
//             ->whereDate('scanned_at', $today);

//         $hadir = (clone $attendanceQuery)->where('status', 'hadir')->count();
//         $izin  = (clone $attendanceQuery)->where('status', 'izin')->count();
//         $sakit = (clone $attendanceQuery)->where('status', 'sakit')->count();
//         $alpha = (clone $attendanceQuery)->where('status', 'alpha')->count();

//         $persentaseHadir = $totalSiswa > 0 ? round(($hadir / $totalSiswa) * 100, 1) : 0;

//         // 3. Aktivitas Absensi Terbaru (5 data terakhir)
//         $recentAttendances = Attendance::with(['student.schoolClass'])
//             ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
//             ->whereDate('scanned_at', $today)
//             ->latest('scanned_at')
//             ->take(5)
//             ->get()
//             ->map(function ($item) {
//                 return [
//                     'nama'   => $item->student->name ?? '-',
//                     'kelas'  => $item->student->schoolClass->name ?? '-',
//                     'waktu'  => $item->scanned_at ? Carbon::parse($item->scanned_at)->format('H:i') . ' WIB' : '-',
//                     'status' => ucfirst($item->status), // 'hadir' -> 'Hadir'
//                 ];
//             });

//         return response()->json([
//             'stats' => [
//                 'totalSiswa'      => $totalSiswa,
//                 'siswaBaru'       => $siswaBaru,
//                 'hadir'           => $hadir,
//                 'persentaseHadir' => $persentaseHadir,
//                 'izin'            => $izin,
//                 'sakit'           => $sakit,
//                 'alpha'           => $alpha,
//             ],
//             'recent_attendances' => $recentAttendances
//         ]);
//     }
// }