<?php

namespace App\Notifications;

use App\Models\PickupRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BinReadyForPickup extends Notification
{
    use Queueable;

    public function __construct(public PickupRequest $pickupRequest) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $bin = $this->pickupRequest->bin;
        $outlet = $bin->currentAssignment?->outlet;

        return (new MailMessage)
            ->subject("Pickup Needed: Bin {$bin->serial_number} at {$bin->fill_level}%")
            ->greeting("Hi {$notifiable->name},")
            ->line('A bin is ready for pickup and needs a collector.')
            ->line("**Bin:** {$bin->serial_number}")
            ->line("**Fill Level:** {$bin->fill_level}%")
            ->line('**Outlet:** '.($outlet ? "{$outlet->name} — {$outlet->address}" : 'Unassigned'))
            ->action('View Collector Dashboard', route('collector.dashboard'))
            ->line('Claim this pickup before another collector does!');
    }
}
