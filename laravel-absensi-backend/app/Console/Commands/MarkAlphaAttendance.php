<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\Student;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkAlphaAttendance extends Command
{
    protected $signature = 'attendance:mark-alpha';
    protected $description = 'Tandai siswa yang tidak absen hari ini sebagai Alfa, sesuai jadwal hari aktif tiap tenant';

    public function handle()
    {
        $today = Carbon::today();
        $namaHariIni = strtolower($today->locale('id')->isoFormat('dddd')); // "senin", "selasa", dst

        $tenants = Tenant::where('status', 'active')->get();

        foreach ($tenants as $tenant) {
            $hariAktif = $tenant->hari_aktif ?? [];

            // Skip kalau hari ini bukan hari sekolah untuk tenant ini
            if (!in_array($namaHariIni, $hariAktif)) {
                $this->info("Tenant {$tenant->name}: hari {$namaHariIni} bukan hari aktif, dilewati.");
                continue;
            }

            $isLibur = \App\Models\SchoolHoliday::where('tanggal', $today->toDateString())
                ->where(function ($q) use ($tenant) {
                    $q->whereNull('tenant_id')->orWhere('tenant_id', $tenant->id);
                })
                ->exists();

            if ($isLibur) {
                $this->info("Tenant {$tenant->name}: tanggal {$today->toDateString()} libur, dilewati.");
                continue;
            }
            // Ambil semua siswa tenant ini yang BELUM punya record attendance hari ini
            $siswaSudahAbsen = Attendance::where('tenant_id', $tenant->id)
                ->whereDate('scanned_at', $today)
                ->pluck('siswa_id');

            $siswaBelumAbsen = Student::where('tenant_id', $tenant->id)
                ->whereNotIn('id', $siswaSudahAbsen)
                ->get();

            foreach ($siswaBelumAbsen as $siswa) {
                Attendance::create([
                    'tenant_id'  => $tenant->id,
                    'siswa_id'   => $siswa->id,
                    'tanggal'    => $today->toDateString(),
                    'status'     => 'alpha',
                    'method'     => 'manual',
                    'scanned_at' => null,
                ]);
            }

            $this->info("Tenant {$tenant->name}: {$siswaBelumAbsen->count()} siswa ditandai Alfa.");
        }

        return Command::SUCCESS;
    }
}