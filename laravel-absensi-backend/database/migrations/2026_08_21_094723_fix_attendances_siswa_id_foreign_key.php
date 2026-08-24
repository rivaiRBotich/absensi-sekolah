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
            $table->dropForeign(['siswa_id']);
            $table->foreign('siswa_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['siswa_id']);
            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
        });
    }
};
