<div class="space-y-6">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ __('Exercise Library') }}
                </h3>
                <button
                    wire:click="createCustomExercise"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('Create Custom Exercise') }}
                </button>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8">
                    <button
                        wire:click="setTab('global')"
                        class="border-transparent {{ $tab === 'global' ? 'border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                    >
                        {{ __('Global Library') }}
                    </button>
                    <button
                        wire:click="setTab('custom')"
                        class="border-transparent {{ $tab === 'custom' ? 'border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                    >
                        {{ __('My Exercises') }}
                    </button>
                    <button
                        wire:click="setTab('recent')"
                        class="border-transparent {{ $tab === 'recent' ? 'border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                    >
                        {{ __('Recent') }}
                    </button>
                </nav>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="{{ __('Search exercises...') }}"
                        class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                    >
                </div>
                <div>
                    <select
                        wire:model.live="muscleGroupFilter"
                        class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                    >
                        <option value="">{{ __('All Muscle Groups') }}</option>
                        @foreach($muscleGroups as $muscleGroup)
                            <option value="{{ $muscleGroup->value }}">{{ $muscleGroup->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select
                        wire:model.live="equipmentFilter"
                        class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                    >
                        <option value="">{{ __('All Equipment') }}</option>
                        @foreach($equipmentTypes as $equipmentType)
                            <option value="{{ $equipmentType->value }}">{{ $equipmentType->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Exercise List -->
            @if($exercises->isEmpty())
                <div class="text-center py-12 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    <p class="mt-4">{{ __('No exercises found.') }}</p>
                </div>
            @else
                <div class="grid gap-3">
                    @foreach($exercises as $exercise)
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 hover:shadow-sm transition">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900">{{ $exercise->name }}</h4>
                                    <div class="mt-1 flex flex-wrap gap-2 text-xs">
                                        @if($exercise->primary_muscle)
                                            <span class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-800 rounded">
                                                {{ $exercise->primary_muscle->label() }}
                                            </span>
                                        @endif
                                        @if($exercise->equipment)
                                            <span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-600 rounded">
                                                {{ $exercise->equipment->label() }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
