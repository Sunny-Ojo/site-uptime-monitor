<?php

use App\Models\Monitor;
use App\Jobs\PerformMonitorCheck;
use App\Services\MonitorService;
use Illuminate\Support\Facades\Http;
use App\Enums\SiteStatus;

beforeEach(function () {
    $this->monitorService = app(MonitorService::class);
});

test('successful check updates status to up', function () {
    Http::fake([
        '*' => Http::response('ok', 200)
    ]);

    $monitor = Monitor::create([
        'url' => 'https://google.com',
        'check_interval' => 5,
        'threshold' => 3,
        'status' => SiteStatus::PENDING->value,
    ]);

    $job = new PerformMonitorCheck($monitor->id);
    $job->handle($this->monitorService);

    $monitor->refresh();
    
    expect($monitor->status)->toBe(SiteStatus::UP);
    expect($monitor->histories()->count())->toBe(1);
    
    $history = $monitor->histories()->first();
    expect($history->is_up)->toBeTrue()
        ->and($history->status_code)->toBe(200);
});

test('failed check updates status to down after threshold is met', function () {
    Http::fake([
        '*' => Http::response('server error', 500)
    ]);

    $monitor = Monitor::create([
        'url' => 'https://failing.com',
        'check_interval' => 5,
        'threshold' => 3,
        'status' => SiteStatus::UP->value,
    ]);

    // Check 1: Fails, but doesn't meet threshold 3
    (new PerformMonitorCheck($monitor->id))->handle($this->monitorService);
    $monitor->refresh();
    expect($monitor->status)->toBe(SiteStatus::PENDING);

    // Check 2: Fails, but still doesn't meet threshold
    (new PerformMonitorCheck($monitor->id))->handle($this->monitorService);
    $monitor->refresh();
    expect($monitor->status)->toBe(SiteStatus::PENDING);

    // Check 3: Fails, threshold met
    (new PerformMonitorCheck($monitor->id))->handle($this->monitorService);
    $monitor->refresh();
    expect($monitor->status)->toBe(SiteStatus::DOWN);
    expect($monitor->histories()->count())->toBe(3);
});

test('exception during check is handled as a failure', function () {
    Http::fake(function () {
        throw new \Illuminate\Http\Client\ConnectionException('Connection Timeout');
    });

    $monitor = Monitor::create([
        'url' => 'https://timeout.com',
        'check_interval' => 5,
        'threshold' => 1,
        'status' => SiteStatus::UP->value,
    ]);

    (new PerformMonitorCheck($monitor->id))->handle($this->monitorService);

    $monitor->refresh();
    
    expect($monitor->status)->toBe(SiteStatus::DOWN);
    expect($monitor->histories()->first()->status_code)->toBe(0);
    expect($monitor->histories()->first()->is_up)->toBeFalse();
});
