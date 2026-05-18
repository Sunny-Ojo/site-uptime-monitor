<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MonitorController;
use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('monitors', MonitorController::class)->only(['index', 'store']);
    Route::get('/monitors/{id}/history', [MonitorController::class, 'history']);
});
