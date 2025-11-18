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

        $service = new WgerApiService();

        // Test 1: List endpoint
        $this->info('=== Test 1: /exercise/ list endpoint ===');
        $exercises = $service->fetchExercises(5);
        $this->info('Fetched ' . count($exercises) . ' exercises');

        if (!empty($exercises)) {
            $this->info('First exercise from list:');
            $this->line(json_encode($exercises[0], JSON_PRETTY_PRINT));
            $this->line('Has name field: ' . (isset($exercises[0]['name']) ? 'YES' : 'NO'));
            $this->line('Has description field: ' . (isset($exercises[0]['description']) ? 'YES' : 'NO'));
        }
        $this->newLine();

        // Test 2: exerciseinfo endpoint (includes translations)
        $this->info('=== Test 2: /exerciseinfo/ endpoint ===');
        $response = $service->client()->get('exerciseinfo/', [
            'limit' => 5,
            'language' => 2, // English
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $infos = $data['results'] ?? [];
            $this->info('Fetched ' . count($infos) . ' exercise infos');

            if (!empty($infos)) {
                $this->info('First exerciseinfo:');
                $this->line(json_encode($infos[0], JSON_PRETTY_PRINT));
            }
        } else {
            $this->error('Failed to fetch exerciseinfo');
        }
        $this->newLine();

        // Test 3: Individual exercise detail
        if (!empty($exercises)) {
            $firstId = $exercises[0]['id'];
            $this->info("=== Test 3: /exercise/{$firstId}/ detail endpoint ===");
            $detail = $service->fetchExerciseDetails($firstId);

            if ($detail) {
                $this->info('Exercise detail:');
                $this->line(json_encode($detail, JSON_PRETTY_PRINT));
            } else {
                $this->error('Failed to fetch exercise detail');
            }
        }

        return self::SUCCESS;
    }
}
