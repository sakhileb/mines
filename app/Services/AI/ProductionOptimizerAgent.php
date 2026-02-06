<?php

namespace App\Services\AI;

use App\Models\Team;
use App\Models\MineArea;
use App\Models\MineAreaProduction;
use App\Services\ProductionForecastService;

/**
 * Production Optimizer AI Agent
 */
class ProductionOptimizerAgent
{
    public function __construct(
        protected ProductionForecastService $forecastService
    ) {}

    public function analyze(Team $team): array
    {
        $recommendations = [];
        $insights = [];

        $areas = MineArea::where('team_id', $team->id)->get();

        foreach ($areas as $area) {
            $production = MineAreaProduction::where('mine_area_id', $area->id)
                ->whereDate('date', '>=', now()->subDays(30))
                ->get();

            if ($production->isEmpty()) continue;

            $avgProduction = $production->avg('material_tonnage');
            $target = $area->target_production_tons ?? $avgProduction * 1.2;

            if ($avgProduction < $target * 0.8) {
                $recommendations[] = [
                    'category' => 'production',
                    'priority' => 'high',
                    'title' => "Underperforming Area: {$area->name}",
                    'description' => "Production is " . round($target - $avgProduction, 2) . " tons below target. Consider resource reallocation.",
                    'confidence_score' => 0.81,
                    'related_mine_area_id' => $area->id,
                    'data' => [
                        'current_average' => round($avgProduction, 2),
                        'target' => round($target, 2),
                        'deficit' => round($target - $avgProduction, 2),
                    ],
                ];
            }
        }

        return [
            'recommendations' => $recommendations,
            'insights' => $insights,
        ];
    }
}
