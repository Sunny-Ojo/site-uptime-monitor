<?php

namespace App\Notifications;

use App\Models\Monitor;
use App\Enums\SiteStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SiteStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Monitor $monitor,
        public SiteStatus $oldStatus,
        public SiteStatus $newStatus
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusText = $this->newStatus === SiteStatus::UP ? 'back up' : 'down';
        
        return (new MailMessage)
                    ->subject("Monitor Alert: {$this->monitor->url} is {$statusText}")
                    ->line("The monitor for {$this->monitor->url} has changed status.")
                    ->line("Old Status: {$this->oldStatus->label()}")
                    ->line("New Status: {$this->newStatus->label()}")
                    ->action('View Application', url('/'))
                    ->line('Thank you for using our monitoring service!');
    }
}
