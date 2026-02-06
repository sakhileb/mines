<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MineArea;
use App\Models\Geofence;
use App\Models\Route;
use App\Services\MineAreaService;
use Illuminate\Support\Facades\DB;

class MineAreasDashboard extends Component
{



    public function loadMinePlans()
    {
        $this->uploadedMinePlans = \App\Models\MinePlan::with('mineArea', 'uploader')
            ->whereHas('mineArea', fn($q) => $q->where('team_id', $this->team->id))
            ->latest()
            ->get();
    }
    public $minePlanFile;
    public $minePlanDescription = '';
    public $minePlanAreaId = null;
    public $uploadedMinePlans = [];
    public $team;
    public $selectedPeriod = '7days';
    public $filterType = null;
    public $filterStatus = null;
    
    // Route Selection Properties
    public $selectedGeofenceId = null;
    public $recommendedRoute = null;
    public $availableRoutes = [];

    protected $mineAreaService;

    public function mount()
    {
        $this->team = auth()->user()->currentTeam;
        $this->mineAreaService = app(MineAreaService::class);
        $this->loadMinePlans();
    }

    public function uploadMinePlan()
    {
        $this->validate([
            'minePlanFile' => 'required|file|mimes:pdf,png,jpg,jpeg,dwg|max:10240',
            'minePlanDescription' => 'nullable|string|max:500',
            'minePlanAreaId' => 'nullable|exists:mine_areas,id',
        ]);

        try {
            $path = $this->minePlanFile->store('mine-plans', 'public');
            \App\Models\MinePlan::create([
                'mine_area_id' => $this->minePlanAreaId,
                'file_path' => $path,
                'description' => $this->minePlanDescription,
                'uploaded_by' => auth()->id(),
            ]);
            session()->flash('minePlanSuccess', 'Mine plan uploaded successfully!');
            $this->reset(['minePlanFile', 'minePlanDescription', 'minePlanAreaId']);
            $this->loadMinePlans();
        } catch (\Exception $e) {
            session()->flash('minePlanError', 'Failed to upload mine plan: ' . $e->getMessage());
        }
    }

    public function deleteMinePlan($id)
    {
        $plan = \App\Models\MinePlan::findOrFail($id);
        if (auth()->user()->is_admin || $plan->uploaded_by === auth()->id()) {
            $plan->delete();
            session()->flash('minePlanSuccess', 'Mine plan deleted.');
            $this->loadMinePlans();
        } else {
            session()->flash('minePlanError', 'You do not have permission to delete this file.');
        }
    }

    public function getStatistics()
    {
        $query = $this->team->mineAreas();

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        $areas = $query->get();

        return [
            'total_areas' => $areas->count(),
            'active_areas' => $areas->where('status', 'active')->count(),
            'total_area_sqm' => $areas->sum('area_sqm') ?? 0,
            'total_machines' => $areas->sum(fn($a) => $a->machines->count()),
            'active_machines' => $areas->sum(fn($a) => $a->machines->where('status', 'online')->count()),
            'total_production' => $this->getTotalProduction($areas),
            'average_production' => $this->getAverageProduction($areas),
        ];
    }

    public function getProductionTrend()
    {
        $days = match($this->selectedPeriod) {
            '7days' => 7,
            '30days' => 30,
            '90days' => 90,
            default => 7,
        };

        $query = DB::table('mine_area_production')
            ->join('mine_areas', 'mine_area_production.mine_area_id', '=', 'mine_areas.id')
            ->select(DB::raw('DATE(mine_area_production.recorded_date) as date'), DB::raw('SUM(mine_area_production.tonnage) as total'))
            ->where('mine_areas.team_id', $this->team->id)
            ->where('mine_area_production.recorded_date', '>=', now()->subDays($days));

        if ($this->filterType) {
            $query->where('mine_areas.type', $this->filterType);
        }

        $query->groupBy('date')->orderBy('date');

        return $query->get()->map(fn($r) => [
            'date' => $r->date,
            'total' => $r->total ?? 0,
        ])->toArray();
    }

    public function getTopAreas()
    {
        $query = $this->team->mineAreas()
            ->withCount('machines')
            ->with(['production' => fn($q) => $q->select(DB::raw('SUM(material_tonnage) as total'))->groupBy('mine_area_id')])
            ->orderBy('area_sqm', 'desc');

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        return $query->limit(5)->get()->map(fn($area) => [
            'name' => $area->name,
            'area_sqm' => $area->area_sqm ?? 0,
            'machines_count' => $area->machines_count,
            'production' => $area->production->sum('total') ?? 0,
        ])->toArray();
    }

