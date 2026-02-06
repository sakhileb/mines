<?php

namespace App\Services\AI;

use App\Models\Team;
use App\Models\Route;
use App\Models\Machine;
use App\Models\MineArea;
use App\Services\RoutePlanningService;

/**
 * Route Advisor AI Agent
 * Analyzes routes and provides optimization recommendations
 */
class RouteAdvisorAgent
{
    public function __construct(
        protected RoutePlanningService $routePlanningService
    ) {}

    public function analyze(Team $team): array
    {
        $recommendations = [];
        $insights = [];

        $routes = Route::where('team_id', $team->id)
            ->where('status', 'active')
            ->with('waypoints')
            ->get();

        foreach ($routes as $route) {
            // Analyze route efficiency
            $efficiency = $this->analyzeRouteEfficiency($route);
            
            if ($efficiency['improvement_possible'] > 15) {
                $recommendations[] = [
                    'category' => 'route',
                    'priority' => 'high',
                    'title' => "Route Optimization Opportunity: {$route->name}",
                    'description' => "Route can be optimized to save {$efficiency['time_savings']} minutes and {$efficiency['fuel_savings']} liters of fuel.",
                    'confidence_score' => 0.83,
                    'estimated_savings' => $efficiency['fuel_savings'] * 25,
                    'estimated_efficiency_gain' => $efficiency['improvement_possible'],
                    'related_route_id' => $route->id,
                    'data' => $efficiency,
                ];
            }
        }

        // Analyze area coverage
        $coverageAnalysis = $this->analyzeAreaCoverage($team);
        $recommendations = array_merge($recommendations, $coverageAnalysis);

        return [
            'recommendations' => $recommendations,
            'insights' => $insights,
        ];
    }

    protected function analyzeRouteEfficiency(Route $route): array
    {
        $directDistance = $this->routePlanningService->calculateDistance(
            $route->start_latitude,
            $route->start_longitude,
            $route->end_latitude,
            $route->end_longitude
        );

        $actualDistance = $route->total_distance;
        $detourPercent = (($actualDistance - $directDistance) / $directDistance) * 100;

        $improvementPossible = max(0, $detourPercent - 10); // 10% detour is acceptable
        $timeSavings = $improvementPossible * 0.5; // minutes
        $fuelSavings = $improvementPossible * 0.3; // liters

        return [
            'direct_distance' => round($directDistance, 2),
            'actual_distance' => $actualDistance,
            'detour_percent' => round($detourPercent, 2),
            'improvement_possible' => round($improvementPossible, 2),
            'time_savings' => round($timeSavings, 2),
            'fuel_savings' => round($fuelSavings, 2),
        ];
    }

    protected function analyzeAreaCoverage(Team $team): array
    {
        $recommendations = [];
        $areas = MineArea::where('team_id', $team->id)->get();

        foreach ($areas as $area) {
            $routesInArea = Route::where('team_id', $team->id)
                ->where('mine_area_id', $area->id)
                ->count();

            if ($routesInArea === 0 && $area->machines()->count() > 0) {
                $recommendations[] = [
                    'category' => 'route',
                    'priority' => 'medium',
                    'title' => "No Optimized Routes: {$area->name}",
                    'description' => "Mine area {$area->name} has no optimized routes defined. Create routes to improve efficiency.",
                    'confidence_score' => 0.70,
                    'estimated_efficiency_gain' => 20,
                    'related_mine_area_id' => $area->id,
                ];
            }
        }

        return $recommendations;
    }
}
