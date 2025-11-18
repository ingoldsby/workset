<div class="space-y-6">
    @if(session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ __('My Programs') }}
                </h3>
                <div class="flex space-x-3">
                    <a
                        href="{{ route('programs.progressionBuilder') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        {{ __('Progression Builder') }}
                    </a>
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

    {{-- Create Program Modal --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="cancelCreate"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="saveProgram">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                        {{ __('Create New Program') }}
                                    </h3>
                                    <div class="mt-2 space-y-4">
                                        <div>
                                            <label for="name" class="block text-sm font-medium text-gray-700">
                                                {{ __('Program Name') }}
                                            </label>
                                            <input
                                                type="text"
                                                id="name"
                                                wire:model="name"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                placeholder="{{ __('e.g., 5/3/1 for Beginners') }}"
                                                autofocus
                                            >
                                            @error('name')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="description" class="block text-sm font-medium text-gray-700">
                                                {{ __('Description') }} <span class="text-gray-500 text-xs">({{ __('Optional') }})</span>
                                            </label>
                                            <textarea
                                                id="description"
                                                wire:model="description"
                                                rows="3"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                placeholder="{{ __('Describe the program\'s goals and structure...') }}"
                                            ></textarea>
                                            @error('description')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button
                                type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                {{ __('Create Program') }}
                            </button>
                            <button
                                type="button"
                                wire:click="cancelCreate"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                {{ __('Cancel') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
