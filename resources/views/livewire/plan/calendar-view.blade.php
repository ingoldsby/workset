<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-gray-900">{{ $monthName }}</h3>
            <div class="flex space-x-2">
                <button
                    wire:click="previousMonth"
                    class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button
                    wire:click="goToToday"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50"
                >
                    {{ __('Today') }}
                </button>
                <button
                    wire:click="nextMonth"
                    class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-7 gap-px bg-gray-200 border border-gray-200">
            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div class="bg-gray-50 p-2 text-center text-xs font-semibold text-gray-700">
                    {{ __($day) }}
                </div>
            @endforeach

            @foreach($calendarDays as $day)
                <div class="bg-white min-h-24 p-2 {{ !$day['isCurrentMonth'] ? 'bg-gray-50' : '' }} {{ $day['isToday'] ? 'ring-2 ring-blue-500' : '' }}">
                    <div class="text-sm {{ !$day['isCurrentMonth'] ? 'text-gray-400' : 'text-gray-900' }} {{ $day['isToday'] ? 'font-bold text-blue-600' : '' }}">
                        {{ $day['date']->day }}
                    </div>
                    <div class="mt-1 space-y-1">
                        @foreach($day['sessions'] as $session)
                            <div class="text-xs p-1 bg-blue-100 text-blue-800 rounded truncate" title="{{ $session->programDay?->name ?? __('Ad-hoc session') }}">
                                {{ $session->programDay?->name ?? __('Session') }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        @if($canManageSchedule)
            <div class="mt-4 text-sm text-gray-600">
                <p>{{ __('Drag and drop functionality for rescheduling sessions will be available soon.') }}</p>
            </div>
        @endif
    </div>
</div>
