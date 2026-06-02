<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Service\ApiSyncService;

class SyncMembersCommand extends Command
{
    protected $signature = 'members:sync';
    protected $description = 'Sinkronisasi berkala data RFID dari database cashless';

    public function handle(ApiSyncService $syncService)
    {
        $result = $syncService->sync();
        if ($result['failed']) {
            $this->error('Gagal menjalankan sinkronisasi otomatis.');
        } else {
            $this->info("Sinkronisasi otomatis berhasil. Total data terproses: {$result['synced']}");
        }
    }
}