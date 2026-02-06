<?php

namespace App\Services\AI;

use App\Models\Team;
use App\Models\Machine;
use App\Models\MineArea;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Mine Area Detector AI Agent
 * Analyzes GPS data to auto-detect mine pit areas and provides area optimization recommendations
 */
class MineAreaDetectorAgent
{
    /**
     * Analyze GPS patterns and detect potential mine areas
     */
    public function analyze(Team $team): array
    {
        $recommendations = [];
        $insights = [];

        // Analyze machine location patterns
        $locationClusters = $this->detectLocationClusters($team);
        if ($locationClusters['recommendations']) {
            $recommendations = array_merge($recommendations, $locationClusters['recommendations']);
        }
        if ($locationClusters['insights']) {
            $insights = array_merge($insights, $locationClusters['insights']);
        }

        // Analyze existing areas
        $areaAnalysis = $this->analyzeExistingAreas($team);
        if ($areaAnalysis['recommendations']) {
            $recommendations = array_merge($recommendations, $areaAnalysis['recommendations']);
        }
        if ($areaAnalysis['insights']) {
            $insights = array_merge($insights, $areaAnalysis['insights']);
        }

        // Analyze area boundaries
        $boundaryAnalysis = $this->analyzeBoundaries($team);
        if ($boundaryAnalysis['recommendations']) {
            $recommendations = array_merge($recommendations, $boundaryAnalysis['recommendations']);
        }

        return [
            'recommendations' => $recommendations,
            'insights' => $insights,
        ];
    }

    /**
     * Detect clusters of machine activity that may indicate unmapped pit areas
     */
    protected function detectLocationClusters(Team $team): array
    {
        $recommendations = [];
        $insights = [];

        // Get recent machine locations from the machines themselves
        // Since there's no location history table, we'll use current machine positions
        $machines = Machine::where('team_id', $team->id)
            ->whereNotNull('last_location_latitude')
            ->whereNotNull('last_location_longitude')
            ->where('last_location_update', '>=', Carbon::now()->subDays(7))
            ->select('id', 'last_location_latitude', 'last_location_longitude', 'mine_area_id')
            ->get();

        if ($machines->isEmpty()) {
            return ['recommendations' => [], 'insights' => []];
        }

        // Convert to location array format
        $recentLocations = $machines->map(function ($machine) {
            return [
                'latitude' => $machine->last_location_latitude,
                'longitude' => $machine->last_location_longitude,
                'machine_id' => $machine->id,
            ];
        })->toArray();

        // Get existing mine areas
        $existingAreas = MineArea::where('team_id', $team->id)->get();

        // Simple clustering: group locations by proximity (within ~200m)
        $clusters = $this->groupLocationsByProximity($recentLocations, 0.002); // ~200m

        // Identify clusters outside existing areas
        $unmappedClusters = [];
        foreach ($clusters as $cluster) {
            $isInExistingArea = false;
            $centerLat = array_sum(array_column($cluster, 'latitude')) / count($cluster);
            $centerLon = array_sum(array_column($cluster, 'longitude')) / count($cluster);

            foreach ($existingAreas as $area) {
                $coordinates = is_string($area->coordinates) ? json_decode($area->coordinates, true) : $area->coordinates;
                if ($this->pointInPolygon($centerLat, $centerLon, $coordinates)) {
                    $isInExistingArea = true;
                    break;
                }
            }

            if (!$isInExistingArea && count($cluster) >= 3) { // Reduced threshold since we have fewer data points
                $unmappedClusters[] = [
                    'center_lat' => $centerLat,
                    'center_lon' => $centerLon,
                    'activity_points' => count($cluster),
                    'unique_machines' => count(array_unique(array_column($cluster, 'machine_id'))),
                ];
            }
        }

        // Create recommendations for unmapped clusters
        foreach ($unmappedClusters as $index => $cluster) {
            $recommendations[] = [
                'category' => 'geofence',
                'priority' => $cluster['activity_points'] > 5 ? 'high' : 'medium',
                'title' => 'Potential Unmapped Mining Area Detected',
                'description' => "Detected machine activity at coordinates ({$cluster['center_lat']}, {$cluster['center_lon']}) with {$cluster['unique_machines']} machines. This area may be an unmapped pit or work zone.",
                'confidence_score' => min(0.95, 0.6 + (min($cluster['unique_machines'], 10) / 20)),
                'estimated_savings' => 15000, // Cost of manual surveying
                'data' => [
                    'center_latitude' => round($cluster['center_lat'], 6),
                    'center_longitude' => round($cluster['center_lon'], 6),
                    'activity_points' => $cluster['activity_points'],
                    'unique_machines' => $cluster['unique_machines'],
                    'suggested_radius' => 100, // meters
                ],
                'impact_analysis' => [
                    'benefit' => 'Auto-geofencing improves tracking accuracy',
                    'recommended_action' => 'Create geofence zone',
                    'estimated_area_size' => '~3 hectares',
                ],
            ];
        }

        // Add insight about overall coverage
        if (count($unmappedClusters) > 0) {
            $insights[] = [
                'type' => 'coverage',
                'severity' => 'warning',
                'title' => 'Geofence Coverage Gaps',
                'description' => "Detected {count($unmappedClusters)} areas with significant machine activity outside existing geofences. Consider expanding coverage.",
                'data' => [
                    'unmapped_areas' => count($unmappedClusters),
                    'total_activity_points' => array_sum(array_column($unmappedClusters, 'activity_points')),
                ],
            ];
        } else {
            $insights[] = [
                'type' => 'coverage',
                'severity' => 'success',
                'title' => 'Excellent Geofence Coverage',
                'description' => 'All major machine activity areas are within defined geofences. Coverage is optimal.',
                'data' => [
                    'coverage_percentage' => 100,
                ],
            ];
        }

        return ['recommendations' => $recommendations, 'insights' => $insights];
    }

