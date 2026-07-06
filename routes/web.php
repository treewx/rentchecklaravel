<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AkahuController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyTransactionController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

// Cron endpoint for external cron services
Route::get('/cron/rent-check/{token}', function ($token) {
    $cronToken = config('app.cron_token');

    if (!$cronToken || !hash_equals($cronToken, $token)) {
        abort(403, 'Invalid token');
    }

    try {
        // Run the rent check command
        $exitCode = Artisan::call('rent:check');

        return response()->json([
            'success' => true,
            'message' => 'Rent check completed',
            'output' => Artisan::output(),
            'exit_code' => $exitCode
        ]);
    } catch (\Exception $e) {
        \Log::error('Rent check cron failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'error' => 'Rent check failed - see server logs',
        ], 500);
    }
})->name('cron.rent-check');

// Login/register/logout, password reset, email verification and 2FA routes
// are registered by Laravel Fortify (see FortifyServiceProvider for views).

// 2FA setup lives outside the two-factor.required middleware so users who
// haven't set it up yet can reach it.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/two-factor/setup', 'auth.two-factor-setup')->name('two-factor.setup');
});

Route::middleware(['auth', 'verified', 'two-factor.required'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('akahu')->name('akahu.')->group(function () {
        Route::get('/connect', [AkahuController::class, 'connect'])->name('connect');
        Route::post('/store-tokens', [AkahuController::class, 'storeTokens'])->name('store-tokens');
        Route::get('/callback', [AkahuController::class, 'callback'])->name('callback');
        Route::delete('/disconnect', [AkahuController::class, 'disconnect'])->name('disconnect');
    });

    Route::post('/properties/transactions-for-keyword', [PropertyController::class, 'getTransactionsForKeyword'])->name('properties.transactions-for-keyword');
    Route::resource('properties', PropertyController::class);

    // Property transaction routes
    Route::post('/properties/{property}/transactions', [PropertyTransactionController::class, 'store'])->name('properties.transactions.store');
    Route::get('/properties/{property}/transactions', [PropertyTransactionController::class, 'index'])->name('properties.transactions.index');

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/email-preferences', [SettingsController::class, 'updateEmailPreferences'])->name('email-preferences');
        Route::delete('/account', [SettingsController::class, 'deleteAccount'])->name('account.delete');
    });
});