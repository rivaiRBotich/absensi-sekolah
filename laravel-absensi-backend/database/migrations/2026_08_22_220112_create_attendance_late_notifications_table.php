<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_late_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('students')->onDelete('cascade');
            $table->date('tanggal');
            $table->timestamps();

            $table->unique(['siswa_id', 'tanggal']); // 1 siswa cuma 1x notif per hari
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_late_notifications');
    }
};