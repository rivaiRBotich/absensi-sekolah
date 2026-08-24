<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->date('tanggal')->nullable()->after('siswa_id');
        });

        // Isi data lama (yang scanned_at-nya masih ada) supaya tidak kosong
        \DB::table('attendances')
            ->whereNotNull('scanned_at')
            ->update(['tanggal' => \DB::raw('scanned_at::date')]);
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('tanggal');
        });
    }
};