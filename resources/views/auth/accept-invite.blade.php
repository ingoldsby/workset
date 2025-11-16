<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        You've been invited to join Workset as a <strong>{{ $invite->role->label() }}</strong>.
        @if($invite->personalTrainer)
            Your personal trainer will be <strong>{{ $invite->personalTrainer->name }}</strong>.
        @endif
        Please complete the form below to create your account.
    </div>

    <form method="POST" action="{{ route('invite.store', $invite->token) }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email (read-only) -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full bg-gray-100" type="email" :value="$invite->email" disabled />
            <p class="mt-1 text-sm text-gray-600">This email address is associated with your invitation.</p>
        </div>

        <!-- Timezone -->
        <div class="mt-4">
            <x-input-label for="timezone" :value="__('Timezone')" />
            <select id="timezone" name="timezone" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                @foreach(timezone_identifiers_list() as $tz)
                    <option value="{{ $tz }}" {{ old('timezone', config('app.timezone')) === $tz ? 'selected' : '' }}>
                        {{ $tz }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('timezone')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Accept Invitation') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
