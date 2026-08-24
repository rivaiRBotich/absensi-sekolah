<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolHoliday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolHolidayController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $holidays = SchoolHoliday::where(function ($q) use ($user) {
                $q->whereNull('tenant_id')->orWhere('tenant_id', $user->tenant_id);
            })
            ->orderBy('tanggal')
            ->get();

        return response()->json($holidays);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal'    => 'required|date',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $holiday = SchoolHoliday::updateOrCreate(
            ['tenant_id' => Auth::user()->tenant_id, 'tanggal' => $request->tanggal],
            ['keterangan' => $request->keterangan]
        );

        return response()->json(['message' => 'Hari libur berhasil ditambahkan.', 'data' => $holiday], 201);
    }

    public function destroy($id)
    {
        $user = Auth::user();

        // Hanya boleh hapus hari libur milik tenant sendiri, bukan libur nasional global (tenant_id null)
        $holiday = SchoolHoliday::where('id', $id)
            ->where('tenant_id', $user->tenant_id)
            ->firstOrFail();

        $holiday->delete();

        return response()->json(['message' => 'Hari libur berhasil dihapus.']);
    }
}