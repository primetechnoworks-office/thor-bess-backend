<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\SiteController;
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

Route::get('settings', [SiteController::class, 'settings']);
Route::get('products', [SiteController::class, 'products']);
Route::get('products/datasheet-options', [SiteController::class, 'datasheetOptions']);
Route::get('products/{slug}', [SiteController::class, 'product']);
Route::get('testimonials', [SiteController::class, 'testimonials']);
Route::get('team', [SiteController::class, 'team']);
Route::get('milestones', [SiteController::class, 'milestones']);
Route::get('faqs', [SiteController::class, 'faqs']);
Route::get('partners', [SiteController::class, 'partners']);
Route::get('credentials', [SiteController::class, 'credentials']);
Route::get('media', [SiteController::class, 'media']);

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
