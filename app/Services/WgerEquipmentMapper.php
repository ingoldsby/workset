<?php

namespace App\Services;

use App\Enums\EquipmentType;

class WgerEquipmentMapper
{
    /**
     * Map wger equipment ID to our EquipmentType enum
     *
     * Based on wger's equipment API:
     * https://wger.de/api/v2/equipment/
     */
    public static function map(int $wgerId): ?EquipmentType
    {
        return match ($wgerId) {
            1 => EquipmentType::Barbell,
            2 => EquipmentType::Dumbbell,
            3 => EquipmentType::Bodyweight, // Gym mat
            4, 5 => null, // Bench/Incline bench - not equipment per se
            6 => EquipmentType::Bodyweight, // Pull-up bar
            7 => EquipmentType::Bodyweight, // None/bodyweight
            8 => EquipmentType::Cable,
            9 => EquipmentType::Machine,
            10 => EquipmentType::Kettlebell,
            11 => EquipmentType::EZBar, // EZ curl bar
            12 => EquipmentType::Band, // Resistance band
            13 => EquipmentType::Machine, // Exercise ball counts as machine for our purposes
            14 => EquipmentType::SmithMachine,
            default => null,
        };
    }

    /**
     * Map multiple wger equipment IDs to our EquipmentType enums
     *
     * @param array<int> $wgerIds
     * @return array<EquipmentType>
     */
    public static function mapMultiple(array $wgerIds): array
    {
        $mapped = [];

        foreach ($wgerIds as $id) {
            $equipment = self::map($id);
            if ($equipment !== null && ! in_array($equipment, $mapped, true)) {
                $mapped[] = $equipment;
            }
        }

        return $mapped;
    }

    /**
     * Determine primary equipment from list of wger IDs
     * Returns the most specific equipment type, preferring non-bodyweight
     */
    public static function determinePrimary(array $wgerIds): ?EquipmentType
    {
        $mapped = self::mapMultiple($wgerIds);

        if (empty($mapped)) {
            return EquipmentType::Bodyweight;
        }

        // Prefer non-bodyweight equipment as primary
        $nonBodyweight = array_filter(
            $mapped,
            fn (EquipmentType $eq) => $eq !== EquipmentType::Bodyweight
        );

        if (! empty($nonBodyweight)) {
            return reset($nonBodyweight);
        }

        return EquipmentType::Bodyweight;
    }

    /**
     * Get equipment variants (all mapped equipment except primary)
     *
     * @param array<int> $wgerIds
     * @return array<string>
     */
    public static function getVariants(array $wgerIds, EquipmentType $primary): array
    {
        $mapped = self::mapMultiple($wgerIds);

        return array_values(
            array_map(
                fn (EquipmentType $eq) => $eq->value,
                array_filter(
                    $mapped,
                    fn (EquipmentType $eq) => $eq !== $primary
                )
            )
        );
    }
}
