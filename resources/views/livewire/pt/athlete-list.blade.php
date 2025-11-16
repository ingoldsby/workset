<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900">
                {{ __('My Athletes') }}
            </h3>
            <div class="flex space-x-2">
                <button
                    wire:click="setFilter('active')"
                    class="px-3 py-1 text-xs rounded {{ $filter === 'active' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}"
                >
                    {{ __('Active') }}
                </button>
                <button
                    wire:click="setFilter('inactive')"
                    class="px-3 py-1 text-xs rounded {{ $filter === 'inactive' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}"
                >
                    {{ __('Inactive') }}
                </button>
                <button
                    wire:click="setFilter('all')"
                    class="px-3 py-1 text-xs rounded {{ $filter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}"
                >
                    {{ __('All') }}
                </button>
            </div>
        </div>

        @if($athletes->isEmpty())
            <div class="text-center py-12 text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="mt-4">{{ __('No athletes found.') }}</p>
                <p class="text-sm mt-2">
                    @if($filter === 'active')
                        {{ __('You have no active athlete assignments.') }}
                    @elseif($filter === 'inactive')
                        {{ __('You have no inactive athlete assignments.') }}
                    @else
                        {{ __('You have no athlete assignments.') }}
                    @endif
                </p>
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach($athletes as $athlete)
                    <div
                        wire:click="viewAthlete('{{ $athlete->id }}')"
                        class="cursor-pointer border border-gray-200 rounded-lg p-4 hover:border-blue-500 hover:shadow-md transition"
                    >
                        <div class="flex items-center mb-3">
                            <div class="flex-shrink-0 h-12 w-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 font-semibold text-lg">
                                    {{ strtoupper(substr($athlete->name, 0, 1)) }}
                                </span>
                            </div>
                            <div class="ml-3">
                                <h4 class="font-semibold text-gray-900">{{ $athlete->name }}</h4>
                                <p class="text-sm text-gray-600">{{ $athlete->email }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <div>
                                <span class="font-semibold">{{ $athlete->training_sessions_count ?? 0 }}</span> sessions
                            </div>
                            <div>
                                {{ __('Member since') }} {{ $athlete->created_at->format('M Y') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
