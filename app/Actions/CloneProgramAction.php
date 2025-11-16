<?php

namespace App\Actions;

use App\Models\Program;
use App\Models\User;

class CloneProgramAction
{
    public function execute(Program $sourceProgram, User $user, bool $asTemplate = false): Program
    {
        $clonedProgram = $sourceProgram->replicate([
            'id',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $clonedProgram->owner_id = $user->id;
        $clonedProgram->cloned_from_id = $sourceProgram->id;
        $clonedProgram->is_template = $asTemplate;
        $clonedProgram->install_count = 0;
        $clonedProgram->visibility = 'private';
        $clonedProgram->save();

        // Clone versions
        foreach ($sourceProgram->versions as $version) {
            $clonedVersion = $version->replicate([
                'id',
                'program_id',
                'created_at',
                'updated_at',
            ]);

            $clonedVersion->program_id = $clonedProgram->id;
            $clonedVersion->save();

            // Clone program days and exercises
            foreach ($version->days as $day) {
                $clonedDay = $day->replicate([
                    'id',
                    'program_version_id',
                    'created_at',
                    'updated_at',
                ]);

                $clonedDay->program_version_id = $clonedVersion->id;
                $clonedDay->save();

                foreach ($day->exercises as $exercise) {
                    $clonedExercise = $exercise->replicate([
                        'id',
                        'program_day_id',
                        'created_at',
                        'updated_at',
                    ]);

                    $clonedExercise->program_day_id = $clonedDay->id;
                    $clonedExercise->save();
                }
            }
        }

        // Increment install count on source if it's a template
        if ($sourceProgram->is_template) {
            $sourceProgram->increment('install_count');
        }

        return $clonedProgram;
    }
}
