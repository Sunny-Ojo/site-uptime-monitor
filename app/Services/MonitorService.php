<?php

namespace App\Services;

use App\Models\Monitor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

use App\Actions\CreateMonitorAction;

class MonitorService
{
    public function __construct(
        protected CreateMonitorAction $createMonitorAction
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
}
