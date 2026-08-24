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
        Schema::table('tenants', function (Blueprint $table) {
            $table->json('hari_aktif')->nullable()->default(json_encode(['senin','selasa','rabu','kamis','jumat','sabtu']));
            $table->time('jam_masuk')->nullable()->default('07:30:00');
            $table->time('jam_pulang')->nullable()->default('13:00:00');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {       
            $table->dropColumn(['hari_aktif', 'jam_masuk', 'jam_pulang']);
        });
    }
};
