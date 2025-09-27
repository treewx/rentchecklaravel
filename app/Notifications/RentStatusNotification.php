<?php

namespace App\Notifications;

use App\Models\Property;
use App\Models\RentCheck;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RentStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public array $rentCheckResults;
    public string $checkDate;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $rentCheckResults, string $checkDate)
    {
        $this->rentCheckResults = $rentCheckResults;
        $this->checkDate = $checkDate;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $receivedCount = count($this->rentCheckResults['received'] ?? []);
        $lateCount = count($this->rentCheckResults['late'] ?? []);
        $partialCount = count($this->rentCheckResults['partial'] ?? []);
        $totalChecked = $receivedCount + $lateCount + $partialCount;

        $message = (new MailMessage)
            ->subject('Rent Status Update - ' . $this->checkDate)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Here\'s your rent collection update for ' . $this->checkDate . ':');

        // Summary line
        if ($totalChecked === 0) {
            $message->line('No rent checks were due today.');
            return $message;
        }

        $message->line("**Summary**: {$totalChecked} properties checked");

        // Rent received section
        if ($receivedCount > 0) {
            $message->line('## ✅ Rent Received (' . $receivedCount . ')');
            foreach ($this->rentCheckResults['received'] as $result) {
                $property = $result['property'];
                $rentCheck = $result['rent_check'];
                $message->line("**{$property->name}**: \${$rentCheck->received_amount} received");
            }
        }

        // Partial payments section
        if ($partialCount > 0) {
            $message->line('## ⚠️ Partial Payments (' . $partialCount . ')');
            foreach ($this->rentCheckResults['partial'] as $result) {
                $property = $result['property'];
                $rentCheck = $result['rent_check'];
                $expected = $rentCheck->expected_amount;
                $received = $rentCheck->received_amount;
                $message->line("**{$property->name}**: \${$received} of \${$expected} received");
            }
        }

        // Late payments section
        if ($lateCount > 0) {
            $message->line('## ❌ Late Payments (' . $lateCount . ')');
            foreach ($this->rentCheckResults['late'] as $result) {
                $property = $result['property'];
                $rentCheck = $result['rent_check'];
                $daysPast = now()->diffInDays($rentCheck->due_date);
                $message->line("**{$property->name}**: \${$rentCheck->expected_amount} - {$daysPast} days overdue");
            }
        }

        $message->action('View Properties', url('/properties'))
                ->line('Thank you for using Rent Tracker!');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'check_date' => $this->checkDate,
            'results' => $this->rentCheckResults,
        ];
    }
}
