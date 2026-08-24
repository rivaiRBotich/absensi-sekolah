<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClassController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Filter kelas hanya milik tenant admin yang sedang login
        $classes = SchoolClass::with('homeroomTeacher')
            ->withCount('students')
            ->when($user->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->latest()
            ->get();

        return response()->json($classes);
    }

    public function store(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        // Validasi nama kelas bersifat unik HANYA dalam scope tenant_id yang sama
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('classes', 'name')->where(function ($query) use ($tenantId) {
                    return $query->where('tenant_id', $tenantId);
                }),
            ],
            'teacher_id' => 'nullable|exists:teachers,id',
        ]);

        // Inject tenant_id ke dalam payload
        $validated['tenant_id'] = $tenantId;

        $class = SchoolClass::create($validated);

        return response()->json([
            'message' => 'Kelas & Wali Kelas berhasil ditambahkan!',
            'data'    => $class->load('homeroomTeacher')
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        // Pastikan hanya bisa mengedit kelas milik tenant sendiri
        $class = SchoolClass::when($user->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('classes', 'name')
                    ->where(fn($query) => $query->where('tenant_id', $user->tenant_id))
                    ->ignore($class->id),
            ],
            'teacher_id' => 'nullable|exists:teachers,id',
        ]);

        $class->update($validated);

        return response()->json([
            'message' => 'Data kelas berhasil diperbarui!',
            'data'    => $class->load('homeroomTeacher')
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();

        // Pastikan hanya bisa menghapus kelas milik tenant sendiri
        $class = SchoolClass::when($user->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->findOrFail($id);

        $class->delete();

        return response()->json([
            'message' => 'Kelas berhasil dihapus!'
        ]);
    }
}