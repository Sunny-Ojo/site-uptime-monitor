<?php

namespace App\Services;

use App\Models\Monitor;
use App\Enums\SiteStatus;

class MonitorStatusService
{
    /**
     * Determine monitor status transitions.
     * 
     * @return array [bool $statusChanged, SiteStatus $newStatus]
     */
    public function determineStatus(Monitor $monitor, bool $isUp): array
    {
        $previousStatus = $monitor->status;
        $newStatus = $previousStatus;

        if ($isUp) {
            $newStatus = SiteStatus::UP;
        } else {
            $recentChecks = $monitor->histories()
                ->latest('id')
                ->limit($monitor->threshold)
                ->get();

            $failureCount = $recentChecks
                ->where('is_up', false)
                ->count();

            if ($failureCount >= $monitor->threshold) {
                $newStatus = SiteStatus::DOWN;
            } else {
                // If it fails but hasn't reached threshold, it becomes PENDING
                $newStatus = SiteStatus::PENDING;
            }
        }

        $statusChanged = $newStatus !== $previousStatus;

        return [$statusChanged, $newStatus];
    }
}
