<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        // Auto reject tahap approval yang melewati batas waktu (FR-7.1–FR-7.3, NFR-3.1).
        // Catatan deployment: memerlukan cron server `* * * * * php artisan schedule:run`
        // yang aktif — dikoordinasikan terpisah pada tahap deployment (bukan tanggung jawab
        // developer aplikasi), lihat T4.2 di tasks_v2.0.md.
        $schedule->command('pengajuan:auto-reject')->everyFifteenMinutes();

        // Transisi status jadwal Akan Datang -> Berlangsung otomatis di background,
        // tidak lagi tergantung ada tidaknya orang yang membuka halaman jadwal.
        $schedule->command('jadwal:auto-update-status')->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
