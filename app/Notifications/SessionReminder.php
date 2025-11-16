<?php

namespace App\Notifications;

use App\Models\SessionPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class SessionReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private SessionPlan $sessionPlan,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];

        // Only send push if user has enabled session reminders
        if ($notifiable->notification_preferences['session_reminders'] ?? false) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    public function toWebPush(object $notifiable): WebPushMessage
    {
        $sessionName = $this->sessionPlan->programDay?->name ?? __('Training Session');

        return (new WebPushMessage)
            ->title(__('Training Reminder'))
            ->body(__('You have a session scheduled: :name', ['name' => $sessionName]))
            ->icon('/images/icons/icon-192x192.png')
            ->badge('/images/icons/badge-72x72.png')
            ->tag('session-reminder-' . $this->sessionPlan->id)
            ->data([
                'url' => route('today.index'),
                'session_plan_id' => $this->sessionPlan->id,
            ])
            ->action(__('View Session'), 'view')
            ->action(__('Dismiss'), 'dismiss');
    }
}
