<?php

namespace App\Notifications;

use App\Models\PickupRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PickupCompleted extends Notification
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
        $collector = $this->pickupRequest->claimedBy;
        $outlet = $bin->currentAssignment?->outlet;

        return (new MailMessage)
            ->subject("Pickup Completed: Bin {$bin->serial_number}")
            ->greeting("Hi {$notifiable->name},")
            ->line("{$collector->name} has completed the pickup for bin {$bin->serial_number}.")
            ->line('**Bin:** '.$bin->serial_number)
            ->line('**Outlet:** '.($outlet ? $outlet->name : 'Unassigned'))
            ->line('**Collector:** '.$collector->name)
            ->line('**Completed:** '.$this->pickupRequest->completed_at->format('d M Y, H:i'))
            ->line('Fill level has been reset to 0%.')
            ->action('View Pickup Details', route('admin.pickup-requests.show', $this->pickupRequest));
    }
}
