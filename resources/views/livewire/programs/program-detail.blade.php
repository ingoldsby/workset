<div class="space-y-6">
    {{-- Program Header --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $program->name }}</h3>

                    @if($program->description)
                        <p class="text-gray-600 mb-4">{{ $program->description }}</p>
                    @endif

                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>{{ $program->owner->name }}</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>{{ __('Created') }} {{ $program->created_at->diffForHumans() }}</span>
                        </div>
                        @if($program->activeVersion)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ __('Active') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                {{ __('Draft') }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex space-x-2">
                    <button
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        {{ __('Edit Program') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Program Versions --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-lg font-semibold text-gray-900">
                    {{ __('Program Versions') }}
                </h4>
                <button
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('New Version') }}
                </button>
            </div>

            @if($program->versions->isEmpty())
                <div class="text-center py-12 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-4">{{ __('No versions yet.') }}</p>
                    <p class="text-sm mt-2">{{ __('Create your first version to define the program structure.') }}</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($program->versions->sortByDesc('created_at') as $version)
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 transition">
                            <div class="flex justify-between items-center">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <h5 class="font-semibold text-gray-900">
                                            {{ $version->name ?? __('Version :number', ['number' => $loop->iteration]) }}
                                        </h5>
                                        @if($version->is_active)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                {{ __('Active') }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ __('Created') }} {{ $version->created_at->format('j M Y') }}
                                    </p>
                                </div>
                                <div class="flex space-x-2">
                                    <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        {{ __('View') }}
                                    </button>
                                    @if(!$version->is_active)
                                        <button class="text-green-600 hover:text-green-800 text-sm font-medium">
                                            {{ __('Activate') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Program Information --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">
                {{ __('Program Information') }}
            </h4>
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('Visibility') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ ucfirst($program->visibility) }}
                    </dd>
                </div>
                @if($program->is_template)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Template') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                {{ __('Template Program') }}
                            </span>
                        </dd>
                    </div>
                @endif
                @if($program->category)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Category') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $program->category }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('Last Updated') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $program->updated_at->format('j M Y, g:i a') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
