<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TenantController extends Controller
{
    // 1. Get List Tenant + Stats Dashboard
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $query = Tenant::withCount([
            'users as students_count' => function ($q) {
                $q->where('role', 'student');
            }, 
            'users as teachers_count' => function ($q) {
                $q->where('role', 'teacher');
            }
        ])->with(['users' => function($q) {
            $q->where('role', 'school-admin')->select('id', 'tenant_id', 'name', 'email');
        }]);

        // FIX: Gunakan closure grouping agar 'orWhere' tidak membatalkan filter 'status'
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('npsn', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $tenants = $query->latest()->get();

        // Statistik Dashboard
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'suspended_tenants' => Tenant::where('status', 'suspended')->count(),
            'due_soon_tenants' => Tenant::where('status', 'active')
                ->where('due_date', '<=', Carbon::now()->addDays(7))
                ->count(),
        ];

        return response()->json([
            'stats' => $stats,
            'tenants' => $tenants
        ]);
    }

    // 2. Create Sekolah Baru + Admin Sekolah (Atomic Transaction)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'npsn' => 'nullable|string|unique:tenants,npsn',
            'phone' => 'nullable|string',
            'due_date' => 'required|date',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:6',
        ]);

        return DB::transaction(function () use ($request) {
            // Create Tenant
            $tenant = Tenant::create([
                'name' => $request->name,
                'npsn' => $request->npsn,
                'phone' => $request->phone,
                'status' => 'active',
                'due_date' => $request->due_date,
            ]);

            // Create Admin Sekolah
            $admin = User::create([
                'tenant_id' => $tenant->id,
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'role' => 'school-admin',
            ]);

            return response()->json([
                'message' => 'Sekolah dan Admin Sekolah berhasil dibuat!',
                'tenant' => $tenant,
                'admin' => $admin
            ], 201);
        });
    }

    // 3. Toggle Status (Aktif / Suspend)
    public function toggleStatus($id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->status = ($tenant->status === 'active') ? 'suspended' : 'active';
        $tenant->save();

        return response()->json([
            'message' => 'Status sekolah berhasil diubah menjadi ' . $tenant->status,
            'status' => $tenant->status
        ]);
    }

    // 4. Perpanjang Masa Aktif
    public function extendSubscription(Request $request, $id)
    {
        $request->validate([
            'months' => 'required|integer|min:1'
        ]);

        $tenant = Tenant::findOrFail($id);
        
        $baseDate = Carbon::parse($tenant->due_date)->isPast() 
            ? Carbon::now() 
            : Carbon::parse($tenant->due_date);

        $tenant->due_date = $baseDate->addMonths($request->months);
        $tenant->status = 'active';
        $tenant->save();

        return response()->json([
            'message' => "Masa aktif sekolah berhasil diperpanjang hingga " . Carbon::parse($tenant->due_date)->format('d M Y'),
            'due_date' => $tenant->due_date
        ]);
    }

    public function updateSchedule(Request $request)
    {
        $request->validate([
            'hari_aktif' => 'required|array',
            'hari_aktif.*' => 'in:senin,selasa,rabu,kamis,jumat,sabtu,minggu',
            'jam_masuk' => 'required',
            'jam_pulang' => 'required',
        ]);

        $tenant = Tenant::findOrFail(Auth::user()->tenant_id);
        $tenant->update($request->only(['hari_aktif', 'jam_masuk', 'jam_pulang']));

        return response()->json(['message' => 'Pengaturan jadwal berhasil disimpan', 'tenant' => $tenant]);
    }

    public function getSchedule()
    {
        $tenant = Tenant::findOrFail(Auth::user()->tenant_id);
        return response()->json([
            'hari_aktif' => $tenant->hari_aktif,
            'jam_masuk'  => $tenant->jam_masuk,
            'jam_pulang' => $tenant->jam_pulang,
        ]);
    }
}