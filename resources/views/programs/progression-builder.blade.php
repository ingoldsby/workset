<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Progression Rule Builder') }}
            </h2>
            <a
                href="{{ route('programs.index') }}"
                class="text-sm text-gray-600 hover:text-gray-900"
            >
                ← {{ __('Back to Programs') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('About Progression Rules') }}</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        {{ __('Progression rules define how exercises should progress over time. You can combine multiple rules to create sophisticated programming strategies.') }}
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">{{ __('Available Rule Types:') }}</h4>
                            <ul class="space-y-1 text-gray-600">
                                <li>• {{ __('Linear Progression') }} - {{ __('Add weight consistently') }}</li>
                                <li>• {{ __('Double Progression') }} - {{ __('Increase reps then weight') }}</li>
                                <li>• {{ __('Top Set + Back-off') }} - {{ __('Heavy set with volume') }}</li>
                                <li>• {{ __('RPE Target') }} - {{ __('Auto-regulation by feel') }}</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">{{ __('Advanced Options:') }}</h4>
                            <ul class="space-y-1 text-gray-600">
                                <li>• {{ __('Planned Deloads') }} - {{ __('Scheduled recovery weeks') }}</li>
                                <li>• {{ __('Weekly Undulation') }} - {{ __('Varying intensities') }}</li>
                                <li>• {{ __('Custom Warm-ups') }} - {{ __('Specific preparation sets') }}</li>
                                <li>• {{ __('Miss Handling') }} - {{ __('Auto-adjust on failures') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @livewire('programs.progression-rule-builder')
                </div>
            </div>

            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-medium text-blue-900 mb-2">{{ __('💡 Tip: Combining Rules') }}</h4>
                <p class="text-sm text-blue-800">
                    {{ __('You can apply multiple rules to a single exercise. For example, use Linear Progression with Planned Deloads and Custom Warm-ups for a complete programming strategy.') }}
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
