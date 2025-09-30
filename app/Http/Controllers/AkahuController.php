<?php

namespace App\Http\Controllers;

use App\Models\AkahuCredential;
use App\Services\AkahuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AkahuController extends Controller
{
    private AkahuService $akahuService;

    public function __construct(AkahuService $akahuService)
    {
        $this->akahuService = $akahuService;
    }

    public function connect(Request $request)
    {
        $user = auth()->user();

        if ($user->akahuCredentials) {
            return redirect()->route('dashboard')->with('error', 'Akahu account already connected');
        }

        // Show manual token entry form
        return view('akahu.connect');
    }

    public function storeTokens(Request $request)
    {
        $request->validate([
            'app_token' => 'required|string',
            'user_token' => 'required|string',
        ]);

        $user = auth()->user();

        // Store the tokens manually
        $credential = AkahuCredential::updateOrCreate(
            ['user_id' => $user->id],
            [
                'access_token' => $request->user_token,
                'refresh_token' => null, // Not needed for manual tokens
                'expires_at' => null, // Manual tokens don't expire typically
                'accounts' => [], // Will be populated immediately below
                'app_token' => $request->app_token, // Store app token separately
            ]
        );

        // Fetch and store accounts immediately after connecting
        try {
            $accounts = $this->akahuService->getAccounts($user);
            $credential->update(['accounts' => $accounts]);
            Log::info('Fetched ' . count($accounts) . ' accounts for user ' . $user->id);
        } catch (\Exception $e) {
            Log::error('Failed to fetch Akahu accounts: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('warning', 'Akahu tokens added but failed to fetch accounts. Please try reconnecting.');
        }

        return redirect()->route('dashboard')->with('success', 'Akahu connected successfully with ' . count($accounts) . ' account(s)');
    }

    public function callback(Request $request)
    {
        $user = auth()->user();

        if (!$request->has('code')) {
            return redirect()->route('dashboard')->with('error', 'Authorization failed');
        }

        $state = session('akahu_state');
        if ($request->state !== $state) {
            return redirect()->route('dashboard')->with('error', 'Invalid state parameter');
        }

        try {
            $tokenData = $this->akahuService->exchangeCodeForToken(
                $request->code,
                route('akahu.callback')
            );

            $accounts = $this->akahuService->getAccounts($user);

            AkahuCredential::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'access_token' => $tokenData['access_token'],
                    'refresh_token' => $tokenData['refresh_token'],
                    'expires_at' => now()->addSeconds($tokenData['expires_in']),
                    'accounts' => $accounts,
                ]
            );

            return redirect()->route('dashboard')->with('success', 'Akahu account connected successfully');
        } catch (\Exception $e) {
            Log::error('Akahu connection failed: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Failed to connect Akahu account');
        }
    }

    public function disconnect()
    {
        $user = auth()->user();
        $user->akahuCredentials?->delete();

        return redirect()->route('dashboard')->with('success', 'Akahu account disconnected');
    }
}