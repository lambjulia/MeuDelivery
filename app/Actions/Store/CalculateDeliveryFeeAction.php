<?php

namespace App\Actions\Store;

use App\Models\DeliveryZone;

class CalculateDeliveryFeeAction
{
    public function execute(int $companyId, ?string $district, ?string $city): array
    {
        if (! $district && ! $city) {
            return ['fee' => 0, 'zone' => null, 'estimated_time' => null, 'found' => false];
        }

        // Try to find a zone by district name (case-insensitive)
        $zones = DeliveryZone::where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        foreach ($zones as $zone) {
            $neighborhoods = array_map('mb_strtolower', $zone->neighborhoods ?? []);

            if ($district && in_array(mb_strtolower($district), $neighborhoods)) {
                return [
                    'found'          => true,
                    'fee'            => (float) $zone->delivery_fee,
                    'zone_id'        => $zone->id,
                    'zone_name'      => $zone->name,
                    'estimated_time' => $zone->estimated_time,
                ];
            }
        }

        return ['found' => false, 'fee' => null, 'zone' => null, 'estimated_time' => null];
    }
}
