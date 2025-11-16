<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Send Invitation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('invites.store') }}">
                        @csrf

                        <!-- Email -->
                        <div>
                            <x-input-label for="email" :value="__('Email Address')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Role -->
                        <div class="mt-4">
                            <x-input-label for="role" :value="__('Role')" />
                            <select id="role" name="role" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">Select a role...</option>
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="pt" {{ old('role') === 'pt' ? 'selected' : '' }}>PT</option>
                                <option value="member" {{ old('role') === 'member' ? 'selected' : '' }}>Member</option>
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        <!-- Personal Trainer (only shown when role is Member) -->
                        <div class="mt-4" id="pt-selection" style="display: none;">
                            <x-input-label for="pt_id" :value="__('Assign Personal Trainer (Optional)')" />
                            <select id="pt_id" name="pt_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">None</option>
                                @foreach($personalTrainers as $pt)
                                    <option value="{{ $pt->id }}" {{ old('pt_id') === $pt->id ? 'selected' : '' }}>
                                        {{ $pt->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('pt_id')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6 space-x-3">
                            <a href="{{ route('invites.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Send Invitation') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const ptSelection = document.getElementById('pt-selection');

            function togglePtSelection() {
                if (roleSelect.value === 'member') {
                    ptSelection.style.display = 'block';
                } else {
                    ptSelection.style.display = 'none';
                }
            }

            roleSelect.addEventListener('change', togglePtSelection);
            togglePtSelection(); // Initial check
        });
    </script>
    @endpush
</x-app-layout>
