<?php

namespace App\Console\Commands;

use App\Jobs\PerformMonitorCheck;
use App\Models\Monitor;
use Illuminate\Console\Command;

class CheckMonitorsCommand extends Command
{
    /**
     * The console command signature.
     */
    protected $signature = 'monitor:check';

    /**
     * The console command description.
     */
    protected $description = 'Dispatch checks for monitors that are due';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Finding due monitors...');

        Monitor::query()
            ->due()
            ->chunkById(100, function ($monitors) {
                foreach ($monitors as $monitor) {
                    PerformMonitorCheck::dispatch($monitor->id);
                }
            });

        $this->info('Monitor check jobs dispatched successfully.');
    }
}
