<?php

use App\Models\Monitor;
use App\Jobs\PerformMonitorCheck;
use Illuminate\Support\Facades\Queue;
use App\Enums\SiteStatus;

test('check monitors command dispatches jobs for due monitors', function () {
    Queue::fake();

    $dueMonitor = Monitor::create([
        'url' => 'https://google.com',
        'check_interval' => 5,
        'threshold' => 3,
        'status' => SiteStatus::PENDING->value,
        'last_checked_at' => now()->subMinutes(6)
    ]);

    $notDueMonitor = Monitor::create([
        'url' => 'https://yahoo.com',
        'check_interval' => 5,
        'threshold' => 3,
        'status' => SiteStatus::UP->value,
        'last_checked_at' => now()->addDays(1)
    ]);

    $neverCheckedMonitor = Monitor::create([
        'url' => 'https://bing.com',
        'check_interval' => 5,
        'threshold' => 3,
        'status' => SiteStatus::PENDING->value,
        'last_checked_at' => null
    ]);

    $this->artisan('monitor:check')
        ->expectsOutputToContain('dispatched successfully')
        ->assertExitCode(0);

    Queue::assertPushed(PerformMonitorCheck::class, 2);
    Queue::assertPushed(PerformMonitorCheck::class, function ($job) use ($dueMonitor) {
        return $job->monitorId === $dueMonitor->id;
    });
    Queue::assertPushed(PerformMonitorCheck::class, function ($job) use ($neverCheckedMonitor) {
        return $job->monitorId === $neverCheckedMonitor->id;
    });
    Queue::assertNotPushed(PerformMonitorCheck::class, function ($job) use ($notDueMonitor) {
        return $job->monitorId === $notDueMonitor->id;
    });
});
