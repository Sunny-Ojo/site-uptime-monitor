<?php

namespace App\Services;

use App\Models\Monitor;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MonitorPingService
{
    /**
     * Perform HTTP request to check monitor status.
     *
     * @return array [bool $isUp, int $statusCode, ?int $responseTimeMs]
     */
    public function ping(Monitor $monitor): array
    {
        $startTime = microtime(true);
        $statusCode = 0;
        $responseTimeMs = null;
        $isUp = false;

        try {
            $response = Http::timeout(10)
                ->connectTimeout(5)
                ->retry(2, 500)
                ->get($monitor->url);

            $responseTimeMs = (int) round((microtime(true) - $startTime) * 1000);
            $statusCode = $response->status();
            $isUp = $response->successful() || $response->redirect();
        } catch (ConnectionException $exception) {
            Log::warning('Connection error during monitor check.', [
                'monitor_id' => $monitor->id,
                'url' => $monitor->url,
                'message' => $exception->getMessage(),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Unexpected monitor check failure.', [
                'monitor_id' => $monitor->id,
                'url' => $monitor->url,
                'message' => $exception->getMessage(),
            ]);
        }

        return [$isUp, $statusCode, $responseTimeMs];
    }
}
