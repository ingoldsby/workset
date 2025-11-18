<?php

namespace App\Console\Commands;

use App\Models\Exercise;
use App\Services\WgerApiService;
use App\Services\WgerExerciseTransformer;
use Illuminate\Console\Command;

class DiagnoseExercise extends Command
{
    protected $signature = 'workset:diagnose-exercise {wger_id : The wger exercise ID to diagnose}';

    protected $description = 'Diagnose why a specific wger exercise was not imported';

    public function handle(): int
    {
        $wgerId = (int) $this->argument('wger_id');

        $this->info("Diagnosing wger exercise ID: {$wgerId}");
        $this->newLine();

        // Check if it exists in our database
        $existing = Exercise::where('wger_id', $wgerId)->first();

        if ($existing) {
            $this->info('✓ Exercise EXISTS in database!');
            $this->table(
                ['Field', 'Value'],
                [
                    ['ID', $existing->id],
                    ['Name', $existing->name],
                    ['wger_id', $existing->wger_id],
                    ['Primary Muscle', $existing->primary_muscle?->label() ?? 'None'],
                    ['Equipment', $existing->equipment?->label() ?? 'None'],
                    ['Description', substr($existing->description ?? '', 0, 100) . '...'],
                ]
            );
            return self::SUCCESS;
        }

        $this->warn('✗ Exercise NOT FOUND in database');
        $this->newLine();

        // Fetch from wger API to see why
        $this->info('Fetching from wger API...');
        $service = new WgerApiService();

        // Fetch all exercises and find this one
        $exercises = $service->fetchExercises(1000);
        $wgerExercise = null;

        foreach ($exercises as $exercise) {
            if ($exercise['id'] === $wgerId) {
                $wgerExercise = $exercise;
                break;
            }
        }

        if (!$wgerExercise) {
            $this->error("Exercise ID {$wgerId} not found in wger API response");
            $this->info('This might mean the exercise ID is from the old /exercise/ endpoint');
            $this->info('The /exerciseinfo/ endpoint uses different IDs');
            return self::FAILURE;
        }

        $this->info('✓ Found in wger API');
        $this->newLine();

        // Check if it should be imported
        $shouldImport = WgerExerciseTransformer::shouldImport($wgerExercise, 2);

        $this->info('Import Filter Checks:');
        $this->newLine();

        // Check translations
        $hasTranslations = !empty($wgerExercise['translations']);
        $this->line('Has translations: ' . ($hasTranslations ? '✓ YES' : '✗ NO'));

        if ($hasTranslations) {
            $translation = WgerExerciseTransformer::getTranslation($wgerExercise, 2);
            $hasEnglishTranslation = $translation !== null;
            $this->line('Has English translation: ' . ($hasEnglishTranslation ? '✓ YES' : '✗ NO'));

            if ($hasEnglishTranslation) {
                $hasName = !empty($translation['name']);
                $this->line('Has name: ' . ($hasName ? '✓ YES (' . $translation['name'] . ')' : '✗ NO'));
            }
        }

        // Check muscles
        $hasMuscles = !empty($wgerExercise['muscles']) || !empty($wgerExercise['muscles_secondary']);
        $this->line('Has muscle data: ' . ($hasMuscles ? '✓ YES' : '✗ NO'));

        if ($hasMuscles) {
            $primaryCount = count($wgerExercise['muscles'] ?? []);
            $secondaryCount = count($wgerExercise['muscles_secondary'] ?? []);
            $this->line("  - Primary muscles: {$primaryCount}");
            $this->line("  - Secondary muscles: {$secondaryCount}");
        }

        $this->newLine();
        $this->info('Should Import: ' . ($shouldImport ? '✓ YES' : '✗ NO'));

        if (!$shouldImport) {
            $this->warn('This exercise was skipped due to missing data');
        } else {
            $this->info('This exercise SHOULD have been imported!');
            $this->warn('There may have been an error during import. Check logs.');
        }

        // Show raw data
        if ($this->option('verbose')) {
            $this->newLine();
            $this->info('Raw wger data:');
            $this->line(json_encode($wgerExercise, JSON_PRETTY_PRINT));
        }

        return self::SUCCESS;
    }
}
