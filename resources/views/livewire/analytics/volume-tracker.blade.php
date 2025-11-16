<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">
            {{ __('Weekly Volume Trend') }}
        </h3>

        <div class="space-y-2">
            @foreach($weeklyVolume as $week)
                <div class="flex items-center">
                    <div class="w-20 text-sm text-gray-600">{{ $week['week'] }}</div>
                    <div class="flex-1 bg-gray-200 rounded-full h-8">
                        <div
                            class="bg-blue-600 h-8 rounded-full flex items-center justify-end pr-2"
                            style="width: {{ $week['volume'] > 0 ? min(($week['volume'] / max(array_column($weeklyVolume, 'volume'))) * 100, 100) : 0 }}%"
                        >
                            @if($week['volume'] > 0)
                                <span class="text-xs text-white font-semibold">{{ number_format($week['volume']) }}kg</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if(empty(array_filter(array_column($weeklyVolume, 'volume'))))
            <div class="text-center py-8 text-gray-500">
                <p>{{ __('No volume data available yet.') }}</p>
                <p class="text-sm mt-2">{{ __('Start logging sessions to see your progress.') }}</p>
            </div>
        @endif
    </div>
</div>
