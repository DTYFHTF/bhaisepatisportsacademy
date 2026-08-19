<?php

namespace App\Console\Commands;

use App\Services\ZktecoSyncService;
use Illuminate\Console\Command;

class SyncAccessDevices extends Command
{
    protected $signature = 'ops:sync-access-devices';

    protected $description = 'Push the current eligibility whitelist to ZKTeco ADMS doors (enrol the eligible, revoke the rest)';

    public function handle(ZktecoSyncService $sync): int
    {
        $queued = $sync->syncAll();

        $this->info("Queued {$queued} device commands.");

        return self::SUCCESS;
    }
}
