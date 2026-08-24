<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\SuperAdminController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\SchoolHolidayController;

// Route Login (Public)
Route::post('/login', [AuthController::class, 'login']);

// Route Protected (Harus Bawa Token Sanctum)
Route::middleware('auth:sanctum')->post('/admin/students/{id}/face', [StudentController::class, 'updateFace']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/superadmin/tenants', [TenantController::class, 'index']);
    Route::post('/superadmin/tenants', [TenantController::class, 'store']);
    Route::patch('/superadmin/tenants/{id}/toggle', [TenantController::class, 'toggleStatus']);
    Route::post('/superadmin/tenants/{id}/extend', [TenantController::class, 'extendSubscription']);

    Route::patch('/superadmin/users/{id}/reset-password', function (Request $request, $id) {
        $request->validate(['password' => 'required|string|min:6']);
        $user = \App\Models\User::findOrFail($id);
        $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Password berhasil direset']);
        
    });

    Route::get('/superadmin/settings/price', [SuperAdminController::class, 'getPrice']);
    Route::patch('/superadmin/settings/price', [SuperAdminController::class, 'updatePrice']);

    // Route Data Guru
    Route::get('/admin/teachers', [TeacherController::class, 'index']);
    Route::post('/admin/teachers', [TeacherController::class, 'store']);
    Route::delete('/admin/teachers/{id}', [TeacherController::class, 'destroy']);
    Route::put('/admin/teachers/{id}', [TeacherController::class, 'update']);
    Route::patch('/admin/teachers/{id}/reset-password', [TeacherController::class, 'resetPassword']);

    // Route Data Siswa
    Route::get('/admin/students', [StudentController::class, 'index']);
    Route::post('/admin/students', [StudentController::class, 'store']);
    Route::post('/admin/students/import', [StudentController::class, 'import']);
    Route::delete('/admin/students/{id}', [StudentController::class, 'destroy']);
    Route::put('/admin/students/{id}', [StudentController::class, 'update']);

    // Route Manajemen Kelas
    Route::get('/admin/classes', [ClassController::class, 'index']);
    Route::post('/admin/classes', [ClassController::class, 'store']);
    Route::delete('/admin/classes/{id}', [ClassController::class, 'destroy']);
    Route::put('/admin/classes/{id}', [ClassController::class, 'update']);

    Route::get('/admin/dashboard', [DashboardController::class, 'index']);

    Route::post('/admin/siswa/{id}/face', [StudentController::class, 'updateFace']);
    Route::post('/admin/attendance/face-automatic', [AttendanceController::class, 'storeFaceAutomatic']);
    Route::get('/admin/attendance/summary', [AttendanceController::class, 'summary']);

    Route::get('/admin/settings/schedule', [TenantController::class, 'getSchedule']);
    Route::patch('/admin/settings/schedule', [TenantController::class, 'updateSchedule']);
    Route::patch('/admin/attendance/{id}', [AttendanceController::class, 'updateStatusManual']);
    Route::get('/admin/attendance', [AttendanceController::class, 'index']);   

    Route::get('/admin/attendance/export/excel', [AttendanceController::class, 'exportExcel']);
    Route::get('/admin/attendance/export/pdf', [AttendanceController::class, 'exportPdf']);
    Route::get('/admin/attendance/summary/export/excel', [AttendanceController::class, 'exportSummaryExcel']);
    Route::get('/admin/attendance/summary/export/pdf', [AttendanceController::class, 'exportSummaryPdf']);  

    Route::get('/admin/holidays', [SchoolHolidayController::class, 'index']);
    Route::post('/admin/holidays', [SchoolHolidayController::class, 'store']);
    Route::delete('/admin/holidays/{id}', [SchoolHolidayController::class, 'destroy']);

});