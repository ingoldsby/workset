<div class="space-y-6">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">
                {{ __('Training History') }}
            </h3>

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Search') }}</label>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="{{ __('Search exercises...') }}"
                        class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('From') }}</label>
                    <input
                        type="date"
                        wire:model.live="dateFrom"
                        class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('To') }}</label>
                    <input
                        type="date"
                        wire:model.live="dateTo"
                        class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Status') }}</label>
                    <div class="space-y-2">
                        <label class="inline-flex items-center">
                            <input
                                type="checkbox"
                                wire:model.live="showCompleted"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            >
                            <span class="ml-2 text-sm text-gray-700">{{ __('Completed') }}</span>
                        </label>
                        <label class="inline-flex items-center ml-4">
                            <input
                                type="checkbox"
                                wire:model.live="showIncomplete"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            >
                            <span class="ml-2 text-sm text-gray-700">{{ __('Incomplete') }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Session List -->
            @if($sessions->isEmpty())
                <div class="text-center py-12 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="mt-4">{{ __('No sessions found.') }}</p>
                    <p class="text-sm mt-2">{{ __('Start logging your workouts to see them here.') }}</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($sessions as $session)
                        <div
                            wire:click="viewSession('{{ $session->id }}')"
                            class="cursor-pointer border border-gray-200 rounded-lg p-4 hover:border-blue-500 hover:shadow-sm transition"
                        >
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <div class="font-semibold text-gray-900">
                                        {{ $session->started_at->format('l, j F Y') }}
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        {{ $session->started_at->format('g:i A') }}
                                        @if($session->completed_at)
                                            - {{ $session->completed_at->format('g:i A') }}
                                            ({{ $session->started_at->diffInMinutes($session->completed_at) }} min)
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    @if($session->completed_at)
                                        <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 rounded text-xs">
                                            {{ __('Completed') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">
                                            {{ __('In Progress') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="text-sm text-gray-600">
                                {{ $session->sessionSets->groupBy(fn($set) => $set->exercise_id ?? $set->member_exercise_id)->count() }} exercises,
                                {{ $session->sessionSets->count() }} total sets
                            </div>

                            @if($session->sessionSets->isNotEmpty())
                                <div class="mt-2 text-xs text-gray-500">
                                    {{ $session->sessionSets->unique(fn($set) => $set->exercise_id ?? $set->member_exercise_id)->take(3)->map(fn($set) => $set->exercise?->name ?? $set->memberExercise?->name)->implode(', ') }}
                                    @if($session->sessionSets->unique(fn($set) => $set->exercise_id ?? $set->member_exercise_id)->count() > 3)
                                        {{ __('and more...') }}
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $sessions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
