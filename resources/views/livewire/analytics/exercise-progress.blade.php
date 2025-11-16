<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">
            {{ __('Exercise Progress') }}
        </h3>

        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-semibold text-gray-900 mb-3">{{ __('Top Exercises') }}</h4>
                @if($topExercises->isEmpty())
                    <p class="text-sm text-gray-500">{{ __('No exercise data available yet.') }}</p>
                @else
                    <div class="space-y-2">
                        @foreach($topExercises as $exercise)
                            <div
                                wire:click="selectExercise('{{ $exercise['id'] }}')"
                                class="cursor-pointer flex justify-between items-center p-2 rounded hover:bg-gray-50 {{ $selectedExerciseId === $exercise['id'] ? 'bg-blue-50' : '' }}"
                            >
                                <span class="text-sm">{{ $exercise['name'] }}</span>
                                <span class="text-xs text-gray-500">{{ $exercise['setCount'] }} sets</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <h4 class="font-semibold text-gray-900 mb-3">{{ __('Personal Records') }}</h4>
                @if(!$selectedExerciseId)
                    <p class="text-sm text-gray-500">{{ __('Select an exercise to view records.') }}</p>
                @elseif($personalRecords->isEmpty())
                    <p class="text-sm text-gray-500">{{ __('No records found for this exercise.') }}</p>
                @else
                    <div class="space-y-2">
                        @foreach($personalRecords as $record)
                            <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                <div>
                                    <span class="font-semibold">{{ $record->weight_performed }}kg</span>
                                    <span class="text-sm text-gray-600"> &times; {{ $record->reps_performed }}</span>
                                </div>
                                <span class="text-xs text-gray-500">
                                    {{ $record->trainingSession->started_at->format('M j, Y') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
