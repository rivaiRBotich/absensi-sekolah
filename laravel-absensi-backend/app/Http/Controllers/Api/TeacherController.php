<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller {
    public function index() {
        $user = Auth::user();

        $teachers = Teacher::when($user->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->latest()
            ->get()
            ->map(function ($teacher) {
                $teacher->has_account = !is_null($teacher->user_id);
                return $teacher;
            });

        return response()->json($teachers);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'nip'      => 'required|unique:teachers,nip',
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:teachers,email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $tenantId = Auth::user()->tenant_id;

        $teacher = DB::transaction(function () use ($validated, $tenantId) {
            $user = User::create([
                'tenant_id' => $tenantId,
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'password'  => Hash::make($validated['password']),
                'role'      => 'school-guru',
            ]);

            return Teacher::create([
                'tenant_id' => $tenantId,
                'user_id'   => $user->id,
                'nip'       => $validated['nip'],
                'name'      => $validated['name'],
                'email'     => $validated['email'],
            ]);
        });

        return response()->json([
            'message' => 'Guru berhasil ditambahkan! Akun login juga sudah dibuat.',
            'data'    => $teacher
        ], 201);
    }

    public function update(Request $request, $id) {
        $user = Auth::user();

        $teacher = Teacher::when($user->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->findOrFail($id);

        $validated = $request->validate([
            'nip'   => 'required|unique:teachers,nip,' . $teacher->id,
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:teachers,email,' . $teacher->id
                . '|unique:users,email,' . ($teacher->user_id ?? 'NULL'),
        ]);

        $teacher->update($validated);

        // Sinkronkan juga ke akun login-nya (kalau sudah punya akun)
        if ($teacher->user_id) {
            User::where('id', $teacher->user_id)->update([
                'name'  => $validated['name'],
                'email' => $validated['email'],
            ]);
        }

        return response()->json(['message' => 'Data guru berhasil diperbarui!', 'data' => $teacher]);
    }

    public function resetPassword(Request $request, $id) {
        $user = Auth::user();

        $teacher = Teacher::when($user->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->findOrFail($id);

        $request->validate(['password' => 'required|string|min:6']);

        if ($teacher->user_id) {
            User::where('id', $teacher->user_id)->update([
                'password' => Hash::make($request->password),
            ]);

            return response()->json(['message' => 'Password guru berhasil direset.']);
        }

        // Guru lama yang belum punya akun -> buatkan sekalian
        $newUser = User::create([
            'tenant_id' => $teacher->tenant_id,
            'name'      => $teacher->name,
            'email'     => $teacher->email,
            'password'  => Hash::make($request->password),
            'role'      => 'school-guru',
        ]);

        $teacher->update(['user_id' => $newUser->id]);

        return response()->json(['message' => 'Akun login guru berhasil dibuat.']);
    }

    public function destroy($id) {
        $user = Auth::user();

        $teacher = Teacher::when($user->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->findOrFail($id);

        // Hapus juga akun login-nya kalau ada
        if ($teacher->user_id) {
            User::where('id', $teacher->user_id)->delete();
        }

        $teacher->delete();
        return response()->json(['message' => 'Guru berhasil dihapus!']);
    }
}