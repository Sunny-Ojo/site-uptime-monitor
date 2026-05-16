<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MonitorController;

Route::apiResource('monitors', MonitorController::class)->only(['index', 'store']);
Route::get('/monitors/{id}/history', [MonitorController::class, 'history']);
