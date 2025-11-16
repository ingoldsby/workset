<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Notification Preferences') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Manage how and when you receive notifications.') }}
        </p>
    </header>

    <form wire:submit="save" class="mt-6 space-y-6">
        @if(session('notification-preferences-saved'))
            <div class="bg-green-50 border border-green-200 rounded-md p-4">
                <p class="text-sm text-green-800">{{ session('notification-preferences-saved') }}</p>
            </div>
        @endif

        <!-- Session Reminders -->
        <div class="flex items-start">
            <div class="flex items-center h-5">
                <input
                    type="checkbox"
                    wire:model="sessionReminders"
                    id="session-reminders"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                >
            </div>
            <div class="ml-3">
                <label for="session-reminders" class="font-medium text-gray-700">
                    {{ __('Session Reminders') }}
                </label>
                <p class="text-sm text-gray-600">
                    {{ __('Receive push notifications before scheduled training sessions.') }}
                </p>
            </div>
        </div>

        @if(Auth::user()->isPt() || Auth::user()->isAdmin())
            <!-- PT Activity Alerts -->
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input
                        type="checkbox"
                        wire:model="ptActivityAlerts"
                        id="pt-activity-alerts"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >
                </div>
                <div class="ml-3">
                    <label for="pt-activity-alerts" class="font-medium text-gray-700">
                        {{ __('Athlete Activity Alerts') }}
                    </label>
                    <p class="text-sm text-gray-600">
                        {{ __('Receive notifications when your athletes complete sessions.') }}
                    </p>
                </div>
            </div>

            <!-- PT Daily Digest -->
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input
                        type="checkbox"
                        wire:model="dailyDigest"
                        id="daily-digest"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >
                </div>
                <div class="ml-3">
                    <label for="daily-digest" class="font-medium text-gray-700">
                        {{ __('Daily Athlete Summary') }}
                    </label>
                    <p class="text-sm text-gray-600">
                        {{ __('Receive a daily summary of athlete activity at 8:00 PM local time.') }}
                    </p>
                </div>
            </div>
        @endif

        <!-- Weekly Digest -->
        <div class="space-y-3">
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input
                        type="checkbox"
                        wire:model.live="weeklyDigest"
                        id="weekly-digest"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >
                </div>
                <div class="ml-3">
                    <label for="weekly-digest" class="font-medium text-gray-700">
                        {{ __('Weekly Training Summary') }}
                    </label>
                    <p class="text-sm text-gray-600">
                        {{ __('Receive a weekly summary of your training progress.') }}
                    </p>
                </div>
            </div>

            @if($weeklyDigest)
                <div class="ml-8">
                    <label for="weekly-digest-day" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('Delivery Day') }}
                    </label>
                    <select
                        wire:model="weeklyDigestDay"
                        id="weekly-digest-day"
                        class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                    >
                        <option value="sunday">{{ __('Sunday') }}</option>
                        <option value="monday">{{ __('Monday') }}</option>
                        <option value="tuesday">{{ __('Tuesday') }}</option>
                        <option value="wednesday">{{ __('Wednesday') }}</option>
                        <option value="thursday">{{ __('Thursday') }}</option>
                        <option value="friday">{{ __('Friday') }}</option>
                        <option value="saturday">{{ __('Saturday') }}</option>
                    </select>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button
                type="submit"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
                {{ __('Save Preferences') }}
            </button>
        </div>
    </form>
</section>
