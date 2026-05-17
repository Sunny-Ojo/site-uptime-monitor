<?php

namespace App\Services;

use App\Models\Monitor;
use App\Enums\SiteStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Actions\CreateMonitorAction;
use Illuminate\Support\Facades\DB;

class MonitorService
{
    public function __construct(
        protected CreateMonitorAction $createMonitorAction,
        protected MonitorPingService $pingService,
        protected MonitorStatusService $statusService
    ) {}
    /**
     * Get all monitors.
     */
    public function getAllMonitors(): Collection
    {
        return Monitor::all();
    }

    /**
     * Find a monitor by ID.
     */
    public function findMonitor(int $id): ?Monitor
    {
        return Monitor::find($id);
    }

    /**
     * Create a new monitor.
     */
    public function createMonitor(array $data): Monitor
    {
        return $this->createMonitorAction->execute($data);
    }

    /**
     * Get paginated history for a monitor.
     */
    public function getMonitorHistory(Monitor $monitor, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $monitor->histories()
            ->orderByDesc('checked_at')
            ->paginate(perPage: $perPage, page: $page);
    }


    /**
     * Perform monitor check.
     */
    public function check(Monitor $monitor): void
    {
        [$isUp, $statusCode, $responseTimeMs] =
            $this->pingService->ping($monitor);

        [$statusChanged, $previousStatus, $newStatus] =
            DB::transaction(function () use (
                $monitor,
                $statusCode,
                $responseTimeMs,
                $isUp
            ) {

                $monitor->histories()->create([
                    'status_code' => $statusCode,
                    'response_time_ms' => $responseTimeMs,
                    'is_up' => $isUp,
                    'checked_at' => now(),
                ]);

                $previousStatus = $monitor->status;

                $monitor->update([
                    'last_checked_at' => now(),
                ]);

                [$statusChanged, $newStatus] =
                    $this->statusService
                    ->determineStatus($monitor, $isUp);

                if ($statusChanged) {

                    $monitor->update([
                        'status' => $newStatus,
                    ]);
                }

                $this->updateUptimePercentage($monitor);

                return [
                    $statusChanged,
                    $previousStatus,
                    $newStatus,
                ];
            });

        if ($statusChanged) {
            event(new \App\Events\MonitorStatusChanged($monitor, $previousStatus, $newStatus));
        }
    }

    /**
     * Update uptime percentage.
     */
    protected function updateUptimePercentage(
        Monitor $monitor
    ): void {

        $totalChecks = $monitor->histories()->count();

        if ($totalChecks === 0) {
            return;
        }

        $successfulChecks = $monitor->histories()
            ->where('is_up', true)
            ->count();

        $uptimePercentage = round(
            ($successfulChecks / $totalChecks) * 100,
            2
        );

        $monitor->update([
            'uptime_percentage' => $uptimePercentage,
        ]);
    }
}
