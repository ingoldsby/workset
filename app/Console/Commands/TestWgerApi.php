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
        $this->info('Testing wger API endpoints...');
        $this->newLine();

        $service = new WgerApiService(2); // English

        // Test 1: New exercisebaseinfo endpoint
        $this->info('=== Test 1: /exercisebaseinfo/ endpoint (NEW) ===');
        $exercises = $service->fetchExercises(5);
        $this->info('Fetched ' . count($exercises) . ' exercises');

        if (!empty($exercises)) {
            $this->info('First exercise from exercisebaseinfo:');
            $this->line(json_encode($exercises[0], JSON_PRETTY_PRINT));
            $this->newLine();
            $this->line('Has translations: ' . (!empty($exercises[0]['translations']) ? 'YES' : 'NO'));
            $this->line('Has muscles: ' . (!empty($exercises[0]['muscles']) ? 'YES (' . count($exercises[0]['muscles']) . ')' : 'NO'));
            $this->line('Has equipment: ' . (!empty($exercises[0]['equipment']) ? 'YES (' . count($exercises[0]['equipment']) . ')' : 'NO'));

            if (!empty($exercises[0]['translations'])) {
                $firstTranslation = $exercises[0]['translations'][0] ?? null;
                if ($firstTranslation) {
                    $this->line('First translation name: ' . ($firstTranslation['name'] ?? 'N/A'));
                    $this->line('Translation language: ' . ($firstTranslation['language'] ?? 'N/A'));
                }
            }
        }

        return self::SUCCESS;
    }
}
