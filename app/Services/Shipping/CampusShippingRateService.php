<?php

namespace App\Services\Shipping;

use App\Models\Transaction;
use App\Models\University;

class CampusShippingRateService
{
    public const TIER_INTRA_CAMPUS = 'intra_campus';

    public const TIER_SAME_CITY = 'same_city';

    public const TIER_REGIONAL = 'regional';

    public const TIER_INTER_CITY = 'inter_city';

    public const TIERS = [
        self::TIER_INTRA_CAMPUS => [
            'name' => 'Satu Area Kampus',
            'badge' => 'Intra-Kampus',
            'description' => 'Pengiriman sesama civitas dalam lingkungan satu kampus.',
        ],
        self::TIER_SAME_CITY => [
            'name' => 'Antar-Kampus Satu Kota',
            'badge' => 'Lokal Se-Kota',
            'description' => 'Pengiriman lokal antar-kampus dalam satu wilayah kota/kabupaten.',
        ],
        self::TIER_REGIONAL => [
            'name' => 'Antar-Kota Regional',
            'badge' => 'Jawa Tengah & DIY',
            'description' => 'Pengiriman antar-kota se-provinsi atau wilayah sekitar.',
        ],
        self::TIER_INTER_CITY => [
            'name' => 'Antar-Provinsi / Nasional',
            'badge' => 'Lintas Provinsi',
            'description' => 'Pengiriman ekspedisi antar-provinsi ke seluruh Indonesia.',
        ],
    ];

