<?php

namespace App\Actions;

use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Support\Collection;

class ExportSessionDataCsv
{
    public function execute(User $user, ?string $startDate = null, ?string $endDate = null): string
    {
        $query = TrainingSession::query()
            ->where('user_id', $user->id)
            ->with(['exercises.sets', 'exercises.exercise', 'exercises.memberExercise'])
            ->whereNotNull('completed_at')
            ->orderBy('started_at');

        if ($startDate) {
            $query->where('started_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('started_at', '<=', $endDate);
        }

        $sessions = $query->get();

        $csvData = [];
        $csvData[] = [
            'Date',
            'Session Notes',
            'Exercise',
            'Set Number',
            'Set Type',
            'Reps',
            'Weight',
            'RPE',
            'Completed',
        ];

        foreach ($sessions as $session) {
            foreach ($session->exercises as $exercise) {
                foreach ($exercise->sets as $set) {
                    $csvData[] = [
                        $session->started_at->format('Y-m-d H:i'),
                        $session->notes ?? '',
                        $exercise->getExerciseName(),
                        $set->set_number,
                        $set->set_type?->value ?? '',
                        $set->performed_reps ?? $set->prescribed_reps,
                        $set->performed_weight ?? $set->prescribed_weight,
                        $set->performed_rpe ?? $set->prescribed_rpe,
                        $set->completed ? 'Yes' : 'No',
                    ];
                }
            }
        }

        return $this->arrayToCsv($csvData);
    }

    private function arrayToCsv(array $data): string
    {
        $output = fopen('php://temp', 'r+');

        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