    public function getMachineDistribution()
    {
        $areas = $this->team->mineAreas()
            ->withCount('machines')
            ->get()
            ->sortByDesc('machines_count')
            ->take(10);

        return $areas->map(fn($area) => [
            'name' => $area->name,
            'count' => $area->machines_count,
        ])->toArray();
    }

    private function getTotalProduction($areas)
    {
        return $areas->sum(fn($area) => $area->production->sum('material_tonnage')) ?? 0;
    }

    private function getAverageProduction($areas)
    {
        if ($areas->isEmpty()) return 0;
        
        $total = $this->getTotalProduction($areas);
        $count = $areas->sum(fn($a) => $a->production->count());
        
        return $count > 0 ? $total / $count : 0;
    }

    /**
     * Handle geofence selection and find optimal route
     */
    public function updatedSelectedGeofenceId($geofenceId)
    {
        if (!$geofenceId) {
            $this->recommendedRoute = null;
            $this->availableRoutes = [];
            return;
        }

        $geofence = Geofence::find($geofenceId);
        if (!$geofence) {
            return;
        }

        // Find all active routes that lead to this geofence
        $this->availableRoutes = $this->findRoutesToGeofence($geofence);

        // Select the best route based on safety and optimization
        $this->recommendedRoute = $this->selectOptimalRoute($this->availableRoutes);
    }

    /**
     * Find all routes that lead to a specific geofence
     */
    private function findRoutesToGeofence($geofence)
    {
        $routes = Route::where('team_id', $this->team->id)
            ->where('status', 'active')
            ->with(['machine', 'mineArea'])
            ->get();

        // Filter routes whose end point is within or near the geofence
        $routesToGeofence = $routes->filter(function($route) use ($geofence) {
            return $this->isPointNearGeofence(
                $route->end_latitude,
                $route->end_longitude,
                $geofence
            );
        });

        return $routesToGeofence->map(function($route) {
            return [
                'id' => $route->id,
                'name' => $route->name,
                'type' => $route->route_type,
                'distance' => $route->total_distance,
                'estimated_time' => $route->estimated_time,
                'estimated_fuel' => $route->estimated_fuel,
                'machine' => $route->machine ? $route->machine->name : 'N/A',
                'score' => $this->calculateRouteScore($route),
            ];
        })->sortByDesc('score')->values()->toArray();
    }

    /**
     * Check if a point is within or near a geofence
     */
    private function isPointNearGeofence($lat, $lon, $geofence)
    {
        // Calculate distance from point to geofence center
        $distance = $this->calculateDistance(
            $lat,
            $lon,
            $geofence->center_latitude,
            $geofence->center_longitude
        );

        // Consider route leads to geofence if within 500m of center
        // Adjust threshold based on geofence size
        $threshold = 0.5; // 500 meters
        if ($geofence->area_sqm > 100000) { // If area > 100,000 sqm
            $threshold = 1.0; // 1 km threshold for larger areas
        }

        return $distance <= $threshold;
    }

    /**
     * Calculate distance between two points in kilometers
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Calculate route score based on safety and optimization
     */
    private function calculateRouteScore($route)
    {
        $score = 0;

        // Route type scoring (higher is better)
        $typeScores = [
            'optimal' => 50,
            'safest' => 45,
            'shortest' => 30,
            'custom' => 20,
        ];
        $score += $typeScores[$route->route_type] ?? 0;

        // Fuel efficiency scoring (lower fuel is better)
        if ($route->estimated_fuel > 0) {
            $fuelScore = max(0, 30 - ($route->estimated_fuel / 10));
            $score += $fuelScore;
        }

        // Time efficiency scoring (lower time is better)
        if ($route->estimated_time > 0) {
            $timeScore = max(0, 20 - ($route->estimated_time / 10));
            $score += $timeScore;
        }

        return round($score, 2);
    }

    /**
     * Select the optimal route from available options
     */
    private function selectOptimalRoute($routes)
    {
        if (empty($routes)) {
            return null;
        }

        // Routes are already sorted by score in descending order
        // Return the highest scoring route
        return $routes[0] ?? null;
    }

    public function render()
    {
        $geofences = Geofence::where('team_id', $this->team->id)
            ->where('status', 'active')
            ->with('mineArea')
            ->orderBy('name')
            ->get();

        return view('livewire.mine-areas-dashboard', [
            'statistics' => $this->getStatistics(),
            'productionTrend' => $this->getProductionTrend(),
            'topAreas' => $this->getTopAreas(),
            'machineDistribution' => $this->getMachineDistribution(),
            'geofences' => $geofences,
        ]);
    }
}
