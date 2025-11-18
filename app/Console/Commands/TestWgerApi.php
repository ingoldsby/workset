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

        // Test all available endpoints
        $endpoints = [
            'exercise',
            'exerciseinfo',
            'exercisebaseinfo',
            'exercisebase',
        ];

        foreach ($endpoints as $endpoint) {
            $this->info("=== Testing /{$endpoint}/ ===");

            try {
                $response = $service->client()->get("{$endpoint}/", [
                    'limit' => 3,
                    'language' => 2,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $count = count($data['results'] ?? []);
                    $this->info("✓ Endpoint exists - {$count} results");

                    if ($count > 0) {
                        $first = $data['results'][0];
                        $this->line("  Has 'name': " . (isset($first['name']) ? 'YES' : 'NO'));
                        $this->line("  Has 'translations': " . (!empty($first['translations']) ? 'YES (' . count($first['translations']) . ')' : 'NO'));
                        $this->line("  Has 'muscles': " . (!empty($first['muscles']) ? 'YES (' . count($first['muscles']) . ')' : 'NO'));
                        $this->line("  Has 'equipment': " . (!empty($first['equipment']) ? 'YES (' . count($first['equipment']) . ')' : 'NO'));
                    }
                } else {
                    $this->warn("✗ HTTP {$response->status()}");
                }
            } catch (\Exception $e) {
                $this->error("✗ Error: {$e->getMessage()}");
            }

            $this->newLine();
        }

        return self::SUCCESS;
    }
}
