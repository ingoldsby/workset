<?php

namespace App\Jobs;

use App\Actions\GenerateWorkoutSuggestionAction;
use App\Enums\Role;
use App\Enums\SuggestionType;
use App\Models\PtAssignment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateWeeklyWorkoutSuggestionsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ?string $ptId = null,
        public ?string $memberId = null,
    ) {}

    public function handle(GenerateWorkoutSuggestionAction $action): void
    {
        $assignments = $this->getAssignments();

        foreach ($assignments as $assignment) {
            try {
                $pt = $assignment->pt;
                $member = $assignment->member;

                if (! $member->workoutPreference) {
                    Log::info('Skipping member without workout preferences', [
                        'member_id' => $member->id,
                        'pt_id' => $pt->id,
                    ]);

                    continue;
                }

                $action->execute(
                    member: $member,
                    pt: $pt,
                    type: SuggestionType::WeeklyProgram,
                    customPrompt: 'Generate a weekly training program based on the user\'s preferences and recent activity.',
                );

                Log::info('Generated weekly workout suggestion', [
                    'member_id' => $member->id,
                    'pt_id' => $pt->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to generate weekly workout suggestion', [
                    'member_id' => $assignment->member_id,
                    'pt_id' => $assignment->pt_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function getAssignments()
    {
        $query = PtAssignment::with(['pt', 'member.workoutPreference']);

        if ($this->ptId) {
            $query->where('pt_id', $this->ptId);
        }

        if ($this->memberId) {
            $query->where('member_id', $this->memberId);
        }

        return $query->get();
    }
}
