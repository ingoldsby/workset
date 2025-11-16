<?php

namespace App\Console\Commands;

use App\Models\Exercise;
use App\Services\WgerApiService;
use App\Services\WgerExerciseTransformer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportExercisesFromWger extends Command
{
    protected $signature = 'workset:import-exercises
                            {--limit=100 : Number of exercises to fetch per page}
                            {--max= : Maximum number of exercises to import}
                            {--fresh : Delete existing exercises before import}
                            {--images : Download and store exercise images}
                            {--language=2 : wger language ID (2 = English)}';

    protected $description = 'Import exercises from wger.de API into the exercise library';

    protected int $imported = 0;
    protected int $skipped = 0;
    protected int $failed = 0;
    protected int $imagesDownloaded = 0;

    public function handle(): int
    {
        $this->info('Starting exercise import from wger.de...');
        $this->newLine();

        // Handle fresh import
        if ($this->option('fresh')) {
            if (! $this->confirm('This will delete ALL existing exercises. Are you sure?')) {
                $this->warn('Import cancelled.');

                return self::FAILURE;
            }

            $this->handleFreshImport();
        }

        // Initialize service
        $languageId = (int) $this->option('language');
        $service = new WgerApiService($languageId);

        try {
            // Fetch exercises
            $this->info('Fetching exercises from wger API...');
            $limit = (int) $this->option('limit');
            $wgerExercises = $service->fetchExercises($limit);

            $total = count($wgerExercises);
            $this->info("Fetched {$total} exercises from wger.");
            $this->newLine();

            // Apply max limit if specified
            $max = $this->option('max');
            if ($max !== null) {
                $max = (int) $max;
                $wgerExercises = array_slice($wgerExercises, 0, $max);
                $this->info("Limited to first {$max} exercises.");
            }

            // Transform and import
            $this->info('Transforming and importing exercises...');
            $progressBar = $this->output->createProgressBar(count($wgerExercises));
            $progressBar->start();

            foreach ($wgerExercises as $wgerExercise) {
                $this->processExercise($wgerExercise, $service);
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);

            // Display summary
            $this->displaySummary();

            Log::info('Exercise import completed', [
                'imported' => $this->imported,
                'skipped' => $this->skipped,
                'failed' => $this->failed,
                'images' => $this->imagesDownloaded,
            ]);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            Log::error('Exercise import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }

    /**
     * Handle fresh import by deleting existing exercises
     */
    protected function handleFreshImport(): void
    {
        $this->warn('Deleting existing exercises...');

        $count = Exercise::query()->count();
        Exercise::query()->delete();

        $this->info("Deleted {$count} existing exercises.");
        $this->newLine();
    }

    /**
     * Process a single exercise
     */
    protected function processExercise(array $wgerExercise, WgerApiService $service): void
    {
        // Check if should import
        if (! WgerExerciseTransformer::shouldImport($wgerExercise)) {
            $this->skipped++;

            return;
        }

        try {
            DB::beginTransaction();

            // Transform exercise data
            $exerciseData = WgerExerciseTransformer::transform($wgerExercise);

            // Check for existing by wger_id
            $existing = Exercise::query()
                ->where('wger_id', $exerciseData['wger_id'])
                ->first();

            if ($existing) {
                // Update existing exercise
                $existing->update($exerciseData);
                $exercise = $existing;
            } else {
                // Create new exercise
                $exercise = Exercise::create($exerciseData);
                $this->imported++;
            }

            // Handle images if requested
            if ($this->option('images')) {
                $this->processExerciseImages($exercise, $wgerExercise['id'], $service);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->failed++;

            Log::error('Failed to import exercise', [
                'wger_id' => $wgerExercise['id'] ?? null,
                'name' => $wgerExercise['name'] ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Process exercise images
     */
    protected function processExerciseImages(Exercise $exercise, int $wgerId, WgerApiService $service): void
    {
        try {
            $images = $service->fetchExerciseImages($wgerId);

            if (empty($images)) {
                return;
            }

            // Download first image as primary
            $primaryImage = $images[0] ?? null;

            if ($primaryImage && ! empty($primaryImage['image'])) {
                $localPath = $service->downloadImage($primaryImage['image']);

                if ($localPath) {
                    // Update exercise with image path
                    $exercise->update([
                        'image_url' => $localPath,
                        'thumbnail_url' => $primaryImage['thumbnail'] ?? null,
                    ]);

                    $this->imagesDownloaded++;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to download exercise images', [
                'exercise_id' => $exercise->id,
                'wger_id' => $wgerId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Display import summary
     */
    protected function displaySummary(): void
    {
        $this->info('Import Summary');
        $this->info('==============');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Imported (new)', $this->imported],
                ['Skipped', $this->skipped],
                ['Failed', $this->failed],
                ['Images Downloaded', $this->imagesDownloaded],
                ['Total Exercises in DB', Exercise::count()],
            ]
        );
    }
}
