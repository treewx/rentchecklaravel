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
            'error' => $e->getMessage(),
            'file' => $e->getFile() . ':' . $e->getLine()
        ], 500);
    }
})->name('cron.rent-check');

// Test notification endpoint
Route::get('/cron/test-notification/{token}', function ($token) {
    // Verify the secret token
    if ($token !== config('app.cron_token')) {
        abort(403, 'Invalid token');
    }

    try {
        // Run the test notification command
        $exitCode = Artisan::call('rent:test-notification');

        return response()->json([
            'success' => $exitCode === 0,
            'message' => $exitCode === 0 ? 'Test notification sent' : 'Test notification failed',
            'output' => Artisan::output(),
            'exit_code' => $exitCode
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('cron.test-notification');

// Debug endpoint to show server time
Route::get('/debug/server-time/{token}', function ($token) {
    if ($token !== config('app.cron_token')) {
        abort(403, 'Invalid token');
    }

    return response()->json([
        'server_time_utc' => now()->toDateTimeString(),
        'server_time_nz' => now()->timezone('Pacific/Auckland')->toDateTimeString(),
        'timezone_config' => config('app.timezone'),
    ]);
});

// Debug endpoint to check user email settings
Route::get('/debug/email-settings/{token}', function ($token) {
    if ($token !== config('app.cron_token')) {
        abort(403, 'Invalid token');
    }

    $user = App\Models\User::first();

    if (!$user) {
        return response()->json(['error' => 'No users found']);
    }

    return response()->json([
        'user_email' => $user->email,
        'email_notifications_enabled' => $user->email_notifications_enabled,
        'email_on_rent_received' => $user->email_on_rent_received,
        'email_on_rent_late' => $user->email_on_rent_late,
        'email_on_rent_partial' => $user->email_on_rent_partial,
    ]);
});

// Debug endpoint to check rent checks
Route::get('/debug/rent-checks/{token}', function ($token) {
    if ($token !== config('app.cron_token')) {
        abort(403, 'Invalid token');
    }

    $rentChecks = App\Models\RentCheck::with('property')->get();
    $now = now();

    $data = $rentChecks->map(function($check) use ($now) {
        return [
            'id' => $check->id,
            'property' => $check->property->name,
            'due_date' => $check->due_date->toDateTimeString(),
            'status' => $check->status,
            'is_due' => $check->due_date <= $now,
            'is_pending' => $check->status === 'pending',
            'will_be_checked' => ($check->status === 'pending' && $check->due_date <= $now),
        ];
    });

    return response()->json([
        'current_time' => $now->toDateTimeString(),
        'rent_checks' => $data,
        'pending_and_due_count' => $rentChecks->filter(function($check) use ($now) {
            return $check->status === 'pending' && $check->due_date <= $now;
        })->count()
    ]);
});

// Debug endpoint to reset a rent check to pending for testing
Route::get('/debug/reset-rent-check/{id}/{token}', function ($id, $token) {
    if ($token !== config('app.cron_token')) {
        abort(403, 'Invalid token');
    }

    try {
        $rentCheck = App\Models\RentCheck::find($id);

        if (!$rentCheck) {
            return response()->json(['error' => 'Rent check not found', 'id' => $id]);
        }

        $rentCheck->status = 'pending';
        $rentCheck->checked_at = null;
        $rentCheck->received_amount = null;
        $rentCheck->received_at = null;
        $rentCheck->transaction_id = null;
        $rentCheck->matching_transactions = null;
        $rentCheck->save();

        return response()->json([
            'success' => true,
            'message' => 'Rent check reset to pending',
            'rent_check' => [
                'id' => $rentCheck->id,
                'property' => $rentCheck->property->name,
                'due_date' => $rentCheck->due_date->toDateTimeString(),
                'status' => $rentCheck->status,
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'file' => $e->getFile() . ':' . $e->getLine()
        ], 500);
    }
});

// Test email endpoint - Simple HTTP API approach
Route::get('/test-email/{token}', function ($token) {
    if ($token !== config('app.cron_token')) {
        abort(403, 'Invalid token');
    }

    try {
        $user = App\Models\User::first();

        if (!$user) {
            return response()->json(['error' => 'No users found']);
        }

        // Use Mailtrap HTTP API for production email sending
        $apiKey = env('MAILTRAP_API_KEY');

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://send.api.mailtrap.io/api/send', [
            'from' => [
                'email' => 'hello@honeystoneltd.com',
                'name' => 'Rent Tracker',
            ],
            'to' => [
                [
                    'email' => $user->email,
                    'name' => $user->name ?? 'User',
                ]
            ],
            'subject' => 'Test Email - ' . now()->toDateTimeString(),
            'text' => 'This is a test email from your Laravel app via Mailtrap production API. Sent at ' . now()->toDateTimeString(),
            'html' => '<p>This is a test email from your Laravel app via Mailtrap production API.</p><p>Sent at ' . now()->toDateTimeString() . '</p>',
        ]);

        if ($response->successful()) {
            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully!',
                'to' => $user->email,
                'time' => now()->toDateTimeString(),
                'response' => $response->json()
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Mailtrap API returned an error',
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body()
            ], 500);
        }
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ], 500);
    }
});

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