    /**
     * Analyze existing mine areas for optimization opportunities
     */
    protected function analyzeExistingAreas(Team $team): array
    {
        $recommendations = [];
        $insights = [];

        $areas = MineArea::where('team_id', $team->id)
            ->withCount('machines')
            ->get();

        if ($areas->isEmpty()) {
            $insights[] = [
                'type' => 'setup',
                'severity' => 'warning',
                'title' => 'No Mine Areas Defined',
                'description' => 'Start by defining your mine pit areas and work zones to enable better tracking and analytics.',
                'data' => [
                    'defined_areas' => 0,
                ],
            ];
            return ['recommendations' => $recommendations, 'insights' => $insights];
        }

        // Check for areas with no activity
        foreach ($areas as $area) {
            // Check if any machines are currently assigned to this area
            $currentMachines = Machine::where('team_id', $team->id)
                ->where('mine_area_id', $area->id)
                ->whereNotNull('last_location_latitude')
                ->whereNotNull('last_location_longitude')
                ->where('last_location_update', '>=', Carbon::now()->subDays(7))
                ->count();

            if ($currentMachines === 0 && $area->machines_count === 0) {
                $recommendations[] = [
                    'category' => 'geofence',
                    'priority' => 'low',
                    'title' => "Inactive Area: {$area->name}",
                    'description' => "Mine area '{$area->name}' has no assigned machines and no recent activity. Consider archiving or repurposing.",
                    'confidence_score' => 0.88,
                    'related_mine_area_id' => $area->id,
                    'data' => [
                        'area_type' => $area->type,
                        'days_inactive' => 7,
                        'assigned_machines' => 0,
                    ],
                    'impact_analysis' => [
                        'recommended_action' => 'Archive or reassign',
                        'benefit' => 'Cleaner dashboard and reports',
                    ],
                ];
            }
        }

        // Analyze area density
        $totalMachines = $areas->sum('machines_count');
        $avgMachinesPerArea = $totalMachines / max($areas->count(), 1);

        $insights[] = [
            'type' => 'utilization',
            'severity' => 'info',
            'title' => 'Area Utilization Overview',
            'description' => "You have {$areas->count()} defined mine areas with an average of " . number_format($avgMachinesPerArea, 1) . " machines per area.",
            'data' => [
                'total_areas' => $areas->count(),
                'total_machines' => $totalMachines,
                'avg_machines_per_area' => round($avgMachinesPerArea, 2),
                'areas_by_type' => $areas->groupBy('type')->map->count()->toArray(),
            ],
        ];

        return ['recommendations' => $recommendations, 'insights' => $insights];
    }

