<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Session Details') }}
            </h2>
            <a href="{{ route('history.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                {{ __('← Back to History') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @livewire('history.session-detail', ['sessionId' => $session])
        </div>
    </div>
</x-app-layout>
