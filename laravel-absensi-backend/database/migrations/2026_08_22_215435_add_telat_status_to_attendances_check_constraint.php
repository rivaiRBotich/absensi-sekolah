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
        Schema::table('attendances', function (Blueprint $table) {
            DB::statement('ALTER TABLE attendances DROP CONSTRAINT attendances_status_check');
            DB::statement("ALTER TABLE attendances ADD CONSTRAINT attendances_status_check CHECK (status IN ('hadir', 'sakit', 'izin', 'alpha', 'telat'))");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            DB::statement('ALTER TABLE attendances DROP CONSTRAINT attendances_status_check');
            DB::statement("ALTER TABLE attendances ADD CONSTRAINT attendances_status_check CHECK (status IN ('hadir', 'sakit', 'izin', 'alpha'))");
        });
    }
};
