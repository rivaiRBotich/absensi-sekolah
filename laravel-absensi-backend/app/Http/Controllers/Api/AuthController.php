<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Cek Kredensial User
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Email atau password yang Anda masukkan salah.'
            ], 401);
        }

        // 3. Ambil Data User beserta Tenant (Sekolah)
        $user = User::with('tenant')->where('email', $request->email)->first();

        // 4. Cek Jika Sekolah Dalam Status Suspended (Matikan Akses)
        if ($user->tenant && $user->tenant->status !== 'active') {
            return response()->json([
                'message' => 'Akses sekolah Anda sedang dibekukan. Silakan lakukan pembayaran.'
            ], 403);
        }

        // 5. Buat Sanctum Token
        $token = $user->createToken('auth_token')->plainTextToken;
        $teacher = \App\Models\Teacher::where('user_id', $user->id)->with('managedClass')->first();
        $isWaliKelas = $teacher && $teacher->managedClass;

        // 6. Return Response JSON ke Nuxt 3
        return response()->json([
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'roles' => [$user->role], // Array role untuk dibaca Nuxt 3
                'tenant_id' => $user->tenant_id,
                'school_name' => $user->tenant ? $user->tenant->name : 'System Super Admin',
                'is_wali_kelas' => $isWaliKelas,
                'wali_kelas_class_id' => $isWaliKelas ? $teacher->managedClass->id : null,
                'wali_kelas_class_name' => $isWaliKelas ? $teacher->managedClass->name : null,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        // Hapus token yang sedang digunakan
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil keluar dari akun.'
        ]);
    }
}
