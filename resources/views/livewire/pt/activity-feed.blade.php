<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">
            {{ __('Recent Activity') }}
        </h3>

        @if($activities->isEmpty())
            <div class="text-center py-12 text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <p class="mt-4">{{ __('No recent activity.') }}</p>
                <p class="text-sm mt-2">{{ __("Your athletes' recent workouts will appear here.") }}</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($activities as $activity)
                    <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900">
                                <span class="font-semibold">{{ $activity['user']->name }}</span>
                                {{ __('completed a workout') }}
                            </p>
                            <div class="mt-1 text-xs text-gray-600">
                                {{ $activity['data']['exercises'] }} exercises &middot;
                                {{ $activity['data']['sets'] }} sets &middot;
                                {{ $activity['data']['duration'] }} minutes
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $activity['timestamp']->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
