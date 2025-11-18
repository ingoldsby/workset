<?php

namespace App\Console\Commands;

use App\Services\WgerApiService;
use Illuminate\Console\Command;

class TestWgerApi extends Command
{
    protected $signature = 'workset:test-wger';

    protected $description = 'Test wger API response structure';

    public function handle(): int
    {
        $this->info('Fetching sample exercises from wger...');

        $service = new WgerApiService();
        $exercises = $service->fetchExercises(5); // Just get 5

        $this->info('Fetched ' . count($exercises) . ' exercises');
        $this->newLine();

        if (empty($exercises)) {
            $this->error('No exercises returned from API');
            return self::FAILURE;
        }

        // Show first exercise structure
        $this->info('First exercise data structure:');
        $this->line(json_encode($exercises[0], JSON_PRETTY_PRINT));
        $this->newLine();

        // Check for muscle data
        $withMuscles = 0;
        $withoutMuscles = 0;

        foreach ($exercises as $exercise) {
            $hasMuscles = !empty($exercise['muscles']) || !empty($exercise['muscles_secondary']);
            if ($hasMuscles) {
                $withMuscles++;
            } else {
                $withoutMuscles++;
            }
        }

        $this->info("Exercises WITH muscle data: {$withMuscles}");
        $this->info("Exercises WITHOUT muscle data: {$withoutMuscles}");

        return self::SUCCESS;
    }
}
