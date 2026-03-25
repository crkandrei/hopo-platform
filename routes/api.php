<?php

use App\Http\Controllers\Api\BridgeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScanController;
use App\Http\Middleware\BridgeApiAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(BridgeApiAuth::class)->prefix('bridges')->group(function () {
    Route::post('/heartbeat', [BridgeController::class, 'heartbeat']);
    Route::post('/logs', [BridgeController::class, 'logs']);
    Route::get('/commands/{clientId}', [BridgeController::class, 'pollCommands']);
    Route::post('/commands/{clientId}/ack', [BridgeController::class, 'ackCommand']);
});

// Rute protejate cu autentificare web
Route::middleware('auth')->group(function () {

    // Rute pentru dashboard
    Route::get('/dashboard/stats', [App\Http\Controllers\DashboardApiController::class, 'stats']);

    // Rute pentru sesiuni
    Route::get('/sessions/active', [App\Http\Controllers\ScanPageController::class, 'getActiveSessions']);
    Route::post('/sessions/start', [App\Http\Controllers\ScanPageController::class, 'startSession']);
    Route::post('/sessions/{id}/stop', [App\Http\Controllers\ScanPageController::class, 'stopSession']);
    Route::get('/sessions/stats', [App\Http\Controllers\ScanPageController::class, 'getSessionStats']);

    // Rute pentru activitate
    Route::get('/activity/recent', [App\Http\Controllers\DashboardApiController::class, 'recentActivity']);
});
