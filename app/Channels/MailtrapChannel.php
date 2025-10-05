<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailtrapChannel
{
    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        // Get the mail message from the notification
        $message = $notification->toMail($notifiable);

        // Build the email content
        $html = $this->buildHtmlFromMailMessage($message);
        $text = $this->buildTextFromMailMessage($message);

        // Send via Mailtrap HTTP API
        $apiKey = env('MAILTRAP_API_KEY');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://send.api.mailtrap.io/api/send', [
            'from' => [
                'email' => config('mail.from.address'),
                'name' => config('mail.from.name'),
            ],
            'to' => [
                [
                    'email' => $notifiable->email,
                    'name' => $notifiable->name ?? 'User',
                ]
            ],
            'subject' => $message->subject,
            'text' => $text,
            'html' => $html,
        ]);

        if (!$response->successful()) {
            Log::error('Mailtrap API error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'to' => $notifiable->email
            ]);

            throw new \Exception('Failed to send email via Mailtrap: ' . $response->body());
        }
    }

    /**
     * Build HTML content from MailMessage
     */
    protected function buildHtmlFromMailMessage($message): string
    {
        $html = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">';

        if (!empty($message->greeting)) {
            $html .= '<h2>' . e($message->greeting) . '</h2>';
        }

        foreach ($message->introLines as $line) {
            $html .= '<p>' . $this->formatLine($line) . '</p>';
        }

        if (isset($message->actionText) && isset($message->actionUrl)) {
            $html .= '<div style="text-align: center; margin: 30px 0;">';
            $html .= '<a href="' . e($message->actionUrl) . '" style="background-color: #3490dc; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block;">';
            $html .= e($message->actionText);
            $html .= '</a>';
            $html .= '</div>';
        }

        foreach ($message->outroLines as $line) {
            $html .= '<p>' . $this->formatLine($line) . '</p>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Build plain text content from MailMessage
     */
    protected function buildTextFromMailMessage($message): string
    {
        $text = '';

        if (!empty($message->greeting)) {
            $text .= $message->greeting . "\n\n";
        }

        foreach ($message->introLines as $line) {
            $text .= strip_tags($line) . "\n\n";
        }

        if (isset($message->actionText) && isset($message->actionUrl)) {
            $text .= $message->actionText . ": " . $message->actionUrl . "\n\n";
        }

        foreach ($message->outroLines as $line) {
            $text .= strip_tags($line) . "\n\n";
        }

        return trim($text);
    }

    /**
     * Format a line of text, converting markdown-style formatting to HTML
     */
    protected function formatLine(string $line): string
    {
        // Convert **bold** to <strong>
        $line = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $line);

        // Convert ## headings to <h3>
        if (preg_match('/^##\s+(.+)$/', $line, $matches)) {
            return '<h3>' . e($matches[1]) . '</h3>';
        }

        return $line;
    }
}
