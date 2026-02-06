<?php

namespace App\Services;

use App\Models\MineArea;
use App\Models\ProductionForecast;
use App\Models\MineAreaProduction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProductionForecastService
{
    /**
     * Generate production forecast using historical data
     */
    public function generateForecast(MineArea $mineArea, $daysAhead = 7): Collection
    {
        $forecasts = collect();

        // Get last 90 days of production data
        $historicalData = $mineArea->production()
            ->whereDate('date', '>=', now()->subDays(90))
            ->orderBy('date')
            ->get()
            ->groupBy('material_name');

        foreach ($historicalData as $materialName => $records) {
            if ($records->count() < 7) {
                continue; // Need at least 7 data points
            }

            for ($i = 1; $i <= $daysAhead; $i++) {
                $forecastDate = now()->addDays($i)->toDateString();
                
                $predicted = $this->predictProduction($records, $i);
                $confidence = $this->calculateConfidence($records);

                $forecast = ProductionForecast::updateOrCreate(
                    [
                        'mine_area_id' => $mineArea->id,
                        'forecast_date' => $forecastDate,
                        'material_name' => $materialName,
                    ],
                    [
                        'predicted_tonnage' => $predicted,
                        'confidence_score' => $confidence,
                        'model_version' => '1.0',
                        'factors' => $this->getFactors($mineArea),
                    ]
                );

                $forecasts->push($forecast);
            }
        }

        return $forecasts;
    }

    /**
     * Predict production using weighted moving average
     */
    private function predictProduction(Collection $records, int $daysAhead): float
    {
        $tonnages = $records->pluck('material_tonnage')->filter()->toArray();
        
        if (empty($tonnages)) {
            return 0;
        }

        // Weighted moving average with exponential smoothing
        $weights = array_reverse(range(1, count($tonnages)));
        $weightSum = array_sum($weights);
        
        $weighted = 0;
        foreach ($tonnages as $i => $tonnage) {
            $weighted += $tonnage * $weights[$i];
        }

        $avg = $weighted / $weightSum;

        // Add trend factor
        $lastValue = end($tonnages);
        $trendFactor = 1 + (($lastValue - $avg) / $avg * 0.1);

        return max(0, $avg * $trendFactor);
    }

    /**
     * Calculate confidence score (0-1)
     */
    private function calculateConfidence(Collection $records): float
    {
        $tonnages = $records->pluck('material_tonnage')->filter()->toArray();
        
        if (count($tonnages) < 7) {
            return 0.4;
        }

        $mean = array_sum($tonnages) / count($tonnages);
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $tonnages)) / count($tonnages);
        $stdDev = sqrt($variance);

        $coefficientOfVariation = $stdDev / $mean;

        // Lower variation = higher confidence
        $confidence = max(0.5, 1 - ($coefficientOfVariation / 2));

        return min(0.95, $confidence);
    }

    /**
     * Get factors affecting prediction
     */
    private function getFactors(MineArea $mineArea): array
    {
        $machines = $mineArea->machines()->where('status', 'online')->count();
        $activeSensors = $mineArea->sensors()->where('status', 'active')->count() ?? 0;
        
        return [
            'active_machines' => $machines,
            'active_sensors' => $activeSensors,
            'area_status' => $mineArea->status,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Get forecast accuracy metrics
     */
    public function getAccuracyMetrics(MineArea $mineArea, int $days = 30): array
    {
        $forecasts = ProductionForecast::where('mine_area_id', $mineArea->id)
            ->whereDate('created_at', '>=', now()->subDays($days))
            ->get();

        if ($forecasts->isEmpty()) {
            return [
                'count' => 0,
                'mae' => null,
                'rmse' => null,
                'accuracy' => null,
            ];
        }

        $errors = [];
        foreach ($forecasts as $forecast) {
            $actual = MineAreaProduction::where('mine_area_id', $mineArea->id)
                ->where('material_name', $forecast->material_name)
                ->whereDate('date', $forecast->forecast_date)
                ->value('material_tonnage');

            if ($actual) {
                $errors[] = abs($forecast->predicted_tonnage - $actual);
            }
        }

        if (empty($errors)) {
            return ['count' => $forecasts->count(), 'mae' => null, 'rmse' => null, 'accuracy' => null];
        }

        $mae = array_sum($errors) / count($errors);
        $rmse = sqrt(array_sum(array_map(fn($e) => $e ** 2, $errors)) / count($errors));

        return [
            'count' => $forecasts->count(),
            'mae' => round($mae, 2),
            'rmse' => round($rmse, 2),
            'accuracy' => round((1 - ($mae / 100)) * 100, 2),
        ];
    }

    /**
     * Get upcoming forecasts
     */
    public function getUpcomingForecasts(MineArea $mineArea, int $days = 7): Collection
    {
        return ProductionForecast::where('mine_area_id', $mineArea->id)
            ->whereBetween('forecast_date', [today(), today()->addDays($days)])
            ->orderBy('forecast_date')
            ->get();
    }
}
