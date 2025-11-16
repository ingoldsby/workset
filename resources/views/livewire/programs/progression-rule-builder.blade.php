<div class="space-y-4">
    <!-- Existing Rules -->
    @if(!empty($rules))
        <div class="space-y-2">
            <h4 class="font-semibold text-gray-900">{{ __('Active Rules') }}</h4>
            @foreach($rules as $index => $rule)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded border border-gray-200">
                    <div class="flex-1">
                        <span class="font-medium text-gray-900">
                            {{ \App\Enums\ProgressionRuleType::from($rule['type'])->label() }}
                        </span>
                        <p class="text-xs text-gray-600 mt-1">
                            @if($rule['type'] === 'linear_progression')
                                +{{ $rule['increment'] }}kg every {{ $rule['frequency'] }}
                                @if($rule['cap']) (cap: {{ $rule['cap'] }}kg) @endif
                            @elseif($rule['type'] === 'double_progression')
                                {{ $rule['reps_min'] }}-{{ $rule['reps_max'] }} reps, then +{{ $rule['weight_increment'] }}kg
                            @elseif($rule['type'] === 'top_set_backoff')
                                1 &times; {{ $rule['top_set_reps'] }}, {{ $rule['backoff_sets'] }} &times; {{ $rule['backoff_reps'] }}
                                @if($rule['backoff_type'] === 'percentage')
                                    @ {{ $rule['backoff_percentage'] }}%
                                @else
                                    (-{{ $rule['backoff_weight_reduction'] }}kg)
                                @endif
                            @elseif($rule['type'] === 'rpe_target')
                                Target RPE {{ $rule['target_rpe'] }} &plusmn; {{ $rule['tolerance'] }}
                            @elseif($rule['type'] === 'planned_deload')
                                Deload {{ $rule['percentage'] }}% every {{ $rule['frequency_weeks'] }} weeks
                            @elseif($rule['type'] === 'weekly_undulation')
                                H: {{ $rule['heavy_percentage'] }}%, M: {{ $rule['medium_percentage'] }}%, L: {{ $rule['light_percentage'] }}%
                            @elseif($rule['type'] === 'custom_warmup')
                                {{ count($rule['warmup_sets']) }} warm-up sets
                            @endif
                        </p>
                    </div>
                    <button
                        wire:click="removeRule({{ $index }})"
                        class="ml-3 text-red-600 hover:text-red-800"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Add Rule Button -->
    @if(!$showAddRule)
        <button
            wire:click="showAddRuleForm"
            class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('Add Progression Rule') }}
        </button>
    @endif

    <!-- Add Rule Form -->
    @if($showAddRule)
        <div class="border border-gray-300 rounded-lg p-4 bg-white">
            <h4 class="font-semibold text-gray-900 mb-4">{{ __('Add Progression Rule') }}</h4>

            <!-- Rule Type Selection -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Rule Type') }}</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @foreach($ruleTypes as $ruleType)
                        <button
                            type="button"
                            wire:click="selectRuleType('{{ $ruleType->value }}')"
                            class="text-left p-3 border rounded {{ $selectedRuleType === $ruleType->value ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400' }}"
                        >
                            <div class="font-medium text-sm">{{ $ruleType->label() }}</div>
                            <div class="text-xs text-gray-600 mt-1">{{ $ruleType->description() }}</div>
                        </button>
                    @endforeach
                </div>
                @error('selectedRuleType') <span class="text-xs text-red-600 mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Linear Progression Fields -->
            @if($selectedRuleType === 'linear_progression')
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Weight Increment (kg)') }}</label>
                            <input
                                type="number"
                                wire:model="linearIncrement"
                                step="0.5"
                                class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            >
                            @error('linearIncrement') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Weight Cap (kg)') }} <span class="text-gray-500">({{ __('optional') }})</span></label>
                            <input
                                type="number"
                                wire:model="linearCap"
                                step="0.5"
                                class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            >
                            @error('linearCap') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Frequency') }}</label>
                        <select
                            wire:model="linearFrequency"
                            class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                        >
                            <option value="session">{{ __('Every Session') }}</option>
                            <option value="week">{{ __('Every Week') }}</option>
                        </select>
                        @error('linearFrequency') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>
            @endif

            <!-- Double Progression Fields -->
            @if($selectedRuleType === 'double_progression')
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Min Reps') }}</label>
                            <input
                                type="number"
                                wire:model="doubleRepsMin"
                                class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            >
                            @error('doubleRepsMin') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Max Reps') }}</label>
                            <input
                                type="number"
                                wire:model="doubleRepsMax"
                                class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            >
                            @error('doubleRepsMax') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Weight Increment (kg)') }}</label>
                            <input
                                type="number"
                                wire:model="doubleWeightIncrement"
                                step="0.5"
                                class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            >
                            @error('doubleWeightIncrement') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <p class="text-xs text-gray-600">
                        {{ __('Example: 6-8 reps. Hit 8 reps on all sets → add weight and drop to 6 reps.') }}
                    </p>
                </div>
            @endif

            <!-- Top Set + Back-off Fields -->
            @if($selectedRuleType === 'top_set_backoff')
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Top Set Reps') }}</label>
                            <input
                                type="number"
                                wire:model="topSetReps"
                                class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            >
                            @error('topSetReps') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Back-off Sets') }}</label>
                            <input
                                type="number"
                                wire:model="backoffSets"
                                class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            >
                            @error('backoffSets') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Back-off Reps') }}</label>
                            <input
                                type="number"
                                wire:model="backoffReps"
                                class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            >
                            @error('backoffReps') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Back-off Type') }}</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input
                                    type="radio"
                                    wire:model.live="backoffType"
                                    value="percentage"
                                    class="rounded-full border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                                <span class="ml-2 text-sm text-gray-700">{{ __('Percentage of top set') }}</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input
                                    type="radio"
                                    wire:model.live="backoffType"
                                    value="weight"
                                    class="rounded-full border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                                <span class="ml-2 text-sm text-gray-700">{{ __('Fixed weight reduction') }}</span>
                            </label>
                        </div>
                    </div>
                    @if($backoffType === 'percentage')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Back-off Percentage') }}</label>
                            <input
                                type="number"
                                wire:model="backoffPercentage"
                                min="1"
                                max="100"
                                class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            >
                            @error('backoffPercentage') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    @else
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Weight Reduction (kg)') }}</label>
                            <input
                                type="number"
                                wire:model="backoffWeightReduction"
                                step="0.5"
                                class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            >
                            @error('backoffWeightReduction') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>
            @endif

            <!-- RPE Target Fields -->
            @if($selectedRuleType === 'rpe_target')
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Target RPE') }}</label>
                            <input
                                type="number"
                                wire:model="targetRpe"
                                step="0.5"
                                min="1"
                                max="10"
                                class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            >
                            @error('targetRpe') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Tolerance') }}</label>
                            <input
                                type="number"
                                wire:model="rpeTolerance"
                                step="0.5"
                                min="0.1"
                                max="2"
                                class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            >
                            @error('rpeTolerance') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Weight Increase (kg)') }}</label>
                            <input
                                type="number"
                                wire:model="rpeWeightIncrease"
                                step="0.5"
                                class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            >
                            @error('rpeWeightIncrease') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            <p class="text-xs text-gray-600 mt-1">{{ __('If RPE < target - tolerance') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Weight Decrease (kg)') }}</label>
                            <input
                                type="number"
                                wire:model="rpeWeightDecrease"
                                step="0.5"
                                class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            >
                            @error('rpeWeightDecrease') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            <p class="text-xs text-gray-600 mt-1">{{ __('If RPE > target + tolerance') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Planned Deload Fields -->
            @if($selectedRuleType === 'planned_deload')
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Frequency (weeks)') }}</label>
                            <input
                                type="number"
                                wire:model="deloadFrequencyWeeks"
                                min="1"
                                max="12"
                                class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            >
                            @error('deloadFrequencyWeeks') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Deload Percentage') }}</label>
                            <input
                                type="number"
                                wire:model="deloadPercentage"
                                min="1"
                                max="100"
                                class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            >
                            @error('deloadPercentage') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <p class="text-xs text-gray-600">
                        {{ __('Example: Every 4 weeks, reduce weight to 70% of current working weight.') }}
                    </p>
                </div>
            @endif

            <!-- Weekly Undulation Fields -->
            @if($selectedRuleType === 'weekly_undulation')
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Heavy Day %') }}</label>
                            <input
                                type="number"
                                wire:model="heavyPercentage"
                                min="1"
                                max="100"
                                class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            >
                            @error('heavyPercentage') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Medium Day %') }}</label>
                            <input
                                type="number"
                                wire:model="mediumPercentage"
                                min="1"
                                max="100"
                                class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            >
                            @error('mediumPercentage') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Light Day %') }}</label>
                            <input
                                type="number"
                                wire:model="lightPercentage"
                                min="1"
                                max="100"
                                class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            >
                            @error('lightPercentage') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <p class="text-xs text-gray-600">
                        {{ __('Percentages are relative to the base working weight for this exercise.') }}
                    </p>
                </div>
            @endif

            <!-- Custom Warm-up Fields -->
            @if($selectedRuleType === 'custom_warmup')
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <label class="block text-sm font-medium text-gray-700">{{ __('Warm-up Sets') }}</label>
                        <button
                            type="button"
                            wire:click="addWarmupSet"
                            class="text-sm text-blue-600 hover:text-blue-800"
                        >
                            + {{ __('Add Set') }}
                        </button>
                    </div>
                    @foreach($warmupSets as $index => $set)
                        <div class="flex items-center space-x-3">
                            <div class="flex-1">
                                <input
                                    type="number"
                                    wire:model="warmupSets.{{ $index }}.reps"
                                    placeholder="{{ __('Reps') }}"
                                    class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                >
                                @error("warmupSets.{$index}.reps") <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div class="flex-1">
                                <input
                                    type="number"
                                    wire:model="warmupSets.{{ $index }}.percentage"
                                    placeholder="{{ __('% of working weight') }}"
                                    min="1"
                                    max="100"
                                    class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                >
                                @error("warmupSets.{$index}.percentage") <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <button
                                type="button"
                                wire:click="removeWarmupSet({{ $index }})"
                                class="text-red-600 hover:text-red-800"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    @endforeach
                    @error('warmupSets') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            @endif

            <!-- Miss Handling (for applicable rule types) -->
            @if(in_array($selectedRuleType, ['linear_progression', 'double_progression']))
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <label class="inline-flex items-center mb-3">
                        <input
                            type="checkbox"
                            wire:model.live="enableMissHandling"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                        <span class="ml-2 text-sm font-medium text-gray-700">{{ __('Enable Miss Handling') }}</span>
                    </label>

                    @if($enableMissHandling)
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Action on Miss') }}</label>
                                <select
                                    wire:model.live="missAction"
                                    class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                >
                                    <option value="reduce">{{ __('Reduce Weight') }}</option>
                                    <option value="deload">{{ __('Trigger Deload') }}</option>
                                    <option value="maintain">{{ __('Maintain Weight') }}</option>
                                </select>
                                @error('missAction') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            @if($missAction === 'reduce')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Reduction Amount (kg)') }}</label>
                                    <input
                                        type="number"
                                        wire:model="missReductionAmount"
                                        step="0.5"
                                        class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                    >
                                    @error('missReductionAmount') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="mt-6 flex space-x-3">
                <button
                    type="button"
                    wire:click="addRule"
                    class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700"
                >
                    {{ __('Add Rule') }}
                </button>
                <button
                    type="button"
                    wire:click="cancelAddRule"
                    class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400"
                >
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    @endif
</div>
