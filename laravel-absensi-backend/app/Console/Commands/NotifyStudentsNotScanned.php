<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\Student;
use App\Models\Attendance;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotifyStudentsNotScanned extends Command
{
    protected $signature = 'attendance:notify-not-scanned';
    protected $description = 'Kirim notifikasi WA ke orang tua siswa yang belum absen setelah lewat jam masuk';

    public function handle()
    {
        $today = Carbon::today();
        $namaHariIni = strtolower($today->locale('id')->isoFormat('dddd')); // "senin", dst

        $tenants = Tenant::where('status', 'active')->get();

        foreach ($tenants as $tenant) {
            $hariAktif = $tenant->hari_aktif ?? [];
            
            if (!in_array($namaHariIni, $hariAktif)) {
                continue; // bukan hari sekolah, skip
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

            $jamMasuk   = $tenant->jam_masuk ?? '07:00:00';
            $batasMasuk = Carbon::parse($today->toDateString() . ' ' . $jamMasuk);

            if (now()->lt($batasMasuk)) {
                continue; // belum lewat jam masuk, belum saatnya cek
            }

            $siswaSudahAbsen = Attendance::where('tenant_id', $tenant->id)
                ->where('tanggal', $today->toDateString())
                ->pluck('siswa_id');

            $siswaBelumAbsen = Student::where('tenant_id', $tenant->id)
                ->whereNotIn('id', $siswaSudahAbsen)
                ->get();

            $terkirim = 0;

            foreach ($siswaBelumAbsen as $siswa) {
                $sudahDinotif = DB::table('attendance_late_notifications')
                    ->where('siswa_id', $siswa->id)
                    ->where('tanggal', $today->toDateString())
                    ->exists();

                if ($sudahDinotif) {
                    continue; // sudah pernah dikirim hari ini, skip
                }

                if (!empty($siswa->parent_phone)) {
                    WhatsAppService::send(
                        $siswa->parent_phone,
                        "Halo, sampai saat ini ({$today->format('d/m/Y')} pukul " . now()->format('H:i') . " WIB) anak Anda, {$siswa->name}, belum ada status absen di sekolah. Mohon segera hubungi guru atau wali kelas untuk konfirmasi."
                    );
                    $terkirim++;
                }

                DB::table('attendance_late_notifications')->insert([
                    'tenant_id'  => $tenant->id,
                    'siswa_id'   => $siswa->id,
                    'tanggal'    => $today->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->info("Tenant {$tenant->name}: {$terkirim} notifikasi terkirim.");
        }

        return Command::SUCCESS;
    }
}