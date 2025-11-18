<?php

namespace App\Services;

use App\Enums\ExerciseCategory;
use App\Enums\ExerciseLevel;
use App\Enums\ExerciseMechanics;

class WgerExerciseTransformer
{
    /**
     * Transform wger exerciseinfo data to our Exercise model format
     *
     * @param array $wgerExercise Exercise data from wger API exerciseinfo endpoint
     * @param int $languageId Language ID to extract (default 2 = English)
     * @return array Exercise attributes ready for database insertion
     */
    public static function transform(array $wgerExercise, int $languageId = 2): array
    {
        // Extract translation data for the specified language
        $translation = self::getTranslation($wgerExercise, $languageId);

        if (!$translation) {
            throw new \Exception("No translation found for exercise {$wgerExercise['id']} in language {$languageId}");
        }

        // Extract category ID early (needed for muscle fallback)
        $categoryId = is_array($wgerExercise['category'])
            ? $wgerExercise['category']['id']
            : $wgerExercise['category'];

        // Extract equipment IDs from the new structure
        $equipmentIds = array_map(fn($eq) => $eq['id'], $wgerExercise['equipment'] ?? []);

        // Extract muscle IDs from the new structure
        $primaryMuscleIds = array_map(fn($m) => $m['id'], $wgerExercise['muscles'] ?? []);
        $secondaryMuscleIds = array_map(fn($m) => $m['id'], $wgerExercise['muscles_secondary'] ?? []);

        // Map equipment
        $primaryEquipment = WgerEquipmentMapper::determinePrimary($equipmentIds);
        $equipmentVariants = $primaryEquipment
            ? WgerEquipmentMapper::getVariants($equipmentIds, $primaryEquipment)
            : [];

        // Map muscles (with category fallback)
        $primaryMuscle = WgerMuscleMapper::determinePrimary($primaryMuscleIds, $secondaryMuscleIds);

        // Fallback to category if no muscle data
        if (!$primaryMuscle && $categoryId) {
            $primaryMuscle = WgerMuscleMapper::mapCategory($categoryId);
        }

        $secondaryMuscles = $primaryMuscle
            ? WgerMuscleMapper::getSecondary($primaryMuscleIds, $secondaryMuscleIds, $primaryMuscle)
            : [];

        // Determine mechanics based on muscle involvement
        $mechanics = self::determineMechanics($primaryMuscleIds, $secondaryMuscleIds);

        // Determine category from wger category
        $category = self::determineCategory($categoryId);

        // Clean description (wger uses HTML)
        $description = self::cleanDescription($translation['description'] ?? '');

        // Build aliases from translation
        $aliases = $translation['aliases'] ?? [];

        return [
            'name' => trim($translation['name']),
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
            'language' => $languageId,
        ];
    }

    /**
     * Get translation for specified language from exerciseinfo translations array
     */
    public static function getTranslation(array $wgerExercise, int $languageId): ?array
    {
        $translations = $wgerExercise['translations'] ?? [];

        foreach ($translations as $translation) {
            if ($translation['language'] === $languageId) {
                return $translation;
            }
        }

        // Fallback to first available translation if specified language not found
        return $translations[0] ?? null;
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
     * Check if exercise should be imported
     * Filter out low-quality or incomplete exercises
     */
    public static function shouldImport(array $wgerExercise, int $languageId = 2): bool
    {
        // Must have translations
        if (empty($wgerExercise['translations'])) {
            return false;
        }

        // Get translation for the specified language
        $translation = self::getTranslation($wgerExercise, $languageId);

        if (!$translation) {
            return false;
        }

        // Must have a name
        if (empty($translation['name'])) {
            return false;
        }

        // Must have at least one muscle group OR a valid category
        $hasMuscles = ! empty($wgerExercise['muscles']) || ! empty($wgerExercise['muscles_secondary']);

        // Check if category maps to a muscle group
        $categoryId = is_array($wgerExercise['category'])
            ? $wgerExercise['category']['id']
            : $wgerExercise['category'];
        $categoryMapsToMuscle = $categoryId && WgerMuscleMapper::mapCategory($categoryId) !== null;

        if (! $hasMuscles && ! $categoryMapsToMuscle) {
            return false;
        }

        // Filter out very generic or placeholder exercises
        $name = strtolower($translation['name']);
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
     * @param int $languageId Language ID to use for filtering and transformation
     * @return array<array>
     */
    public static function transformMany(array $wgerExercises, int $languageId = 2): array
    {
        $transformed = [];

        foreach ($wgerExercises as $exercise) {
            if (self::shouldImport($exercise, $languageId)) {
                $transformed[] = self::transform($exercise, $languageId);
            }
        }

        return $transformed;
    }
}
