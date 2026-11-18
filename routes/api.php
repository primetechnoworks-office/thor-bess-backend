<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website API (consumed by the Next.js site)
|--------------------------------------------------------------------------
| All responses follow the website's { ok: true } / { ok: false, error } shape.
*/
Route::middleware('throttle:leads')->prefix('leads')->group(function () {
    Route::post('contact', [LeadController::class, 'contact']);
    Route::post('datasheet', [LeadController::class, 'datasheet']);
    Route::post('quote', [LeadController::class, 'quote']);
});

Route::get('posts', [PostController::class, 'index']);
Route::get('posts/categories', [PostController::class, 'categories']);
Route::get('posts/{slug}', [PostController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Token auth (Sanctum) — for API clients / future mobile app
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:api-login');

    Route::middleware(['auth:sanctum', 'active'])->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});
