<?php

namespace App\Events;

use App\Models\Monitor;
use App\Enums\SiteStatus;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MonitorStatusChanged
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Monitor $monitor,
        public SiteStatus $previousStatus,
        public SiteStatus $newStatus
    ) {}
}