    /**
     * Calculate spherical distance between two coordinate points using the Haversine formula.
     */
    public function calculateDistanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371.0;

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadiusKm * $c, 2);
    }

    /**
     * Determine the distance and zone tier between origin and destination universities.
     *
     * @return array{distance_km: float, tier: string, tier_data: array}
     */
    public function resolveDistanceAndTier(University $origin, University $destination): array
    {
        if ($origin->id === $destination->id) {
            $distanceKm = 0.0;
            $tier = self::TIER_INTRA_CAMPUS;

            return [
                'distance_km' => $distanceKm,
                'tier' => $tier,
                'tier_data' => self::TIERS[$tier],
            ];
        }

        $originCoords = $origin->getCoordinates();
        $destCoords = $destination->getCoordinates();

        if ($originCoords && $destCoords) {
            $distanceKm = $this->calculateDistanceKm(
                $originCoords['lat'],
                $originCoords['lng'],
                $destCoords['lat'],
                $destCoords['lng']
            );
        } else {
            // Fallback estimation using city/province heuristics if coordinates are not set
            if ($origin->city && $destination->city && strtolower($origin->city) === strtolower($destination->city)) {
                $distanceKm = 6.5;
            } elseif ($origin->province && $destination->province && strtolower($origin->province) === strtolower($destination->province)) {
                $distanceKm = 75.0;
            } else {
                $distanceKm = 350.0;
            }
        }

        // Determine tier based on calculated distance
        if ($distanceKm <= 1.0) {
            $tier = self::TIER_INTRA_CAMPUS;
        } elseif ($distanceKm <= 25.0) {
            $tier = self::TIER_SAME_CITY;
        } elseif ($distanceKm <= 150.0) {
            $tier = self::TIER_REGIONAL;
        } else {
            $tier = self::TIER_INTER_CITY;
        }

        return [
            'distance_km' => $distanceKm,
            'tier' => $tier,
            'tier_data' => self::TIERS[$tier],
        ];
    }

    /**
     * Compute tariff breakdown for all supported couriers based on distance and zone.
     *
     * @return array<string, array{name: string, cost: float, formatted_cost: string, etd: string, available: bool, unavailable_reason?: string}>
     */
    public function calculateAllCourierRates(float $distanceKm, string $tier): array
    {
        $rates = [];

        // 1. Kurir Mahasiswa Kampus
        $kampusAvailable = in_array($tier, [self::TIER_INTRA_CAMPUS, self::TIER_SAME_CITY], true);
        $kampusCost = match ($tier) {
            self::TIER_INTRA_CAMPUS => 5000.00,
            self::TIER_SAME_CITY => 8000.00,
            default => 0.00,
        };
        $kampusEtd = match ($tier) {
            self::TIER_INTRA_CAMPUS => '1 - 3 Jam (Hari Ini)',
            self::TIER_SAME_CITY => '3 - 5 Jam (Hari Ini)',
            default => 'Tidak Tersedia',
        };

        $rates['kampus'] = [
            'name' => Transaction::COURIERS['kampus'] ?? 'Kurir Mahasiswa Kampus',
            'cost' => $kampusCost,
            'formatted_cost' => $kampusAvailable ? 'Rp '.number_format($kampusCost, 0, ',', '.') : '-',
            'etd' => $kampusEtd,
            'available' => $kampusAvailable,
            'badge' => 'Tarif Mahasiswa',
        ];
        if (! $kampusAvailable) {
            $rates['kampus']['unavailable_reason'] = 'Kurir mahasiswa hanya melayani area kampus dan se-kota (maks. 25 km).';
        }

        // 2. GoSend / GrabExpress Instant
        $gosendAvailable = in_array($tier, [self::TIER_INTRA_CAMPUS, self::TIER_SAME_CITY], true) && $distanceKm <= 30.0;
        $gosendCost = match ($tier) {
            self::TIER_INTRA_CAMPUS => 8000.00,
            self::TIER_SAME_CITY => 14000.00,
            default => 0.00,
        };
        $gosendEtd = match ($tier) {
            self::TIER_INTRA_CAMPUS => '1 - 2 Jam (Instant)',
            self::TIER_SAME_CITY => '1 - 3 Jam (Instant)',
            default => 'Tidak Tersedia',
        };

        $rates['gosend'] = [
            'name' => Transaction::COURIERS['gosend'] ?? 'GoSend / GrabExpress',
            'cost' => $gosendCost,
            'formatted_cost' => $gosendAvailable ? 'Rp '.number_format($gosendCost, 0, ',', '.') : '-',
            'etd' => $gosendEtd,
            'available' => $gosendAvailable,
            'badge' => 'Paling Cepat',
        ];
        if (! $gosendAvailable) {
            $rates['gosend']['unavailable_reason'] = 'Layanan kurir instan melebihi batas jarak maksimal (30 km).';
        }

        // 3. J&T Express
        $jntCost = match ($tier) {
            self::TIER_INTRA_CAMPUS => 7000.00,
            self::TIER_SAME_CITY => 9000.00,
            self::TIER_REGIONAL => 14000.00,
            self::TIER_INTER_CITY => 15000.00 + (max(0, floor(($distanceKm - 150) / 100)) * 3000.00),
            default => 10000.00,
        };
        $jntEtd = match ($tier) {
            self::TIER_INTRA_CAMPUS => 'Hari Ini / Besok',
            self::TIER_SAME_CITY => '1 Hari Kerja',
            self::TIER_REGIONAL => '1 - 2 Hari Kerja',
            self::TIER_INTER_CITY => '2 - 3 Hari Kerja',
            default => '2 - 3 Hari Kerja',
        };

        $rates['jnt'] = [
            'name' => Transaction::COURIERS['jnt'] ?? 'J&T Express',
            'cost' => $jntCost,
            'formatted_cost' => 'Rp '.number_format($jntCost, 0, ',', '.'),
            'etd' => $jntEtd,
            'available' => true,
            'badge' => 'Populer',
        ];

        // 4. JNE Express
        $jneCost = match ($tier) {
            self::TIER_INTRA_CAMPUS => 7000.00,
            self::TIER_SAME_CITY => 9000.00,
            self::TIER_REGIONAL => 15000.00,
            self::TIER_INTER_CITY => 16000.00 + (max(0, floor(($distanceKm - 150) / 100)) * 3500.00),
            default => 10000.00,
        };
        $jneEtd = match ($tier) {
            self::TIER_INTRA_CAMPUS => '1 Hari Kerja',
            self::TIER_SAME_CITY => '1 Hari Kerja',
            self::TIER_REGIONAL => '1 - 2 Hari Kerja',
            self::TIER_INTER_CITY => '2 - 3 Hari Kerja',
            default => '2 - 3 Hari Kerja',
        };

        $rates['jne'] = [
            'name' => Transaction::COURIERS['jne'] ?? 'JNE Express',
            'cost' => $jneCost,
            'formatted_cost' => 'Rp '.number_format($jneCost, 0, ',', '.'),
            'etd' => $jneEtd,
            'available' => true,
            'badge' => 'Reguler',
        ];

        // 5. SiCepat Ekspres
        $sicepatCost = match ($tier) {
            self::TIER_INTRA_CAMPUS => 7000.00,
            self::TIER_SAME_CITY => 9000.00,
            self::TIER_REGIONAL => 14000.00,
            self::TIER_INTER_CITY => 15000.00 + (max(0, floor(($distanceKm - 150) / 100)) * 3000.00),
            default => 10000.00,
        };
        $sicepatEtd = match ($tier) {
            self::TIER_INTRA_CAMPUS => '1 Hari Kerja',
            self::TIER_SAME_CITY => '1 Hari Kerja',
            self::TIER_REGIONAL => '1 - 2 Hari Kerja',
            self::TIER_INTER_CITY => '2 - 3 Hari Kerja',
            default => '2 - 3 Hari Kerja',
        };

        $rates['sicepat'] = [
            'name' => Transaction::COURIERS['sicepat'] ?? 'SiCepat Ekspres',
            'cost' => $sicepatCost,
            'formatted_cost' => 'Rp '.number_format($sicepatCost, 0, ',', '.'),
            'etd' => $sicepatEtd,
            'available' => true,
            'badge' => 'Ekonomis',
        ];

        // 6. Ekspedisi Kurir Lainnya
        $lainnyaCost = match ($tier) {
            self::TIER_INTRA_CAMPUS => 7000.00,
            self::TIER_SAME_CITY => 10000.00,
            self::TIER_REGIONAL => 15000.00,
            self::TIER_INTER_CITY => 18000.00 + (max(0, floor(($distanceKm - 150) / 100)) * 3000.00),
            default => 12000.00,
        };

        $rates['lainnya'] = [
            'name' => Transaction::COURIERS['lainnya'] ?? 'Ekspedisi Kurir Lainnya',
            'cost' => $lainnyaCost,
            'formatted_cost' => 'Rp '.number_format($lainnyaCost, 0, ',', '.'),
            'etd' => '2 - 4 Hari Kerja',
            'available' => true,
            'badge' => 'Alternatif',
        ];

        return $rates;
    }

    /**
     * Calculate comprehensive shipping rate estimation between two campuses.
     *
     * @return array{
     *     origin_university: array,
     *     destination_university: array,
     *     distance_km: float,
     *     distance_label: string,
     *     zone_tier: string,
     *     zone_label: string,
     *     zone_description: string,
     *     rates: array,
     *     selected_courier: string,
     *     cost: float,
     *     formatted_cost: string,
     *     etd: string
     * }
     */
    public function calculateRate(University $origin, University $destination, ?string $courier = null): array
    {
        $resolved = $this->resolveDistanceAndTier($origin, $destination);
        $distanceKm = $resolved['distance_km'];
        $tier = $resolved['tier'];
        $tierData = $resolved['tier_data'];

        $allRates = $this->calculateAllCourierRates($distanceKm, $tier);

        // Determine default courier if not passed or unavailable
        $selectedCourier = $courier && isset($allRates[$courier]) ? $courier : 'jnt';

        // If the requested courier is not available for this distance, fall back to jnt
        if (! $allRates[$selectedCourier]['available']) {
            $selectedCourier = 'jnt';
        }

        $rateInfo = $allRates[$selectedCourier];

        return [
            'origin_university' => [
                'id' => $origin->id,
                'name' => $origin->name,
                'slug' => $origin->slug,
                'city' => $origin->city,
                'province' => $origin->province,
                'formatted_location' => $origin->formatted_location,
            ],
            'destination_university' => [
                'id' => $destination->id,
                'name' => $destination->name,
                'slug' => $destination->slug,
                'city' => $destination->city,
                'province' => $destination->province,
                'formatted_location' => $destination->formatted_location,
            ],
            'distance_km' => $distanceKm,
            'distance_label' => $distanceKm <= 0.05 ? '< 1 km (Satu Kampus)' : number_format($distanceKm, 1, ',', '.').' km',
            'zone_tier' => $tier,
            'zone_label' => $tierData['name'],
            'zone_badge' => $tierData['badge'],
            'zone_description' => $tierData['description'],
            'rates' => $allRates,
            'selected_courier' => $selectedCourier,
            'cost' => (float) $rateInfo['cost'],
            'formatted_cost' => $rateInfo['formatted_cost'],
            'etd' => $rateInfo['etd'],
        ];
    }
}
