<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->date('date'); // Tanggal absensi (misal: 2026-08-20)
            $table->time('time')->nullable(); // Jam masuk (misal: 06:45:00)
            $table->text('note')->nullable(); // Keterangan tambahan (surat dokter/alasan izin)
            $table->unique(['student_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            //
        });
    }
};
