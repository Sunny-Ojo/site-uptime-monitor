<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return a success response.
     */
    protected function success($data, int $code = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
        ], $code);
    }

    /**
     * Return an error response.
     */
    protected function error(string $message, int $code): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $code);
    }

    /**
     * Return a 404 not found response.
     */
    protected function notFound(string $message = 'Resource not found.'): JsonResponse
    {
        return $this->error($message, 404);
    }

    /**
     * Return a paginated response.
     */
    protected function paginate($collection, $paginator): JsonResponse
    {
        return response()->json([
            'data' => $collection,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
