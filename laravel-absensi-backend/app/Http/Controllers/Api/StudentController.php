<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Imports\StudentsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller {
    public function index(Request $request) {
        $user = Auth::user();
        
        // 1. Filter data hanya milik tenant admin
        $query = Student::with('schoolClass')
            ->when($user->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id));

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        return response()->json($query->latest()->get());
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'nisn'     => 'required|unique:students,nisn',
            'name'     => 'required|string|max:255',
            'gender'   => 'required|in:L,P',
            'class_id' => 'required|exists:classes,id',
            'parent_phone' => 'nullable|string|max:20',
        ]);

        // 2. Suntikkan tenant_id dari user yang sedang terautentikasi
        $validated['tenant_id'] = Auth::user()->tenant_id;

        $student = Student::create($validated);
        return response()->json(['message' => 'Siswa berhasil ditambahkan!', 'data' => $student], 201);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $student = Student::when($user->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->findOrFail($id);

        $validated = $request->validate([
            'nisn'         => 'required|unique:students,nisn,' . $student->id,
            'name'         => 'required|string|max:255',
            'gender'       => 'required|in:L,P',
            'class_id'     => 'required|exists:classes,id',
            'parent_phone' => 'nullable|string|max:20',
        ]);

        $student->update($validated);

        return response()->json(['message' => 'Data siswa berhasil diperbarui!', 'data' => $student]);
    }

    public function import(Request $request) {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        // 3. Pasang tenant_id ke dalam class import jika menggunakan Excel
        Excel::import(new StudentsImport(Auth::user()->tenant_id), $request->file('file'));

        return response()->json(['message' => 'Data siswa berhasil terimport dari Excel!']);
    }

    public function destroy($id) {
        $user = Auth::user();
        
        // 4. Pastikan hanya bisa menghapus siswa milik tenant sendiri
        $student = Student::when($user->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->findOrFail($id);

          // Hapus file foto wajah fisiknya kalau ada, sebelum data siswanya dihapus
        if ($student->face_image_path && \Storage::disk('public')->exists($student->face_image_path)) {
            \Storage::disk('public')->delete($student->face_image_path);
        }

            
        $student->delete();
        return response()->json(['message' => 'Siswa berhasil dihapus!']);
    }

    public function updateFace(Request $request, $id)
    {
        $request->validate([
            'face_image' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $user = Auth::user();
        $student = Student::when($user->tenant_id, fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->findOrFail($id);

        // Hapus foto lama jika ada
        if ($student->face_image_path && \Storage::disk('public')->exists($student->face_image_path)) {
            \Storage::disk('public')->delete($student->face_image_path);
        }

        // Simpan foto baru ke storage/app/public/faces/
        $path = $request->file('face_image')->store('faces', 'public');

        $student->update([
            'face_image_path' => $path
        ]);

        return response()->json([
            'message' => 'Foto wajah berhasil didaftarkan!',
            'path' => $path
        ]);
    }
}