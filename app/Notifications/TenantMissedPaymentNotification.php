<?php

namespace App\Notifications;

use App\Models\Property;
use App\Models\RentCheck;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantMissedPaymentNotification extends Notification
{
    use Queueable;

    public Property $property;
    public RentCheck $rentCheck;

    /**
     * Create a new notification instance.
     */
    public function __construct(Property $property, RentCheck $rentCheck)
    {
        $this->property = $property;
        $this->rentCheck = $rentCheck;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [\App\Channels\MailtrapChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $daysPast = now()->diffInDays($this->rentCheck->due_date);
        $dueDate = $this->rentCheck->due_date->format('F j, Y');
        $amount = $this->rentCheck->expected_amount;

        $message = (new MailMessage)
            ->subject('Rent Payment Reminder - ' . $this->property->name)
            ->greeting('Hello,')
            ->line('This is a reminder that your rent payment for **' . $this->property->name . '** is overdue.');

        $message->line('**Payment Details:**')
                ->line('Due Date: ' . $dueDate)
                ->line('Amount Due: $' . number_format($amount, 2))
                ->line('Days Overdue: ' . $daysPast);

        // If there's a partial payment
        if ($this->rentCheck->status === 'partial' && $this->rentCheck->received_amount > 0) {
            $remaining = $amount - $this->rentCheck->received_amount;
            $message->line('Amount Received: $' . number_format($this->rentCheck->received_amount, 2))
                    ->line('**Remaining Balance: $' . number_format($remaining, 2) . '**');
        }

        $message->line('Please arrange payment as soon as possible to avoid any late fees or further action.')
                ->line('If you have already made this payment, please disregard this notice.')
                ->line('Thank you for your attention to this matter.');

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
            'property_id' => $this->property->id,
            'property_name' => $this->property->name,
            'rent_check_id' => $this->rentCheck->id,
            'due_date' => $this->rentCheck->due_date,
            'expected_amount' => $this->rentCheck->expected_amount,
            'status' => $this->rentCheck->status,
        ];
    }
}
