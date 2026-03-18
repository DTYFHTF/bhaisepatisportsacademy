<?php

namespace App\Services;

class SizingService
{
    private array $sizeCharts = [
        'JACKET' => [
            'XS'  => ['chest_min' => 84,  'chest_max' => 88],
            'S'   => ['chest_min' => 88,  'chest_max' => 92],
            'M'   => ['chest_min' => 92,  'chest_max' => 96],
            'L'   => ['chest_min' => 96,  'chest_max' => 100],
            'XL'  => ['chest_min' => 100, 'chest_max' => 106],
            'XXL' => ['chest_min' => 106, 'chest_max' => 114],
        ],
        'TOP' => [
            'XS'  => ['chest_min' => 82,  'chest_max' => 86],
            'S'   => ['chest_min' => 86,  'chest_max' => 90],
            'M'   => ['chest_min' => 90,  'chest_max' => 94],
            'L'   => ['chest_min' => 94,  'chest_max' => 98],
            'XL'  => ['chest_min' => 98,  'chest_max' => 104],
            'XXL' => ['chest_min' => 104, 'chest_max' => 112],
        ],
        'BOTTOM' => [
            'XS'  => ['chest_min' => 66, 'chest_max' => 70],
            'S'   => ['chest_min' => 70, 'chest_max' => 76],
            'M'   => ['chest_min' => 76, 'chest_max' => 82],
            'L'   => ['chest_min' => 82, 'chest_max' => 88],
            'XL'  => ['chest_min' => 88, 'chest_max' => 96],
            'XXL' => ['chest_min' => 96, 'chest_max' => 106],
        ],
    ];

    private array $sizeOrder = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

    public function recommend(array $input): array
    {
        $category = $input['category'] ?? 'TOP';
        $heightCm = $input['height_cm'];
        $weightKg = $input['weight_kg'];
        $chestCm = $input['chest_cm'] ?? null;
        $fitPreference = $input['fit_preference'] ?? 'regular';

        $chest = $chestCm ?? $this->estimateChest($heightCm, $weightKg);
        $baseSize = $this->lookupByChest($chest, $category);
        $adjustedSize = $this->applyFitOffset($baseSize, $fitPreference);
        $confidence = $chestCm ? 'high' : 'medium';

        return [
            'recommended_size' => $adjustedSize,
            'confidence'       => $confidence,
            'estimated_chest'  => round($chest),
        ];
    }

    private function estimateChest(float $height, float $weight): float
    {
        return ($height * 0.38) + ($weight * 0.27) + 16;
    }

    private function lookupByChest(float $chest, string $category): string
    {
        $chart = $this->sizeCharts[$category] ?? $this->sizeCharts['TOP'];

        foreach ($chart as $size => $range) {
            if ($chest >= $range['chest_min'] && $chest < $range['chest_max']) {
                return $size;
            }
        }

        return $chest < ($chart['XS']['chest_min'] ?? 82) ? 'XS' : 'XXL';
    }

    private function applyFitOffset(string $size, string $preference): string
    {
        $index = array_search($size, $this->sizeOrder);

        if ($index === false) {
            return $size;
        }

        return match ($preference) {
            'slim'    => $this->sizeOrder[max(0, $index - 1)],
            'relaxed' => $this->sizeOrder[min(count($this->sizeOrder) - 1, $index + 1)],
            default   => $size,
        };
    }
}
