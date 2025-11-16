<?php

namespace App\Livewire\Programs;

use App\Enums\ProgressionRuleType;
use Illuminate\View\View;
use Livewire\Component;

class ProgressionRuleBuilder extends Component
{
    public ?array $rules = [];
    public ?string $selectedRuleType = null;
    public bool $showAddRule = false;

    // Linear Progression fields
    public ?float $linearIncrement = null;
    public ?float $linearCap = null;
    public string $linearFrequency = 'session'; // session, week

    // Double Progression fields
    public ?int $doubleRepsMin = null;
    public ?int $doubleRepsMax = null;
    public ?float $doubleWeightIncrement = null;

    // Top Set + Back-off fields
    public ?int $topSetReps = null;
    public ?int $backoffSets = null;
    public ?int $backoffReps = null;
    public ?float $backoffPercentage = null;
    public ?float $backoffWeightReduction = null;
    public string $backoffType = 'percentage'; // percentage, weight

    // RPE Target fields
    public ?float $targetRpe = null;
    public ?float $rpeTolerance = null;
    public ?float $rpeWeightIncrease = null;
    public ?float $rpeWeightDecrease = null;

    // Planned Deload fields
    public ?int $deloadFrequencyWeeks = null;
    public ?float $deloadPercentage = null;

    // Weekly Undulation fields
    public ?float $heavyPercentage = null;
    public ?float $mediumPercentage = null;
    public ?float $lightPercentage = null;

    // Custom Warm-up fields
    public array $warmupSets = [];

    // Miss handling
    public bool $enableMissHandling = false;
    public string $missAction = 'reduce'; // reduce, deload, maintain
    public ?float $missReductionAmount = null;

    public function mount(?array $existingRules = null): void
    {
        if ($existingRules) {
            $this->rules = $existingRules;
        }
    }

    public function showAddRuleForm(): void
    {
        $this->showAddRule = true;
        $this->resetRuleFields();
    }

    public function cancelAddRule(): void
    {
        $this->showAddRule = false;
        $this->selectedRuleType = null;
        $this->resetRuleFields();
    }

    public function selectRuleType(string $type): void
    {
        $this->selectedRuleType = $type;
        $this->resetRuleFields();
    }

    public function addRule(): void
    {
        $this->validate($this->getRulesForValidation());

        $rule = $this->buildRuleData();

        $this->rules[] = $rule;
        $this->cancelAddRule();

        $this->dispatch('rules-updated', rules: $this->rules);
    }

    public function removeRule(int $index): void
    {
        unset($this->rules[$index]);
        $this->rules = array_values($this->rules);

        $this->dispatch('rules-updated', rules: $this->rules);
    }

    public function addWarmupSet(): void
    {
        $this->warmupSets[] = [
            'reps' => null,
            'percentage' => null,
        ];
    }

    public function removeWarmupSet(int $index): void
    {
        unset($this->warmupSets[$index]);
        $this->warmupSets = array_values($this->warmupSets);
    }

    protected function buildRuleData(): array
    {
        $baseRule = [
            'type' => $this->selectedRuleType,
            'enabled' => true,
        ];

        return match ($this->selectedRuleType) {
            ProgressionRuleType::LinearProgression->value => array_merge($baseRule, [
                'increment' => $this->linearIncrement,
                'cap' => $this->linearCap,
                'frequency' => $this->linearFrequency,
                'miss_handling' => $this->buildMissHandling(),
            ]),
            ProgressionRuleType::DoubleProgression->value => array_merge($baseRule, [
                'reps_min' => $this->doubleRepsMin,
                'reps_max' => $this->doubleRepsMax,
                'weight_increment' => $this->doubleWeightIncrement,
                'miss_handling' => $this->buildMissHandling(),
            ]),
            ProgressionRuleType::TopSetBackoff->value => array_merge($baseRule, [
                'top_set_reps' => $this->topSetReps,
                'backoff_sets' => $this->backoffSets,
                'backoff_reps' => $this->backoffReps,
                'backoff_type' => $this->backoffType,
                'backoff_percentage' => $this->backoffType === 'percentage' ? $this->backoffPercentage : null,
                'backoff_weight_reduction' => $this->backoffType === 'weight' ? $this->backoffWeightReduction : null,
            ]),
            ProgressionRuleType::RpeTarget->value => array_merge($baseRule, [
                'target_rpe' => $this->targetRpe,
                'tolerance' => $this->rpeTolerance,
                'weight_increase' => $this->rpeWeightIncrease,
                'weight_decrease' => $this->rpeWeightDecrease,
            ]),
            ProgressionRuleType::PlannedDeload->value => array_merge($baseRule, [
                'frequency_weeks' => $this->deloadFrequencyWeeks,
                'percentage' => $this->deloadPercentage,
            ]),
            ProgressionRuleType::WeeklyUndulation->value => array_merge($baseRule, [
                'heavy_percentage' => $this->heavyPercentage,
                'medium_percentage' => $this->mediumPercentage,
                'light_percentage' => $this->lightPercentage,
            ]),
            ProgressionRuleType::CustomWarmup->value => array_merge($baseRule, [
                'warmup_sets' => $this->warmupSets,
            ]),
            default => $baseRule,
        };
    }

