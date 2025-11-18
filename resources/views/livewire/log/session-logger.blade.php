<div class="space-y-6">
    @if($timerActive)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h4 class="font-semibold text-blue-900">{{ __('Rest Timer') }}</h4>
                    <p class="text-2xl font-bold text-blue-600">{{ gmdate('i:s', $restTimerSeconds) }}</p>
                </div>
                <button
                    wire:click="stopRestTimer"
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700"
                >
                    {{ __('Stop') }}
                </button>
            </div>
        </div>
    @endif

    @if($session)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ __('Current Session') }}
                    </h3>
                    <div class="text-sm text-gray-600">
                        {{ __('Started') }}: {{ $session->started_at->format('g:i A') }}
                    </div>
                </div>

                @if(empty($exercises))
                    <div class="text-center py-8 text-gray-500">
                        <p>{{ __('No exercises logged yet.') }}</p>
                        <p class="text-sm mt-2">{{ __('Add an exercise to get started.') }}</p>
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach($exercises as $index => $exercise)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-900 mb-3">{{ $exercise['name'] }}</h4>

                                @if(count($exercise['sets']) > 0)
                                    <div class="space-y-2 mb-3">
                                        @foreach($exercise['sets'] as $set)
                                            <div class="flex items-center justify-between bg-gray-50 p-2 rounded">
                                                <span class="text-sm text-gray-600">{{ __('Set') }} {{ $set->set_number }}</span>
                                                <div class="text-sm">
                                                    @if($set->weight_performed)
                                                        <span class="font-medium">{{ $set->weight_performed }}kg</span>
                                                    @endif
                                                    @if($set->reps_performed)
                                                        <span class="ml-2">&times; {{ $set->reps_performed }}</span>
                                                    @endif
                                                    @if($set->rpe_performed)
                                                        <span class="ml-2 text-gray-500">RPE {{ $set->rpe_performed }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <button
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                                >
                                    {{ __('Add Set') }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="mt-6 flex space-x-3">
                    <button
                        wire:click="openExerciseSelector"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                    >
                        {{ __('Add Exercise') }}
                    </button>

                    @if(!empty($exercises))
                        <button
                            wire:click="completeSession"
                            class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700"
                        >
                            {{ __('Complete Session') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-center">
                <p class="text-gray-500 mb-4">{{ __('No active session.') }}</p>
                <a
                    href="{{ route('today.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                >
                    {{ __('Go to Today') }}
                </a>
            </div>
        </div>
    @endif

    {{-- Exercise Selector Modal --}}
    @if($showExerciseSelector)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Background overlay --}}
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeExerciseSelector"></div>

                {{-- Modal panel --}}
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Select Exercise') }}</h3>
                            <button wire:click="closeExerciseSelector" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Search --}}
                        <div class="mb-4">
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="exerciseSearch"
                                placeholder="{{ __('Search exercises...') }}"
                                class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            >
                        </div>

                        {{-- Exercise List --}}
                        <div class="max-h-96 overflow-y-auto">
                            @if(empty($availableExercises))
                                <div class="text-center py-8 text-gray-500">
                                    <p>{{ __('No exercises found.') }}</p>
                                </div>
                            @else
                                <div class="space-y-2">
                                    @foreach($availableExercises as $exercise)
                                        <button
                                            wire:click="selectExercise('{{ $exercise['id'] }}', '{{ $exercise['type'] }}')"
                                            class="w-full text-left px-4 py-3 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition"
                                        >
                                            <div class="font-medium text-gray-900">{{ $exercise['name'] }}</div>
                                            <div class="text-xs text-gray-500">{{ $exercise['type'] === 'member' ? __('My Exercise') : __('Global Library') }}</div>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
