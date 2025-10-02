<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AkahuController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

// Cron endpoint for external cron services
Route::get('/cron/rent-check/{token}', function ($token) {
    // Verify the secret token
    if ($token !== config('app.cron_token')) {
        abort(403, 'Invalid token');
    }

    // Run the rent check command
    Artisan::call('rent:check');

    return response()->json([
        'success' => true,
        'message' => 'Rent check completed',
        'output' => Artisan::output()
    ]);
})->name('cron.rent-check');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('akahu')->name('akahu.')->group(function () {
        Route::get('/connect', [AkahuController::class, 'connect'])->name('connect');
        Route::post('/store-tokens', [AkahuController::class, 'storeTokens'])->name('store-tokens');
        Route::get('/callback', [AkahuController::class, 'callback'])->name('callback');
        Route::delete('/disconnect', [AkahuController::class, 'disconnect'])->name('disconnect');
    });

    Route::post('/properties/transactions-for-keyword', [PropertyController::class, 'getTransactionsForKeyword'])->name('properties.transactions-for-keyword');
    Route::resource('properties', PropertyController::class);

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/email-preferences', [SettingsController::class, 'updateEmailPreferences'])->name('email-preferences');
    });
});