    protected function buildMissHandling(): ?array
    {
        if (! $this->enableMissHandling) {
            return null;
        }

        return [
            'action' => $this->missAction,
            'reduction_amount' => $this->missReductionAmount,
        ];
    }

    protected function getRulesForValidation(): array
    {
        $baseRules = [
            'selectedRuleType' => ['required', 'string'],
        ];

        $specificRules = match ($this->selectedRuleType) {
            ProgressionRuleType::LinearProgression->value => [
                'linearIncrement' => ['required', 'numeric', 'min:0.1'],
                'linearCap' => ['nullable', 'numeric', 'gt:linearIncrement'],
                'linearFrequency' => ['required', 'in:session,week'],
            ],
            ProgressionRuleType::DoubleProgression->value => [
                'doubleRepsMin' => ['required', 'integer', 'min:1'],
                'doubleRepsMax' => ['required', 'integer', 'gt:doubleRepsMin'],
                'doubleWeightIncrement' => ['required', 'numeric', 'min:0.1'],
            ],
            ProgressionRuleType::TopSetBackoff->value => [
                'topSetReps' => ['required', 'integer', 'min:1'],
                'backoffSets' => ['required', 'integer', 'min:1'],
                'backoffReps' => ['required', 'integer', 'min:1'],
                'backoffType' => ['required', 'in:percentage,weight'],
                'backoffPercentage' => ['required_if:backoffType,percentage', 'nullable', 'numeric', 'min:1', 'max:100'],
                'backoffWeightReduction' => ['required_if:backoffType,weight', 'nullable', 'numeric', 'min:0.1'],
            ],
            ProgressionRuleType::RpeTarget->value => [
                'targetRpe' => ['required', 'numeric', 'min:1', 'max:10'],
                'rpeTolerance' => ['required', 'numeric', 'min:0.1', 'max:2'],
                'rpeWeightIncrease' => ['required', 'numeric', 'min:0.1'],
                'rpeWeightDecrease' => ['required', 'numeric', 'min:0.1'],
            ],
            ProgressionRuleType::PlannedDeload->value => [
                'deloadFrequencyWeeks' => ['required', 'integer', 'min:1', 'max:12'],
                'deloadPercentage' => ['required', 'numeric', 'min:1', 'max:100'],
            ],
            ProgressionRuleType::WeeklyUndulation->value => [
                'heavyPercentage' => ['required', 'numeric', 'min:1', 'max:100'],
                'mediumPercentage' => ['required', 'numeric', 'min:1', 'max:100'],
                'lightPercentage' => ['required', 'numeric', 'min:1', 'max:100'],
            ],
            ProgressionRuleType::CustomWarmup->value => [
                'warmupSets' => ['required', 'array', 'min:1'],
                'warmupSets.*.reps' => ['required', 'integer', 'min:1'],
                'warmupSets.*.percentage' => ['required', 'numeric', 'min:1', 'max:100'],
            ],
            default => [],
        };

        if ($this->enableMissHandling) {
            $specificRules['missAction'] = ['required', 'in:reduce,deload,maintain'];
            $specificRules['missReductionAmount'] = ['required_if:missAction,reduce', 'nullable', 'numeric', 'min:0.1'];
        }

        return array_merge($baseRules, $specificRules);
    }

    protected function resetRuleFields(): void
    {
        // Reset all fields
        $this->linearIncrement = null;
        $this->linearCap = null;
        $this->linearFrequency = 'session';

        $this->doubleRepsMin = null;
        $this->doubleRepsMax = null;
        $this->doubleWeightIncrement = null;

        $this->topSetReps = null;
        $this->backoffSets = null;
        $this->backoffReps = null;
        $this->backoffPercentage = null;
        $this->backoffWeightReduction = null;
        $this->backoffType = 'percentage';

        $this->targetRpe = null;
        $this->rpeTolerance = null;
        $this->rpeWeightIncrease = null;
        $this->rpeWeightDecrease = null;

        $this->deloadFrequencyWeeks = null;
        $this->deloadPercentage = null;

        $this->heavyPercentage = null;
        $this->mediumPercentage = null;
        $this->lightPercentage = null;

        $this->warmupSets = [];

        $this->enableMissHandling = false;
        $this->missAction = 'reduce';
        $this->missReductionAmount = null;
    }

    public function render(): View
    {
        $ruleTypes = ProgressionRuleType::cases();

        return view('livewire.programs.progression-rule-builder', [
            'ruleTypes' => $ruleTypes,
        ]);
    }
}
