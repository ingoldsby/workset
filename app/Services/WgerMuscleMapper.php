<?php

namespace App\Services;

use App\Enums\MuscleGroup;

class WgerMuscleMapper
{
    /**
     * Map wger muscle ID to our MuscleGroup enum
     *
     * Based on wger's muscle API:
     * https://wger.de/api/v2/muscle/
     */
    public static function map(int $wgerId): ?MuscleGroup
    {
        return match ($wgerId) {
            1, 13 => MuscleGroup::Biceps, // Biceps brachii, Brachialis
            2 => MuscleGroup::Shoulders, // Anterior deltoid
            3 => MuscleGroup::Chest, // Serratus anterior
            4 => MuscleGroup::Chest, // Pectoralis major
            5 => MuscleGroup::Triceps, // Triceps brachii
            6 => MuscleGroup::Abs, // Rectus abdominis
            7, 15 => MuscleGroup::Calves, // Gastrocnemius, Soleus
            8 => MuscleGroup::Glutes, // Gluteus maximus
            9 => MuscleGroup::Traps, // Trapezius
            10 => MuscleGroup::Quads, // Quadriceps femoris
            11 => MuscleGroup::Hamstrings, // Biceps femoris
            12 => MuscleGroup::Lats, // Latissimus dorsi
            14 => MuscleGroup::Obliques, // Obliquus externus abdominis
            default => null,
        };
    }

    /**
     * Map multiple wger muscle IDs to our MuscleGroup enums
     *
     * @param array<int> $wgerIds
     * @return array<MuscleGroup>
     */
    public static function mapMultiple(array $wgerIds): array
    {
        $mapped = [];

        foreach ($wgerIds as $id) {
            $muscle = self::map($id);
            if ($muscle !== null && ! in_array($muscle, $mapped, true)) {
                $mapped[] = $muscle;
            }
        }

        return $mapped;
    }

    /**
     * Determine primary muscle from list of wger muscle IDs
     * Uses wger's designation of "primary" muscles
     *
     * @param array<int> $primaryWgerIds
     * @param array<int> $secondaryWgerIds
     */
    public static function determinePrimary(array $primaryWgerIds, array $secondaryWgerIds = []): ?MuscleGroup
    {
        // First try primary muscles from wger
        $primaryMapped = self::mapMultiple($primaryWgerIds);

        if (! empty($primaryMapped)) {
            return reset($primaryMapped);
        }

        // Fallback to secondary if no primary
        $secondaryMapped = self::mapMultiple($secondaryWgerIds);

        if (! empty($secondaryMapped)) {
            return reset($secondaryMapped);
        }

        return null;
    }

    /**
     * Get secondary muscles (all mapped muscles except primary)
     *
     * @param array<int> $primaryWgerIds
     * @param array<int> $secondaryWgerIds
     * @return array<string>
     */
    public static function getSecondary(
        array $primaryWgerIds,
        array $secondaryWgerIds,
        MuscleGroup $primaryMuscle
    ): array {
        $allPrimary = self::mapMultiple($primaryWgerIds);
        $allSecondary = self::mapMultiple($secondaryWgerIds);

        // Combine all muscles except the designated primary
        $combined = array_merge($allPrimary, $allSecondary);

        return array_values(
            array_map(
                fn (MuscleGroup $mg) => $mg->value,
                array_filter(
                    $combined,
                    fn (MuscleGroup $mg) => $mg !== $primaryMuscle
                )
            )
        );
    }
}
