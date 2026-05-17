<?php

namespace App\Listeners;

use App\Events\MonitorStatusChanged;
use App\Enums\SiteStatus;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SiteStatusChanged;

class SendStatusChangedNotification
{
    /**
     * Handle the event.
     */
    public function handle(MonitorStatusChanged $event): void
    {
        $previousStatus = $event->previousStatus;
        $newStatus = $event->newStatus;

        // Only notify on transitions between UP and DOWN
        if (
            ($previousStatus === SiteStatus::UP && $newStatus === SiteStatus::DOWN) ||
            ($previousStatus === SiteStatus::DOWN && $newStatus === SiteStatus::UP)
        ) {
            Notification::route('mail', config('mail.from.address', 'admin@example.com'))
                ->notify(new SiteStatusChanged($event->monitor, $previousStatus, $newStatus));
        }
    }
}
