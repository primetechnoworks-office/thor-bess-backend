<?php

use App\Http\Controllers\Admin\CredentialsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MilestoneController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

/*
|--------------------------------------------------------------------------
| Authentication (guests only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->prefix('admin')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');

    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store'])->middleware('throttle:5,10')->name('register.store');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:5,1')->name('password.email');

    // `password.reset` is the route name Laravel's reset-link email points to.
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

/*
|--------------------------------------------------------------------------
| Admin panel
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'password'])->name('profile.password');

    Route::get('leads/export', [LeadController::class, 'export'])->name('leads.export');
    Route::get('leads/{lead}/attachment', [LeadController::class, 'attachment'])->name('leads.attachment');
    Route::resource('leads', LeadController::class)->only(['index', 'show', 'update', 'destroy']);

    Route::post('posts/images', [PostController::class, 'uploadImage'])->middleware('throttle:30,1')->name('posts.images');
    Route::resource('posts', PostController::class)->except(['show']);

    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('testimonials', TestimonialController::class)->except(['show']);
    Route::resource('team', TeamMemberController::class)->except(['show']);
    Route::resource('milestones', MilestoneController::class)->except(['show']);
    Route::resource('faqs', FaqController::class)->except(['show']);
    Route::put('partners/incubators', [PartnerController::class, 'incubators'])->name('partners.incubators');
    Route::resource('partners', PartnerController::class)->except(['show']);
    Route::get('hero-banners', [MediaController::class, 'edit'])->name('media.edit');
    Route::put('hero-banners', [MediaController::class, 'update'])->name('media.update');

    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('credentials', [CredentialsController::class, 'edit'])->name('credentials.edit');
        Route::put('credentials', [CredentialsController::class, 'update'])->name('credentials.update');
    });
});
