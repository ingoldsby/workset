<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create Program') }}
            </h2>
            <a
                href="{{ route('programs.index') }}"
                class="text-sm text-gray-600 hover:text-gray-900"
            >
                {{ __('Back to Programs') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @livewire('programs.program-builder')
        </div>
    </div>
</x-app-layout>
