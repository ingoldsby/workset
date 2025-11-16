<div>
    @if($plannedSession)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ __("Today's Session") }}
                        </h3>
                        @if($plannedSession->programDay)
                            <p class="text-sm text-gray-600">
                                {{ $plannedSession->programDay->name }}
                            </p>
                        @endif
                    </div>
                    <button
                        wire:click="startSession"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        {{ __('Start Session') }}
                    </button>
                </div>

                @if($plannedSession->notes)
                    <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                        <p class="text-sm text-gray-700">{{ $plannedSession->notes }}</p>
                    </div>
                @endif

                <div class="space-y-3">
                    @forelse($plannedSession->sessionExercises as $sessionExercise)
                        <div class="border-l-4 border-blue-500 pl-3 py-2">
                            <div class="font-medium text-gray-900">
                                {{ $sessionExercise->exercise?->name ?? $sessionExercise->memberExercise?->name }}
                            </div>
                            <div class="text-sm text-gray-600">
                                {{ $sessionExercise->sets }} sets
                                @if($sessionExercise->reps_min && $sessionExercise->reps_max)
                                    &times; {{ $sessionExercise->reps_min }}-{{ $sessionExercise->reps_max }} reps
                                @elseif($sessionExercise->reps_min)
                                    &times; {{ $sessionExercise->reps_min }} reps
                                @endif
                                @if($sessionExercise->weight)
                                    @ {{ $sessionExercise->weight }}kg
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">{{ __('No exercises planned for this session.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    @else
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-center text-gray-500">
                <p>{{ __('No session planned for today.') }}</p>
            </div>
        </div>
    @endif
</div>
