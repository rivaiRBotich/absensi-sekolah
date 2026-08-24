<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->timestamp('waktu_pulang')->nullable()->after('scanned_at');
            $table->unique(['siswa_id', 'tanggal']); // 1 siswa cuma 1 record per hari
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique(['siswa_id', 'tanggal']);
            $table->dropColumn('waktu_pulang');
        });
    }
};