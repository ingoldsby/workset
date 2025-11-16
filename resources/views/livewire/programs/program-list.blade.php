<div class="space-y-6">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ __('My Programs') }}
                </h3>
                <button
                    wire:click="createProgram"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('Create Program') }}
                </button>
            </div>

            @if($programs->isEmpty())
                <div class="text-center py-12 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-4">{{ __('No programs yet.') }}</p>
                    <p class="text-sm mt-2">{{ __('Create your first program to get started.') }}</p>
                </div>
            @else
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($programs as $program)
                        <div
                            wire:click="viewProgram('{{ $program->id }}')"
                            class="cursor-pointer border border-gray-200 rounded-lg p-4 hover:border-blue-500 hover:shadow-md transition"
                        >
                            <h4 class="font-semibold text-gray-900 mb-2">{{ $program->name }}</h4>

                            @if($program->description)
                                <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $program->description }}</p>
                            @endif

                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <div>
                                    @if($program->activeVersion)
                                        <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 rounded">
                                            {{ __('Active') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-600 rounded">
                                            {{ __('Draft') }}
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    {{ $program->created_at->diffForHumans() }}
                                </div>
                            </div>

                            @if($program->owner_id !== Auth::id())
                                <div class="mt-2 text-xs text-gray-500">
                                    {{ __('Created by') }}: {{ $program->owner->name }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
