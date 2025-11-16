<?php

namespace App\Livewire\Programs;

use App\Enums\ProgressionRuleType;
use Illuminate\View\View;
use Livewire\Component;

class ProgressionRulePreview extends Component
{
    public array $rules = [];
    public bool $compact = false;

    public function mount(array $rules, bool $compact = false): void
    {
        $this->rules = $rules;
        $this->compact = $compact;
    }

    public function getRuleSummary(array $rule): string
    {
        return match ($rule['type']) {
            ProgressionRuleType::LinearProgression->value => $this->getLinearProgressionSummary($rule),
            ProgressionRuleType::DoubleProgression->value => $this->getDoubleProgressionSummary($rule),
            ProgressionRuleType::TopSetBackoff->value => $this->getTopSetBackoffSummary($rule),
            ProgressionRuleType::RpeTarget->value => $this->getRpeTargetSummary($rule),
            ProgressionRuleType::PlannedDeload->value => $this->getPlannedDeloadSummary($rule),
            ProgressionRuleType::WeeklyUndulation->value => $this->getWeeklyUndulationSummary($rule),
            ProgressionRuleType::CustomWarmup->value => $this->getCustomWarmupSummary($rule),
            default => __('Unknown rule type'),
        };
    }

    protected function getLinearProgressionSummary(array $rule): string
    {
        $summary = "+{$rule['increment']}kg every {$rule['frequency']}";
        if (isset($rule['cap'])) {
            $summary .= " (cap: {$rule['cap']}kg)";
        }
        return $summary;
    }

    protected function getDoubleProgressionSummary(array $rule): string
    {
        return "{$rule['reps_min']}-{$rule['reps_max']} reps, then +{$rule['weight_increment']}kg";
    }

    protected function getTopSetBackoffSummary(array $rule): string
    {
        $summary = "1 × {$rule['top_set_reps']}, {$rule['backoff_sets']} × {$rule['backoff_reps']}";

        if ($rule['backoff_type'] === 'percentage') {
            $summary .= " @ {$rule['backoff_percentage']}%";
        } else {
            $summary .= " (-{$rule['backoff_weight_reduction']}kg)";
        }

        return $summary;
    }

    protected function getRpeTargetSummary(array $rule): string
    {
        return "Target RPE {$rule['target_rpe']} ± {$rule['tolerance']}";
    }

    protected function getPlannedDeloadSummary(array $rule): string
    {
        return "Deload {$rule['percentage']}% every {$rule['frequency_weeks']} weeks";
    }

    protected function getWeeklyUndulationSummary(array $rule): string
    {
        return "H: {$rule['heavy_percentage']}%, M: {$rule['medium_percentage']}%, L: {$rule['light_percentage']}%";
    }

    protected function getCustomWarmupSummary(array $rule): string
    {
        $count = count($rule['warmup_sets'] ?? []);
        return "{$count} warm-up set" . ($count !== 1 ? 's' : '');
    }

    public function render(): View
    {
        return view('livewire.programs.progression-rule-preview');
    }
}
