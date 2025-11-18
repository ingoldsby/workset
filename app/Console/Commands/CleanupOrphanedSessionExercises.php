<?php

namespace App\Console\Commands;

use App\Models\SessionExercise;
use Illuminate\Console\Command;

class CleanupOrphanedSessionExercises extends Command
{
    protected $signature = 'workset:cleanup-orphaned-exercises';

    protected $description = 'Remove session exercises that have no sets logged';

    public function handle(): int
    {
        $this->comment('Finding orphaned session exercises...');

        $orphaned = SessionExercise::query()
            ->whereDoesntHave('sets')
            ->get();

        if ($orphaned->isEmpty()) {
            $this->info('No orphaned session exercises found.');
            return self::SUCCESS;
        }

        $this->info("Found {$orphaned->count()} orphaned session exercises.");

        if (! $this->confirm('Do you want to delete these records?', true)) {
            $this->comment('Cancelled.');
            return self::SUCCESS;
        }

        $deleted = 0;

        foreach ($orphaned as $sessionExercise) {
            $this->line("Deleting SessionExercise ID: {$sessionExercise->id}");
            $sessionExercise->delete();
            $deleted++;
        }

        $this->info("Successfully deleted {$deleted} orphaned session exercises.");

        return self::SUCCESS;
    }
}
