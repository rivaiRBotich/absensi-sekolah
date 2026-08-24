<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\AuthController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    // Route khusus Super Admin
    Route::get('/superadmin/tenants', [TenantController::class, 'index']);
    Route::post('/superadmin/tenants', [TenantController::class, 'store']);
    Route::patch('/superadmin/tenants/{id}/toggle', [TenantController::class, 'toggleStatus']);
    Route::post('/superadmin/tenants/{id}/extend', [TenantController::class, 'extendSubscription']);
    Route::post('/logout', [AuthController::class, 'logout']);
});