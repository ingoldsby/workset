<div class="space-y-6">
    {{-- Version Header --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-start mb-4">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                        <h3 class="text-2xl font-bold text-gray-900">{{ $program->name }}</h3>
                        @if($version->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ __('Active Version') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                {{ __('Inactive') }}
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500">
                        {{ __('Version :number', ['number' => $version->version_number]) }}
                        @if($version->change_notes)
                            · {{ $version->change_notes }}
                        @endif
                    </p>
                </div>
                <button
                    wire:click="backToProgram"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50"
                >
                    ← {{ __('Back to Program') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Program Days --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-lg font-semibold text-gray-900">
                    {{ __('Training Days') }}
                </h4>
                <button
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 opacity-50 cursor-not-allowed"
                    disabled
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('Add Day') }}
                </button>
            </div>

            @if($version->days->isEmpty())
                <div class="text-center py-12 bg-blue-50 border-2 border-dashed border-blue-200 rounded-lg">
                    <svg class="mx-auto h-12 w-12 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('No training days yet') }}</h3>
                    <p class="mt-2 text-sm text-gray-600 max-w-md mx-auto">
                        {{ __('This version doesn\'t have any training days defined yet. The program structure editor is coming soon!') }}
                    </p>
                    <div class="mt-6 bg-white border border-blue-200 rounded-lg p-4 max-w-2xl mx-auto text-left">
                        <h4 class="font-medium text-gray-900 mb-2">{{ __('Coming Soon: Program Builder') }}</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• {{ __('Add training days to your program') }}</li>
                            <li>• {{ __('Configure exercises, sets, and reps') }}</li>
                            <li>• {{ __('Set up progression rules') }}</li>
                            <li>• {{ __('Define rest days and program structure') }}</li>
                        </ul>
                    </div>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($version->days->sortBy('day_number') as $day)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h5 class="font-semibold text-gray-900">
                                        {{ __('Day :number', ['number' => $day->day_number]) }}: {{ $day->name }}
                                    </h5>
                                    @if($day->description)
                                        <p class="text-sm text-gray-600 mt-1">{{ $day->description }}</p>
                                    @endif
                                    @if($day->exercises->isNotEmpty())
                                        <div class="mt-3 text-sm text-gray-500">
                                            {{ __(':count exercises', ['count' => $day->exercises->count()]) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Version Info --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">
                {{ __('Version Information') }}
            </h4>
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('Version Number') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $version->version_number }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('Status') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($version->is_active)
                            <span class="text-green-600 font-medium">{{ __('Active') }}</span>
                        @else
                            <span class="text-gray-600">{{ __('Inactive') }}</span>
                        @endif
                    </dd>
                </div>
                @if($version->change_notes)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Change Notes') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $version->change_notes }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('Created') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $version->created_at->format('j M Y, g:i a') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('Last Updated') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $version->updated_at->format('j M Y, g:i a') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
