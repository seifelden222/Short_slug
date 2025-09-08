<?php

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Auth routes (public)
Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register'])->name('register');
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login'])->name('login');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout'])->name('logout');

    // Links CRUD
    Route::apiResource('links', App\Http\Controllers\Api\LinkController::class);

    // Analytics
    Route::get('/links/{id}/stats', [App\Http\Controllers\Api\AnalyticsController::class, 'linkStats'])->name('links.stats');
    Route::get('/analytics/overview', [App\Http\Controllers\Api\AnalyticsController::class, 'overview'])->name('analytics.overview');
});

// Redirect endpoint (public, but rate limited)
Route::post('/r/{slug}', [App\Http\Controllers\Api\RedirectController::class, 'redirect'])->name('redirect');
