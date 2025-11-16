<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900">
                {{ __('Overview') }}
            </h3>
            <div class="flex space-x-2">
                <button
                    wire:click="setPeriod('week')"
                    class="px-3 py-1 text-xs rounded {{ $period === 'week' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}"
                >
                    {{ __('Week') }}
                </button>
                <button
                    wire:click="setPeriod('month')"
                    class="px-3 py-1 text-xs rounded {{ $period === 'month' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}"
                >
                    {{ __('Month') }}
                </button>
                <button
                    wire:click="setPeriod('year')"
                    class="px-3 py-1 text-xs rounded {{ $period === 'year' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}"
                >
                    {{ __('Year') }}
                </button>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="text-sm text-blue-600 mb-1">{{ __('Sessions') }}</div>
                <div class="text-2xl font-bold text-blue-900">{{ $stats['totalSessions'] }}</div>
            </div>
            <div class="bg-green-50 rounded-lg p-4">
                <div class="text-sm text-green-600 mb-1">{{ __('Total Sets') }}</div>
                <div class="text-2xl font-bold text-green-900">{{ $stats['totalSets'] }}</div>
            </div>
            <div class="bg-purple-50 rounded-lg p-4">
                <div class="text-sm text-purple-600 mb-1">{{ __('Total Volume (kg)') }}</div>
                <div class="text-2xl font-bold text-purple-900">{{ number_format($stats['totalVolume']) }}</div>
            </div>
            <div class="bg-orange-50 rounded-lg p-4">
                <div class="text-sm text-orange-600 mb-1">{{ __('Avg Duration (min)') }}</div>
                <div class="text-2xl font-bold text-orange-900">{{ $stats['averageDuration'] }}</div>
            </div>
        </div>
    </div>
</div>
