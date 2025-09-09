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

Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register'])->name('register');
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login'])->name('login');
Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout'])->name('logout')->middleware('auth:sanctum');




Route::apiResource('links', App\Http\Controllers\Api\LinkController::class);

Route::post('/r/{slug}', [App\Http\Controllers\Api\ClickController::class, 'redirect']);
Route::get('/links/{id}/stats', [App\Http\Controllers\Api\AnalyticsController::class, 'linkStats']);
Route::get('/analytics/overview', [App\Http\Controllers\Api\AnalyticsController::class, 'overview']);