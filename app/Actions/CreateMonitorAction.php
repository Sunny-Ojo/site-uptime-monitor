<?php

namespace App\Actions;

use App\Models\Monitor;

class CreateMonitorAction
{
    /**
     * Create a new site monitor.
     *
     * @param array $data
     * @return Monitor
     */
    public function execute(array $data): Monitor
    {
        return Monitor::create([
            'url' => $data['url'],
            'check_interval' => $data['check_interval'] ?? 5,
            'threshold' => $data['threshold'] ?? 3,
        ]);
    }
}
