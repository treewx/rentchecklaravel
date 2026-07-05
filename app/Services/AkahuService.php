<?php

namespace App\Services;

use App\Models\AkahuCredential;
use App\Models\User;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class AkahuService
{
    private string $baseUrl;
    private ?string $clientId;
    private ?string $clientSecret;

    public function __construct()
    {
        $this->baseUrl = config('services.akahu.base_url', 'https://api.akahu.io/v1');
        $this->clientId = config('services.akahu.client_id');
        $this->clientSecret = config('services.akahu.client_secret');
    }

    public function getAuthorizationUrl(string $redirectUri, string $state = null): string
    {
        if (!$this->clientId) {
            throw new \Exception('Akahu client ID not configured');
        }

        $params = [
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'scope' => 'ENDURING_CONSENT',
        ];

        if ($state) {
            $params['state'] = $state;
        }

        return $this->baseUrl . '/oauth/authorize?' . http_build_query($params);
    }

    public function exchangeCodeForToken(string $code, string $redirectUri): array
    {
        if (!$this->clientId || !$this->clientSecret) {
            throw new \Exception('Akahu credentials not configured');
        }

        $response = Http::post($this->baseUrl . '/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to exchange authorization code: ' . $response->body());
        }

        return $response->json();
    }

    public function refreshToken(string $refreshToken): array
    {
        if (!$this->clientId || !$this->clientSecret) {
            throw new \Exception('Akahu credentials not configured');
        }

        $response = Http::post($this->baseUrl . '/oauth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to refresh token: ' . $response->body());
        }

        return $response->json();
    }

    public function getAccounts(User $user): array
    {
        $credentials = $this->getUsableCredentials($user);

        $response = $this->makeAuthenticatedRequest('GET', '/accounts', $credentials);

        return $response->json()['items'] ?? [];
    }

    public function getTransactions(User $user, string $accountId, Carbon $start = null, Carbon $end = null): array
    {
        $credentials = $this->getUsableCredentials($user);

        $params = [];
        if ($start) {
            $params['start'] = $start->toISOString();
        }
        if ($end) {
            $params['end'] = $end->toISOString();
        }

        $url = "/accounts/{$accountId}/transactions";
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $response = $this->makeAuthenticatedRequest('GET', $url, $credentials);

        return $response->json()['items'] ?? [];
    }

    private function getUsableCredentials(User $user): AkahuCredential
    {
        $credentials = $user->akahuCredentials;

        if (!$credentials) {
            throw new \Exception('No Akahu credentials found');
        }

        // Expired credentials are only unusable if we can't refresh them
        if ($credentials->isExpired() && !$credentials->refresh_token) {
            throw new \Exception('Akahu credentials have expired');
        }

        return $credentials;
    }

    private function makeAuthenticatedRequest(string $method, string $endpoint, AkahuCredential $credentials): Response
    {
        if ($credentials->isExpired()) {
            $this->refreshCredentials($credentials);
        }

        \Log::info('Making Akahu API request', [
            'method' => $method,
            'endpoint' => $endpoint,
            'has_app_token' => !empty($credentials->app_token),
            'has_user_token' => !empty($credentials->access_token)
        ]);

        $response = Http::withHeaders($this->buildHeaders($credentials))->$method($this->baseUrl . $endpoint);

        \Log::info('Akahu API response', ['status' => $response->status()]);

        if ($response->status() === 401 && $credentials->refresh_token) {
            $this->refreshCredentials($credentials);

            // Rebuild headers so the retry uses the refreshed token
            $response = Http::withHeaders($this->buildHeaders($credentials))->$method($this->baseUrl . $endpoint);
        }

        if (!$response->successful()) {
            \Log::warning('Akahu API request failed', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 500),
            ]);
        }

        return $response;
    }

    private function buildHeaders(AkahuCredential $credentials): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $credentials->access_token,
        ];

        // Manual (app) tokens require the X-Akahu-ID header alongside the user token
        if ($credentials->app_token) {
            $headers['X-Akahu-ID'] = $credentials->app_token;
        }

        return $headers;
    }

    private function refreshCredentials(AkahuCredential $credentials): void
    {
        $tokenData = $this->refreshToken($credentials->refresh_token);

        $credentials->update([
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'] ?? $credentials->refresh_token,
            'expires_at' => now()->addSeconds($tokenData['expires_in']),
        ]);
    }
}