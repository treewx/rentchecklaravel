<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailtrapService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.mailtrap.api_key');
    }

    /**
     * Send an email via Mailtrap Sending API (production)
     */
    public function send(string $to, string $toName, string $subject, string $textBody, string $htmlBody = null): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("https://send.api.mailtrap.io/api/send", [
                'from' => [
                    'email' => config('mail.from.address', 'noreply@renttracker.com'),
                    'name' => config('mail.from.name', 'Rent Tracker'),
                ],
                'to' => [
                    [
                        'email' => $to,
                        'name' => $toName,
                    ]
                ],
                'subject' => $subject,
                'text' => $textBody,
                'html' => $htmlBody ?? $textBody,
            ]);

            if ($response->successful()) {
                Log::info('Mailtrap email sent successfully', [
                    'to' => $to,
                    'subject' => $subject,
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::error('Mailtrap API error', [
                    'to' => $to,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Mailtrap send failed', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
