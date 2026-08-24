<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    // 1. Get Tarif
    public function getPrice()
    {
        $setting = Setting::where('key', 'price_per_student')->first();
        return response()->json([
            'price_per_student' => $setting ? (int) $setting->value : 2000
        ]);
    }

    // 2. Update Tarif
    public function updatePrice(Request $request)
    {
        $request->validate([
            'price_per_student' => 'required|numeric|min:0'
        ]);

        Setting::updateOrCreate(
            ['key' => 'price_per_student'],
            ['value' => $request->price_per_student]
        );

        return response()->json(['message' => 'Tarif berhasil diperbarui!']);
    }
}
