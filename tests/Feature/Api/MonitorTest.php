<?php

use App\Enums\SiteStatus;
use App\Models\Monitor;
use App\Models\CheckHistory;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

test('it can register a new monitor', function () {
    $response = $this->postJson('/api/monitors', [
        'url' => 'https://www.google.com',
        'check_interval' => 5,
        'threshold' => 3
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id', 'url', 'check_interval', 'threshold', 'status', 'last_checked_at', 'uptime_percentage', 'created_at'
            ]
        ])
        ->assertJsonPath('data.url', 'https://www.google.com')
        ->assertJsonPath('data.status', SiteStatus::PENDING->value);

    $this->assertDatabaseHas('monitors', [
        'url' => 'https://www.google.com',
        'user_id' => $this->user->id
    ]);
});

test('it validates the monitor request', function () {
    $response = $this->postJson('/api/monitors', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['url']);
});

test('it rejects duplicate urls', function () {
    Monitor::create(['url' => 'https://www.google.com', 'user_id' => $this->user->id]);

    $response = $this->postJson('/api/monitors', [
        'url' => 'https://www.google.com'
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['url']);
});

test('it can list all monitors', function () {
    Monitor::create(['url' => 'https://example1.com', 'user_id' => $this->user->id]);
    Monitor::create(['url' => 'https://example2.com', 'user_id' => $this->user->id]);

    $response = $this->getJson('/api/monitors');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('it can fetch check history for a monitor', function () {
    $monitor = Monitor::create(['url' => 'https://www.google.com', 'user_id' => $this->user->id]);
    CheckHistory::create([
        'monitor_id' => $monitor->id,
        'status_code' => 200,
        'response_time_ms' => 200,
        'is_up' => true,
        'checked_at' => now()
    ]);

    $response = $this->getJson("/api/monitors/{$monitor->id}/history");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'monitor_id', 'status_code', 'response_time_ms', 'is_up', 'checked_at']
            ],
            'meta' => ['current_page', 'per_page', 'total']
        ]);
});

test('it returns 404 for non-existent monitor history', function () {
    $response = $this->getJson('/api/monitors/012345/history');

    $response->assertStatus(404)
        ->assertJson(['message' => 'Monitor not found.']);
});
