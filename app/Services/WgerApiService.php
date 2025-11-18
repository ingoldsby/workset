<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WgerApiService
{
    private const BASE_URL = 'https://wger.de/api/v2/';
    private const DEFAULT_LANGUAGE = 2; // English

    public function __construct(
        private int $languageId = self::DEFAULT_LANGUAGE,
    ) {}

    /**
     * Get HTTP client with base configuration
     */
    public function client(): PendingRequest
    {
        return Http::baseUrl(self::BASE_URL)
            ->timeout(30)
            ->retry(3, 1000)
            ->withHeaders([
                'Accept' => 'application/json',
            ]);
    }

    /**
     * Fetch all exercises from wger API
     */
    public function fetchExercises(int $limit = 100): array
    {
        $exercises = [];
        $offset = 0;
        $hasMore = true;

        while ($hasMore) {
            try {
                $response = $this->client()->get('exercise/', [
                    'language' => $this->languageId,
                    'limit' => $limit,
                    'offset' => $offset,
                ]);

                if (! $response->successful()) {
                    Log::error('wger API error', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    break;
                }

                $data = $response->json();
                $exercises = array_merge($exercises, $data['results'] ?? []);

                $hasMore = ! empty($data['next']);
                $offset += $limit;

                // Rate limiting - be nice to the API
                if ($hasMore) {
                    usleep(500000); // 0.5 second delay
                }
            } catch (\Exception $e) {
                Log::error('wger API exception', [
                    'message' => $e->getMessage(),
                    'offset' => $offset,
                ]);
                break;
            }
        }

        return $exercises;
    }

    /**
     * Fetch exercise details including images
     */
    public function fetchExerciseDetails(int $exerciseId): ?array
    {
        try {
            $response = $this->client()->get("exercise/{$exerciseId}/");

            if (! $response->successful()) {
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Failed to fetch exercise details', [
                'exercise_id' => $exerciseId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Fetch all equipment from wger API
     */
    public function fetchEquipment(): array
    {
        try {
            $response = $this->client()->get('equipment/', [
                'limit' => 100,
            ]);

            if (! $response->successful()) {
                return [];
            }

            return $response->json()['results'] ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch equipment', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Fetch all muscles from wger API
     */
    public function fetchMuscles(): array
    {
        try {
            $response = $this->client()->get('muscle/', [
                'limit' => 100,
            ]);

            if (! $response->successful()) {
                return [];
            }

            return $response->json()['results'] ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch muscles', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Fetch exercise images
     */
    public function fetchExerciseImages(int $exerciseId): array
    {
        try {
            $response = $this->client()->get('exerciseimage/', [
                'exercise' => $exerciseId,
                'limit' => 10,
            ]);

            if (! $response->successful()) {
                return [];
            }

            return $response->json()['results'] ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch exercise images', [
                'exercise_id' => $exerciseId,
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Download and store exercise image
     */
    public function downloadImage(string $imageUrl): ?string
    {
        try {
            $response = Http::timeout(15)->get($imageUrl);

            if (! $response->successful()) {
                return null;
            }

            $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
            $filename = 'exercises/' . uniqid() . '.' . $extension;

            \Illuminate\Support\Facades\Storage::disk('public')->put(
                $filename,
                $response->body()
            );

            return $filename;
        } catch (\Exception $e) {
            Log::error('Failed to download image', [
                'url' => $imageUrl,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