    /**
     * Analyze area boundaries for potential issues
     */
    protected function analyzeBoundaries(Team $team): array
    {
        $recommendations = [];

        $areas = MineArea::where('team_id', $team->id)->get();

        // Check for overlapping boundaries
        for ($i = 0; $i < $areas->count(); $i++) {
            for ($j = $i + 1; $j < $areas->count(); $j++) {
                $area1 = $areas[$i];
                $area2 = $areas[$j];

                if ($this->areasOverlap($area1, $area2)) {
                    $recommendations[] = [
                        'category' => 'geofence',
                        'priority' => 'medium',
                        'title' => "Overlapping Boundaries: {$area1->name} & {$area2->name}",
                        'description' => "Areas '{$area1->name}' and '{$area2->name}' have overlapping boundaries, which may cause tracking ambiguity.",
                        'confidence_score' => 0.95,
                        'data' => [
                            'area_1_id' => $area1->id,
                            'area_2_id' => $area2->id,
                            'area_1_name' => $area1->name,
                            'area_2_name' => $area2->name,
                        ],
                        'impact_analysis' => [
                            'issue' => 'Machines may be counted in multiple areas',
                            'recommended_action' => 'Adjust boundaries to eliminate overlap',
                        ],
                    ];
                }
            }
        }

        return ['recommendations' => $recommendations];
    }

    /**
     * Group locations by proximity using simple distance clustering
     */
    protected function groupLocationsByProximity(array $locations, float $threshold): array
    {
        $clusters = [];
        $processed = [];

        foreach ($locations as $i => $location) {
            if (in_array($i, $processed)) {
                continue;
            }

            $cluster = [$location];
            $processed[] = $i;

            foreach ($locations as $j => $otherLocation) {
                if ($i === $j || in_array($j, $processed)) {
                    continue;
                }

                $distance = $this->calculateDistance(
                    $location['latitude'],
                    $location['longitude'],
                    $otherLocation['latitude'],
                    $otherLocation['longitude']
                );

                if ($distance < $threshold) {
                    $cluster[] = $otherLocation;
                    $processed[] = $j;
                }
            }

            $clusters[] = $cluster;
        }

        return $clusters;
    }

    /**
     * Calculate distance between two coordinates (simple approximation)
     */
    protected function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        return sqrt(pow($lat2 - $lat1, 2) + pow($lon2 - $lon1, 2));
    }

    /**
     * Check if a point is inside a polygon using ray casting algorithm
     */
    protected function pointInPolygon(float $lat, float $lon, array $polygon): bool
    {
        if (empty($polygon)) {
            return false;
        }

        $inside = false;
        $count = count($polygon);

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $xi = $polygon[$i][0] ?? $polygon[$i]['lat'] ?? 0;
            $yi = $polygon[$i][1] ?? $polygon[$i]['lng'] ?? 0;
            $xj = $polygon[$j][0] ?? $polygon[$j]['lat'] ?? 0;
            $yj = $polygon[$j][1] ?? $polygon[$j]['lng'] ?? 0;

            $intersect = (($yi > $lon) != ($yj > $lon))
                && ($lat < ($xj - $xi) * ($lon - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    /**
     * Check if two areas have overlapping boundaries
     */
    protected function areasOverlap(MineArea $area1, MineArea $area2): bool
    {
        $coords1 = is_string($area1->coordinates) ? json_decode($area1->coordinates, true) : $area1->coordinates;
        $coords2 = is_string($area2->coordinates) ? json_decode($area2->coordinates, true) : $area2->coordinates;

        if (empty($coords1) || empty($coords2)) {
            return false;
        }

        // Simple check: see if any vertex of area2 is inside area1
        foreach ($coords2 as $point) {
            $lat = $point[0] ?? $point['lat'] ?? null;
            $lon = $point[1] ?? $point['lng'] ?? null;

            if ($lat && $lon && $this->pointInPolygon($lat, $lon, $coords1)) {
                return true;
            }
        }

        return false;
    }
}
