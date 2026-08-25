<?php

namespace App\Console\Commands;

use App\Services\JadwalService;
use Illuminate\Console\Command;

class JadwalAutoUpdateStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jadwal:auto-update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transisi otomatis status jadwal dari Akan Datang ke Berlangsung begitu jam mulainya terlewat, tanpa perlu ada yang membuka halaman jadwal';

    /**
     * Execute the console command.
     */
    public function handle(JadwalService $jadwalService): int
    {
        $count = $jadwalService->autoUpdateStatusAkanDatang();
        $this->info("Auto-update status jadwal selesai. {$count} jadwal dipindahkan ke Berlangsung.");

        return self::SUCCESS;
    }
}
