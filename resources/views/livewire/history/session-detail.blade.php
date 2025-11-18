<div>
    @if($session)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                {{-- Session Header --}}
                <div class="mb-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">
                                {{ __('Training Session') }}
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ $session->scheduled_date->format('l, F j, Y') }}
                            </p>
                        </div>
                        <div class="text-right">
                            @if($session->isCompleted())
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    {{ __('Completed') }}
                                </span>
                            @elseif($session->isInProgress())
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    {{ __('In Progress') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                    {{ __('Pending') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">{{ __('Started') }}:</span>
                            <span class="font-medium text-gray-900">{{ $session->started_at->format('g:i A') }}</span>
                        </div>
                        @if($session->completed_at)
                            <div>
                                <span class="text-gray-600">{{ __('Completed') }}:</span>
                                <span class="font-medium text-gray-900">{{ $session->completed_at->format('g:i A') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">{{ __('Duration') }}:</span>
                                <span class="font-medium text-gray-900">
                                    {{ $session->started_at->diffForHumans($session->completed_at, true) }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Exercises --}}
                @if($session->exercises->count() > 0)
                    <div class="space-y-6">
                        <h4 class="text-lg font-semibold text-gray-900">{{ __('Exercises') }}</h4>

                        @foreach($session->exercises as $sessionExercise)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h5 class="font-semibold text-gray-900 mb-3">
                                    {{ $sessionExercise->getExerciseName() }}
                                </h5>

                                @if($sessionExercise->sets->count() > 0)
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Set') }}</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Weight') }}</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Reps') }}</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('RPE') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($sessionExercise->sets as $set)
                                                    <tr>
                                                        <td class="px-3 py-2 text-sm text-gray-900">{{ $set->set_number }}</td>
                                                        <td class="px-3 py-2 text-sm text-gray-900">
                                                            @if($set->performed_weight)
                                                                {{ $set->performed_weight }}kg
                                                            @else
                                                                <span class="text-gray-400">-</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-2 text-sm text-gray-900">
                                                            @if($set->performed_reps)
                                                                {{ $set->performed_reps }}
                                                            @else
                                                                <span class="text-gray-400">-</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-2 text-sm text-gray-900">
                                                            @if($set->performed_rpe)
                                                                {{ $set->performed_rpe }}
                                                            @else
                                                                <span class="text-gray-400">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500">{{ __('No sets logged') }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <p>{{ __('No exercises logged in this session') }}</p>
                    </div>
                @endif

                {{-- Notes --}}
                @if($session->notes)
                    <div class="mt-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ __('Notes') }}</h4>
                        <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700">
                            {{ $session->notes }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-center text-gray-500">
                <p>{{ __('Session not found') }}</p>
            </div>
        </div>
    @endif
</div>
