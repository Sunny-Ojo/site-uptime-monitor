<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MonitorHistoryRequest;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Http\Requests\StoreMonitorRequest;
use App\Services\MonitorService;
use App\Http\Resources\MonitorResource;
use App\Http\Resources\CheckHistoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MonitorController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected MonitorService $monitorService
    ) {}

    /**
     * List all monitors.
     */
    public function index(): JsonResponse
    {
        try {
            return $this->success(
                MonitorResource::collection($this->monitorService->getAllMonitors())
            );
        } catch (\Exception $e) {
            Log::error('Error fetching monitors: ' . $e->getMessage());
            return $this->error('Failed to retrieve monitors. Please try again later.', 500);
        }
    }

    /**
     * Register a new monitor.
     */
    public function store(StoreMonitorRequest $request): JsonResponse
    {
        try {
            $monitor = $this->monitorService->createMonitor($request->validated());
            return $this->success(new MonitorResource($monitor), 201);
        } catch (\Exception $e) {
            Log::error('Error creating monitor: ' . $e->getMessage());
            return $this->error('Failed to create monitor. Please try again later.', 500);
        }
    }

    /**
     * Fetch check history for a specific monitor.
     */
    public function history(MonitorHistoryRequest $request, int $id): JsonResponse
    {
        try {
            $monitor = $this->monitorService->findMonitor($id);

            if (!$monitor) {
                return $this->notFound('Monitor not found.');
            }

            $history = $this->monitorService->getMonitorHistory(
                $monitor,
                $request->integer('per_page', 15),
                $request->integer('page', 1)
            );

            return $this->paginate(
                CheckHistoryResource::collection($history),
                $history
            );
        } catch (\Exception $e) {
            Log::error('Error fetching monitor history', [
                'monitor_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return $this->error(
                'Failed to retrieve history. Please try again later.',
                500
            );
        }
    }
}
