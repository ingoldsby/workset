<div>
    @if(empty($rules))
        <p class="text-sm text-gray-500 italic">{{ __('No progression rules configured') }}</p>
    @else
        @if($compact)
            <div class="space-y-1">
                @foreach($rules as $rule)
                    <div class="text-xs text-gray-600">
                        <span class="font-medium">{{ \App\Enums\ProgressionRuleType::from($rule['type'])->label() }}:</span>
                        {{ $this->getRuleSummary($rule) }}
                    </div>
                @endforeach
            </div>
        @else
            <div class="space-y-3">
                @foreach($rules as $rule)
                    <div class="p-3 bg-gray-50 rounded border border-gray-200">
                        <div class="font-medium text-gray-900 mb-1">
                            {{ \App\Enums\ProgressionRuleType::from($rule['type'])->label() }}
                        </div>
                        <div class="text-sm text-gray-700">
                            {{ $this->getRuleSummary($rule) }}
                        </div>
                        @if(isset($rule['miss_handling']) && $rule['miss_handling'])
                            <div class="mt-2 text-xs text-gray-600 border-t border-gray-300 pt-2">
                                <span class="font-medium">{{ __('Miss handling') }}:</span>
                                {{ ucfirst($rule['miss_handling']['action']) }}
                                @if($rule['miss_handling']['action'] === 'reduce' && isset($rule['miss_handling']['reduction_amount']))
                                    (-{{ $rule['miss_handling']['reduction_amount'] }}kg)
                                @endif
                            </div>
                        @endif

                        @if($rule['type'] === 'custom_warmup' && isset($rule['warmup_sets']))
                            <div class="mt-2 space-y-1">
                                @foreach($rule['warmup_sets'] as $index => $set)
                                    <div class="text-xs text-gray-600">
                                        Set {{ $index + 1 }}: {{ $set['reps'] }} reps @ {{ $set['percentage'] }}%
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>
