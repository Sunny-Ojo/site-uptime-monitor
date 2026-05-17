<?php

namespace App\Jobs;

use App\Models\Monitor;
use App\Services\MonitorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PerformMonitorCheck implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Number of retries.
     */
    public int $tries = 3;

    /**
     * Job timeout in seconds.
     */
    public int $timeout = 20;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $monitorId
    ) {}

    /**
     * Prevent overlapping monitor checks.
     */
    public function middleware(): array
    {
        return [
            new WithoutOverlapping("monitor:{$this->monitorId}"),
        ];
    }

    /**
     * Backoff strategy for retries.
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    /**
     * Execute the job.
     */
    public function handle(
        MonitorService $monitorCheckService
    ): void {

        $monitor = Monitor::findOrFail($this->monitorId);
        
        $monitorCheckService->check($monitor);
    }

    /**
     * Handle failed job.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Monitor check job failed.', [
            'monitor_id' => $this->monitorId,
            'message' => $exception->getMessage(),
        ]);
    }
}