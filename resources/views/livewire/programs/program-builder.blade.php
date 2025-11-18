<div class="space-y-6">
    {{-- Program Metadata --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                {{ __('Program Details') }}
            </h3>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        {{ __('Program Name') }}
                    </label>
                    <input
                        type="text"
                        id="name"
                        wire:model="name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="{{ __('e.g. 12 Week Strength Program') }}"
                    >
                    @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">
                        {{ __('Category') }}
                    </label>
                    <input
                        type="text"
                        id="category"
                        wire:model="category"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="{{ __('e.g. Strength, Hypertrophy, Conditioning') }}"
                    >
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">
                        {{ __('Description') }}
                    </label>
                    <textarea
                        id="description"
                        wire:model="description"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="{{ __('Describe your program...') }}"
                    ></textarea>
                    @error('description') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="visibility" class="block text-sm font-medium text-gray-700">
                        {{ __('Visibility') }}
                    </label>
                    <select
                        id="visibility"
                        wire:model="visibility"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="private">{{ __('Private') }}</option>
                        <option value="public">{{ __('Public') }}</option>
                    </select>
                </div>

                <div class="flex items-center">
                    <label class="flex items-center">
                        <input
                            type="checkbox"
                            wire:model="isTemplate"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        <span class="ml-2 text-sm text-gray-700">{{ __('Save as template') }}</span>
                    </label>
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <button
                    wire:click="saveProgram"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                >
                    {{ __('Save Program Details') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Program Days --}}
    @if($program)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ __('Workout Days') }}
                    </h3>
                    <button
                        wire:click="openAddDayModal"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('Add Day') }}
                    </button>
                </div>

                @if($days->isEmpty())
                    <div class="text-center py-12 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="mt-4">{{ __('No workout days yet.') }}</p>
                        <p class="text-sm mt-2">{{ __('Add your first workout day to get started.') }}</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($days as $day)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded">
                                                {{ __('Day') }} {{ $day->day_number }}
                                            </span>
                                            <h4 class="font-semibold text-gray-900">{{ $day->name }}</h4>
                                        </div>
                                        @if($day->description)
                                            <p class="text-sm text-gray-600 mt-1">{{ $day->description }}</p>
                                        @endif
                                        @if($day->rest_days_after > 0)
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ __('Rest days after: :count', ['count' => $day->rest_days_after]) }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-1">
                                        <button
                                            wire:click="moveDay({{ $day->id }}, 'up')"
                                            class="p-1 text-gray-400 hover:text-gray-600"
                                            title="{{ __('Move up') }}"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                            </svg>
                                        </button>
                                        <button
                                            wire:click="moveDay({{ $day->id }}, 'down')"
                                            class="p-1 text-gray-400 hover:text-gray-600"
                                            title="{{ __('Move down') }}"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                        <button
                                            wire:click="openEditDayModal({{ $day->id }})"
                                            class="p-1 text-gray-400 hover:text-blue-600"
                                            title="{{ __('Edit day') }}"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button
                                            wire:click="deleteDay({{ $day->id }})"
                                            wire:confirm="{{ __('Are you sure you want to delete this day?') }}"
                                            class="p-1 text-gray-400 hover:text-red-600"
                                            title="{{ __('Delete day') }}"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- Exercises --}}
                                <div class="mt-3 space-y-2">
                                    @if($day->exercises->isEmpty())
                                        <div class="text-center py-4 text-gray-400 text-sm border-2 border-dashed border-gray-200 rounded">
                                            {{ __('No exercises yet.') }}
                                        </div>
                                    @else
                                        @foreach($day->exercises->sortBy('order') as $programExercise)
                                            <div class="flex items-center justify-between bg-gray-50 rounded p-3">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2">
                                                        @if($programExercise->superset_group)
                                                            <span class="inline-flex items-center px-1.5 py-0.5 bg-purple-100 text-purple-700 text-xs font-medium rounded">
                                                                {{ __('SS') }} {{ $programExercise->superset_group }}
                                                            </span>
                                                        @endif
                                                        <span class="font-medium text-gray-900">{{ $programExercise->exercise->name }}</span>
                                                    </div>
                                                    <div class="flex gap-3 mt-1 text-sm text-gray-600">
                                                        <span>{{ $programExercise->sets }} {{ __('sets') }}</span>
                                                        @if($programExercise->reps_min && $programExercise->reps_max)
                                                            <span>{{ $programExercise->reps_min }}-{{ $programExercise->reps_max }} {{ __('reps') }}</span>
                                                        @elseif($programExercise->reps_min)
                                                            <span>{{ $programExercise->reps_min }} {{ __('reps') }}</span>
                                                        @endif
                                                        @if($programExercise->rpe)
                                                            <span>{{ __('RPE') }} {{ $programExercise->rpe }}</span>
                                                        @endif
                                                        @if($programExercise->rest_seconds)
                                                            <span>{{ $programExercise->rest_seconds }}{{ __('s rest') }}</span>
                                                        @endif
                                                        @if($programExercise->tempo)
                                                            <span>{{ __('Tempo:') }} {{ $programExercise->tempo }}</span>
                                                        @endif
                                                    </div>
                                                    @if($programExercise->notes)
                                                        <p class="text-xs text-gray-500 mt-1">{{ $programExercise->notes }}</p>
                                                    @endif
                                                </div>

                                                <div class="flex items-center gap-1">
                                                    <button
                                                        wire:click="moveExercise({{ $programExercise->id }}, 'up')"
                                                        class="p-1 text-gray-400 hover:text-gray-600"
                                                        title="{{ __('Move up') }}"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                                        </svg>
                                                    </button>
                                                    <button
                                                        wire:click="moveExercise({{ $programExercise->id }}, 'down')"
                                                        class="p-1 text-gray-400 hover:text-gray-600"
                                                        title="{{ __('Move down') }}"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                        </svg>
                                                    </button>
                                                    <button
                                                        wire:click="deleteExercise({{ $programExercise->id }})"
                                                        wire:confirm="{{ __('Are you sure you want to remove this exercise?') }}"
                                                        class="p-1 text-gray-400 hover:text-red-600"
                                                        title="{{ __('Remove exercise') }}"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif

                                    <button
                                        wire:click="openAddExerciseModal({{ $day->id }})"
                                        class="w-full py-2 border-2 border-dashed border-gray-300 rounded text-sm text-gray-600 hover:border-blue-500 hover:text-blue-600 transition"
                                    >
                                        + {{ __('Add Exercise') }}
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Add/Edit Day Modal --}}
    @if($showAddDayModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ $editingDayId ? __('Edit Workout Day') : __('Add Workout Day') }}
                </h3>

                <div class="space-y-4">
                    <div>
                        <label for="dayNumber" class="block text-sm font-medium text-gray-700">
                            {{ __('Day Number') }}
                        </label>
                        <input
                            type="number"
                            id="dayNumber"
                            wire:model="dayNumber"
                            min="1"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        @error('dayNumber') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="dayName" class="block text-sm font-medium text-gray-700">
                            {{ __('Day Name') }}
                        </label>
                        <input
                            type="text"
                            id="dayName"
                            wire:model="dayName"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="{{ __('e.g. Upper Body Strength') }}"
                        >
                        @error('dayName') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="dayDescription" class="block text-sm font-medium text-gray-700">
                            {{ __('Description (Optional)') }}
                        </label>
                        <textarea
                            id="dayDescription"
                            wire:model="dayDescription"
                            rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        ></textarea>
                    </div>

                    <div>
                        <label for="restDaysAfter" class="block text-sm font-medium text-gray-700">
                            {{ __('Rest Days After') }}
                        </label>
                        <input
                            type="number"
                            id="restDaysAfter"
                            wire:model="restDaysAfter"
                            min="0"
                            max="7"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        @error('restDaysAfter') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        wire:click="closeDayModal"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                    >
                        {{ __('Cancel') }}
                    </button>
                    <button
                        wire:click="saveDay"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                        {{ __('Save Day') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Add Exercise Modal --}}
    @if($showAddExerciseModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ __('Add Exercise') }}
                </h3>

                <div class="space-y-4">
                    <div>
                        <label for="selectedExerciseId" class="block text-sm font-medium text-gray-700">
                            {{ __('Exercise') }}
                        </label>
                        <select
                            id="selectedExerciseId"
                            wire:model="selectedExerciseId"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">{{ __('Select an exercise') }}</option>
                            @foreach($availableExercises as $exercise)
                                <option value="{{ $exercise->id }}">{{ $exercise->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedExerciseId') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="sets" class="block text-sm font-medium text-gray-700">
                                {{ __('Sets') }}
                            </label>
                            <input
                                type="number"
                                id="sets"
                                wire:model="sets"
                                min="1"
                                max="20"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                            @error('sets') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="repsMin" class="block text-sm font-medium text-gray-700">
                                {{ __('Reps (Min)') }}
                            </label>
                            <input
                                type="number"
                                id="repsMin"
                                wire:model="repsMin"
                                min="1"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        <div>
                            <label for="repsMax" class="block text-sm font-medium text-gray-700">
                                {{ __('Reps (Max)') }}
                            </label>
                            <input
                                type="number"
                                id="repsMax"
                                wire:model="repsMax"
                                min="1"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        <div>
                            <label for="rpe" class="block text-sm font-medium text-gray-700">
                                {{ __('RPE (1-10)') }}
                            </label>
                            <input
                                type="number"
                                id="rpe"
                                wire:model="rpe"
                                min="1"
                                max="10"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        <div>
                            <label for="restSeconds" class="block text-sm font-medium text-gray-700">
                                {{ __('Rest (seconds)') }}
                            </label>
                            <input
                                type="number"
                                id="restSeconds"
                                wire:model="restSeconds"
                                min="0"
                                max="600"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                            @error('restSeconds') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="tempo" class="block text-sm font-medium text-gray-700">
                                {{ __('Tempo') }}
                            </label>
                            <input
                                type="text"
                                id="tempo"
                                wire:model="tempo"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="{{ __('e.g. 3-0-1-0') }}"
                            >
                        </div>

                        <div>
                            <label for="supersetGroup" class="block text-sm font-medium text-gray-700">
                                {{ __('Superset Group') }}
                            </label>
                            <input
                                type="number"
                                id="supersetGroup"
                                wire:model="supersetGroup"
                                min="1"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="{{ __('Optional') }}"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="exerciseNotes" class="block text-sm font-medium text-gray-700">
                            {{ __('Notes') }}
                        </label>
                        <textarea
                            id="exerciseNotes"
                            wire:model="exerciseNotes"
                            rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="{{ __('Any special instructions or notes...') }}"
                        ></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        wire:click="closeExerciseModal"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                    >
                        {{ __('Cancel') }}
                    </button>
                    <button
                        wire:click="saveExercise"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                        {{ __('Add Exercise') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
