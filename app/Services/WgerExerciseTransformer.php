<?php

namespace App\Services;

use App\Enums\ExerciseCategory;
use App\Enums\ExerciseLevel;
use App\Enums\ExerciseMechanics;

class WgerExerciseTransformer
{
    /**
     * Transform wger exercise data to our Exercise model format
     *
     * @param array $wgerExercise Exercise data from wger API
     * @param array $wgerImages Optional image data from wger API
     * @return array Exercise attributes ready for database insertion
     */
    public static function transform(array $wgerExercise, array $wgerImages = []): array
    {
        $equipmentIds = $wgerExercise['equipment'] ?? [];
        $primaryMuscleIds = $wgerExercise['muscles'] ?? [];
        $secondaryMuscleIds = $wgerExercise['muscles_secondary'] ?? [];

        // Map equipment
        $primaryEquipment = WgerEquipmentMapper::determinePrimary($equipmentIds);
        $equipmentVariants = $primaryEquipment
            ? WgerEquipmentMapper::getVariants($equipmentIds, $primaryEquipment)
            : [];

        // Map muscles
        $primaryMuscle = WgerMuscleMapper::determinePrimary($primaryMuscleIds, $secondaryMuscleIds);
        $secondaryMuscles = $primaryMuscle
            ? WgerMuscleMapper::getSecondary($primaryMuscleIds, $secondaryMuscleIds, $primaryMuscle)
            : [];

        // Determine mechanics based on muscle involvement
        $mechanics = self::determineMechanics($primaryMuscleIds, $secondaryMuscleIds);

        // Determine category from wger category
        $category = self::determineCategory($wgerExercise['category'] ?? null);

        // Clean description (wger uses HTML)
        $description = self::cleanDescription($wgerExercise['description'] ?? '');

        // Build aliases from variations
        $aliases = self::buildAliases($wgerExercise);

        return [
            'name' => trim($wgerExercise['name']),
            'description' => $description,
            'category' => $category,
            'primary_muscle' => $primaryMuscle,
            'secondary_muscles' => $secondaryMuscles,
            'equipment' => $primaryEquipment,
            'equipment_variants' => $equipmentVariants,
            'mechanics' => $mechanics,
            'level' => ExerciseLevel::Intermediate, // Default level
            'aliases' => $aliases,
            'wger_id' => $wgerExercise['id'],
            'language' => $wgerExercise['language'] ?? 2, // Default English
        ];
    }

    /**
     * Determine exercise mechanics based on muscle involvement
     */
    protected static function determineMechanics(array $primaryMuscleIds, array $secondaryMuscleIds): ExerciseMechanics
    {
        $totalMuscles = count(array_unique([...$primaryMuscleIds, ...$secondaryMuscleIds]));

        // Compound: works multiple muscle groups
        // Isolation: primarily works one muscle group
        return $totalMuscles >= 3
            ? ExerciseMechanics::Compound
            : ExerciseMechanics::Isolation;
    }

    /**
     * Map wger category to our ExerciseCategory enum
     */
    protected static function determineCategory(?int $wgerCategoryId): ExerciseCategory
    {
        // Wger categories:
        // 10: Abs
        // 8: Arms
        // 12: Back
        // 14: Calves
        // 11: Chest
        // 9: Legs
        // 13: Shoulders
        // Most exercises are strength-based
        return ExerciseCategory::Strength;
    }

    /**
     * Clean HTML description from wger
     */
    protected static function cleanDescription(string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // Strip HTML tags
        $text = strip_tags($html);

        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Remove excessive whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        // Trim
        $text = trim($text);

        // Limit length to reasonable amount
        if (strlen($text) > 1000) {
            $text = substr($text, 0, 997) . '...';
        }

        return $text;
    }

    /**
     * Build aliases array from wger exercise data
     *
     * @return array<string>
     */
    protected static function buildAliases(array $wgerExercise): array
    {
        $aliases = [];

        // Add alternate names if present
        if (! empty($wgerExercise['aliases'])) {
            if (is_array($wgerExercise['aliases'])) {
                $aliases = array_merge($aliases, $wgerExercise['aliases']);
            } elseif (is_string($wgerExercise['aliases'])) {
                // Handle comma-separated aliases
                $parsed = array_map('trim', explode(',', $wgerExercise['aliases']));
                $aliases = array_merge($aliases, $parsed);
            }
        }

        // Filter out empty and duplicate aliases
        $aliases = array_filter(array_unique($aliases));

        return array_values($aliases);
    }

    /**
     * Check if exercise should be imported
     * Filter out low-quality or incomplete exercises
     */
    public static function shouldImport(array $wgerExercise): bool
    {
        // Must have a name
        if (empty($wgerExercise['name'])) {
            return false;
        }

        // Must have at least one muscle group
        $hasMuscles = ! empty($wgerExercise['muscles']) || ! empty($wgerExercise['muscles_secondary']);

        if (! $hasMuscles) {
            return false;
        }

        // Filter out very generic or placeholder exercises
        $name = strtolower($wgerExercise['name']);
        $blacklist = ['test', 'placeholder', 'example', 'demo'];

        foreach ($blacklist as $term) {
            if (str_contains($name, $term)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Batch transform multiple exercises
     *
     * @param array<array> $wgerExercises
     * @return array<array>
     */
    public static function transformMany(array $wgerExercises): array
    {
        $transformed = [];

        foreach ($wgerExercises as $exercise) {
            if (self::shouldImport($exercise)) {
                $transformed[] = self::transform($exercise);
            }
        }

        return $transformed;
    }
}
