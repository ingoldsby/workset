<?php

namespace App\Notifications;

use App\Models\TrainingSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class PtActivityAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private TrainingSession $session,
        private string $activityType, // 'session_completed', 'session_logged_on_behalf'
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];

        // Only send push if PT has enabled activity alerts
        if ($notifiable->notification_preferences['pt_activity_alerts'] ?? false) {
            $channels[] = WebPushChannel::class;
        }

        // Also send email for significant activities
        if ($this->activityType === 'session_logged_on_behalf') {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toWebPush(object $notifiable): WebPushMessage
    {
        $message = match ($this->activityType) {
            'session_completed' => __(':athlete completed a session', [
                'athlete' => $this->session->user->name,
            ]),
            'session_logged_on_behalf' => __(':athlete had a session logged by their PT', [
                'athlete' => $this->session->user->name,
            ]),
            default => __('New activity from :athlete', [
                'athlete' => $this->session->user->name,
            ]),
        };

        return (new WebPushMessage)
            ->title(__('Athlete Activity'))
            ->body($message)
            ->icon('/images/icons/icon-192x192.png')
            ->badge('/images/icons/badge-72x72.png')
            ->tag('pt-activity-' . $this->session->id)
            ->data([
                'url' => route('pt.index'),
                'session_id' => $this->session->id,
            ])
            ->action(__('View'), 'view')
            ->action(__('Dismiss'), 'dismiss');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Session Logged on Behalf of :athlete', [
                'athlete' => $this->session->user->name,
            ]))
            ->greeting(__('Hello :name!', ['name' => $notifiable->name]))
            ->line(__('A training session was logged on behalf of :athlete.', [
                'athlete' => $this->session->user->name,
            ]))
            ->line(__('Session details:'))
            ->line('• ' . __('Date: :date', ['date' => $this->session->started_at->format('l, j F Y')]))
            ->line('• ' . __('Exercises: :count', [
                'count' => $this->session->sessionSets->groupBy(fn($set) => $set->exercise_id ?? $set->member_exercise_id)->count(),
            ]))
            ->line('• ' . __('Sets: :count', ['count' => $this->session->sessionSets->count()]))
            ->action(__('View Session'), route('pt.index'));
    }
}
