<?php

namespace App\Console\Commands;

use App\Services\PengajuanService;
use Illuminate\Console\Command;

class AutoRejectPengajuan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pengajuan:auto-reject';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto reject tahap approval pengajuan yang melewati batas waktu 2 hari tanpa respons verifikator';

    /**
     * Execute the console command.
     */
    public function handle(PengajuanService $pengajuanService): int
    {
        $count = $pengajuanService->autoRejectExpiredApprovals();
        $this->info("Auto reject selesai. {$count} tahap approval diproses.");

        return self::SUCCESS;
    }
}
