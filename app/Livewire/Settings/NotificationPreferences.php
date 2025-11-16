<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class NotificationPreferences extends Component
{
    public bool $sessionReminders = false;
    public bool $ptActivityAlerts = false;
    public bool $weeklyDigest = false;
    public string $weeklyDigestDay = 'sunday';
    public bool $dailyDigest = false;

    public function mount(): void
    {
        $preferences = Auth::user()->notification_preferences ?? [];

        $this->sessionReminders = $preferences['session_reminders'] ?? false;
        $this->ptActivityAlerts = $preferences['pt_activity_alerts'] ?? false;
        $this->weeklyDigest = $preferences['weekly_digest'] ?? false;
        $this->weeklyDigestDay = $preferences['weekly_digest_day'] ?? 'sunday';
        $this->dailyDigest = $preferences['daily_digest'] ?? false;
    }

    public function save(): void
    {
        $preferences = [
            'session_reminders' => $this->sessionReminders,
            'pt_activity_alerts' => $this->ptActivityAlerts,
            'weekly_digest' => $this->weeklyDigest,
            'weekly_digest_day' => $this->weeklyDigestDay,
            'daily_digest' => $this->dailyDigest,
        ];

        Auth::user()->update([
            'notification_preferences' => $preferences,
        ]);

        session()->flash('notification-preferences-saved', __('Notification preferences updated successfully.'));
    }

    public function render(): View
    {
        return view('livewire.settings.notification-preferences');
    }
}
