<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $program->name }}
            </h2>
            <div class="flex gap-3">
                @can('update', $program)
                    <a
                        href="{{ route('programs.edit', $program) }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        {{ __('Edit Program') }}
                    </a>
                @endcan
                <a
                    href="{{ route('programs.index') }}"
                    class="text-sm text-gray-600 hover:text-gray-900 flex items-center"
                >
                    {{ __('Back to Programs') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Program Overview --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $program->name }}</h3>
                            @if($program->description)
                                <p class="text-gray-600 mt-2">{{ $program->description }}</p>
                            @endif
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $program->visibility === 'public' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($program->visibility) }}
                            </span>
                            @if($program->is_template)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    {{ __('Template') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 pt-4 border-t border-gray-200">
                        <div>
                            <span class="text-sm text-gray-500">{{ __('Created by') }}</span>
                            <p class="font-medium">{{ $program->owner->name }}</p>
                        </div>
                        @if($program->category)
                            <div>
                                <span class="text-sm text-gray-500">{{ __('Category') }}</span>
                                <p class="font-medium">{{ $program->category }}</p>
                            </div>
                        @endif
                        <div>
                            <span class="text-sm text-gray-500">{{ __('Created') }}</span>
                            <p class="font-medium">{{ $program->created_at->format('d M Y') }}</p>
                        </div>
                        @if($program->activeVersion)
                            <div>
                                <span class="text-sm text-gray-500">{{ __('Version') }}</span>
                                <p class="font-medium">{{ $program->activeVersion->version_number }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Workout Days --}}
            @if($program->activeVersion)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            {{ __('Workout Days') }}
                        </h3>

                        @if($program->activeVersion->days->isEmpty())
                            <div class="text-center py-12 text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="mt-4">{{ __('No workout days configured yet.') }}</p>
                            </div>
                        @else
                            <div class="space-y-6">
                                @foreach($program->activeVersion->days->sortBy('day_number') as $day)
                                    <div class="border border-gray-200 rounded-lg p-5">
                                        <div class="flex items-start gap-3 mb-4">
                                            <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded">
                                                {{ __('Day') }} {{ $day->day_number }}
                                            </span>
                                            <div class="flex-1">
                                                <h4 class="font-semibold text-gray-900 text-lg">{{ $day->name }}</h4>
                                                @if($day->description)
                                                    <p class="text-sm text-gray-600 mt-1">{{ $day->description }}</p>
                                                @endif
                                                @if($day->rest_days_after > 0)
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        {{ __('Rest :count day(s) after this workout', ['count' => $day->rest_days_after]) }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>

                                        @if($day->exercises->isEmpty())
                                            <p class="text-sm text-gray-500 italic">{{ __('No exercises configured.') }}</p>
                                        @else
                                            <div class="space-y-3">
                                                @foreach($day->exercises->sortBy('order') as $programExercise)
                                                    <div class="bg-gray-50 rounded-lg p-4">
                                                        <div class="flex items-start justify-between">
                                                            <div class="flex-1">
                                                                <div class="flex items-center gap-2">
                                                                    @if($programExercise->superset_group)
                                                                        <span class="inline-flex items-center px-2 py-0.5 bg-purple-100 text-purple-700 text-xs font-medium rounded">
                                                                            {{ __('Superset') }} {{ $programExercise->superset_group }}
                                                                        </span>
                                                                    @endif
                                                                    <h5 class="font-semibold text-gray-900">{{ $programExercise->exercise->name }}</h5>
                                                                </div>

                                                                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm">
                                                                    <div class="flex items-center text-gray-700">
                                                                        <span class="font-medium">{{ __('Sets:') }}</span>
                                                                        <span class="ml-1">{{ $programExercise->sets }}</span>
                                                                    </div>

                                                                    @if($programExercise->reps_min && $programExercise->reps_max)
                                                                        <div class="flex items-center text-gray-700">
                                                                            <span class="font-medium">{{ __('Reps:') }}</span>
                                                                            <span class="ml-1">{{ $programExercise->reps_min }}-{{ $programExercise->reps_max }}</span>
                                                                        </div>
                                                                    @elseif($programExercise->reps_min)
                                                                        <div class="flex items-center text-gray-700">
                                                                            <span class="font-medium">{{ __('Reps:') }}</span>
                                                                            <span class="ml-1">{{ $programExercise->reps_min }}</span>
                                                                        </div>
                                                                    @endif

                                                                    @if($programExercise->rpe)
                                                                        <div class="flex items-center text-gray-700">
                                                                            <span class="font-medium">{{ __('RPE:') }}</span>
                                                                            <span class="ml-1">{{ $programExercise->rpe }}</span>
                                                                        </div>
                                                                    @endif

                                                                    @if($programExercise->rest_seconds)
                                                                        <div class="flex items-center text-gray-700">
                                                                            <span class="font-medium">{{ __('Rest:') }}</span>
                                                                            <span class="ml-1">{{ $programExercise->rest_seconds }}s</span>
                                                                        </div>
                                                                    @endif

                                                                    @if($programExercise->tempo)
                                                                        <div class="flex items-center text-gray-700">
                                                                            <span class="font-medium">{{ __('Tempo:') }}</span>
                                                                            <span class="ml-1">{{ $programExercise->tempo }}</span>
                                                                        </div>
                                                                    @endif
                                                                </div>

                                                                @if($programExercise->notes)
                                                                    <div class="mt-2 text-sm text-gray-600 bg-white rounded p-2">
                                                                        <span class="font-medium">{{ __('Notes:') }}</span>
                                                                        {{ $programExercise->notes }}
                                                                    </div>
                                                                @endif

                                                                @if($programExercise->progression_rules && count($programExercise->progression_rules) > 0)
                                                                    <div class="mt-2">
                                                                        <span class="text-xs font-medium text-gray-500">
                                                                            {{ __('Progression rules configured') }}
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center text-gray-500">
                        <p>{{ __('No active version for this program.') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
