<?php

namespace App\Livewire;

use App\Models\Machine;
use App\Models\Geofence;
use App\Models\Route;
use App\Traits\RealtimeUpdates;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class LiveMap extends Component
{
    use RealtimeUpdates;

    public float $centerLat = -28.4793; // South Africa center latitude
    public array $activityFeed = [];
    public bool $isLoading = true;
    public float $centerLng = 24.6727; // South Africa center longitude
    public int $zoomLevel = 12;
    public string $mapStyle = 'satellite'; // 'osm' or 'satellite'
    public bool $showGeofences = true;
    public bool $showMachines = true;
    public bool $showRoutes = false;
    public string $selectedStatus = '';
    public ?int $selectedMineAreaId = null;

    public function mount(): void
    {
        // Try to center on first machine location if available, else default to South Africa
        $team = Auth::user()->currentTeam;
        $firstMachine = Machine::where('team_id', $team->id)
            ->whereNotNull('last_location_latitude')
            ->whereNotNull('last_location_longitude')
            ->first();

        if ($firstMachine) {
            $this->centerLat = (float) $firstMachine->last_location_latitude;
            $this->centerLng = (float) $firstMachine->last_location_longitude;
            $this->zoomLevel = 13;
        } else {
            // South Africa center
            $this->centerLat = -28.4793;
            $this->centerLng = 24.6727;
            $this->zoomLevel = 6;
        }

        $this->loadActivityFeed();

        // Initialize real-time updates
        $this->initializeRealtimeUpdates();
        $this->subscribeToTeamLocations();
    }

    public function loadActivityFeed()
    {
        $team = Auth::user()->currentTeam;
        $this->activityFeed = \App\Models\ActivityLog::where('team_id', $team->id)
            ->latest('created_at')
            ->take(10)
            ->get()
            ->map(fn ($log) => [
                'user' => $log->user->name ?? 'System',
                'action' => $log->action,
                'description' => $log->description,
                'created_at' => $log->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    public function toggleGeofences(): void
    {
        $this->showGeofences = !$this->showGeofences;
        $this->dispatch('map-updated', [
            'mapStyle'  => $this->mapStyle,
            'geofences' => $this->showGeofences ? $this->getGeofences() : [],
            'machines'  => $this->showMachines ? $this->getMachines() : [],
            'routes'    => $this->showRoutes ? $this->getRoutes() : [],
        ]);
    }

    public function toggleMachines(): void
    {
        $this->showMachines = !$this->showMachines;
        $this->dispatch('map-updated', [
            'mapStyle'  => $this->mapStyle,
            'machines'  => $this->showMachines ? $this->getMachines() : [],
            'geofences' => $this->showGeofences ? $this->getGeofences() : [],
            'routes'    => $this->showRoutes ? $this->getRoutes() : [],
        ]);
    }

    public function toggleRoutes(): void
    {
        $this->showRoutes = !$this->showRoutes;
        $this->dispatch('map-updated', [
            'mapStyle'  => $this->mapStyle,
            'machines'  => $this->showMachines ? $this->getMachines() : [],
            'geofences' => $this->showGeofences ? $this->getGeofences() : [],
            'routes'    => $this->showRoutes ? $this->getRoutes() : [],
        ]);
    }

    public function changeMapStyle(string $style): void
    {
        $this->mapStyle = $style;
        $this->dispatch('map-updated', [
            'mapStyle'  => $style,
            'machines'  => $this->showMachines ? $this->getMachines() : [],
            'geofences' => $this->showGeofences ? $this->getGeofences() : [],
            'routes'    => $this->showRoutes ? $this->getRoutes() : [],
        ]);
    }

    public function getMachines()
    {
        $team = Auth::user()->currentTeam;

        $machinesQuery = Machine::where('team_id', $team->id)
            ->whereNotNull('last_location_latitude')
            ->whereNotNull('last_location_longitude');

        if ($this->selectedStatus) {
            $machinesQuery->where('status', $this->selectedStatus);
        }

        if ($this->selectedMineAreaId) {
            $machinesQuery->where('mine_area_id', $this->selectedMineAreaId);
        }

        return $machinesQuery->get();
    }

    public function getMineAreas()
    {
        $team = Auth::user()->currentTeam;
        // Return active mine areas with coordinates decoded for client-side use
        return \App\Models\MineArea::forTeam($team->id)
            ->byStatus('active')
            ->orderBy('name')
            ->get()
            ->map(function ($area) {
                return [
                    'id' => $area->id,
                    'name' => $area->name,
                    'coordinates' => is_string($area->coordinates) ? json_decode($area->coordinates, true) : $area->coordinates ?? [],
                ];
            })
            ->toArray();
    }

    public function updatedSelectedMineAreaId($value): void
    {
        // When user selects a mine area, push an update to the map with filtered machines
        $this->dispatch('map-updated', [
            'mapStyle'           => $this->mapStyle,
            'machines'           => $this->getMachines(),
            'geofences'          => $this->showGeofences ? $this->getGeofences() : [],
            'routes'             => $this->showRoutes ? $this->getRoutes() : [],
            'selectedMineAreaId' => $value,
        ]);
    }

    public function getGeofences()
    {
        $team = Auth::user()->currentTeam;

        return Geofence::where('team_id', $team->id)
            ->get()
            ->map(function ($geofence) {
                return [
                    'id'               => $geofence->id,
                    'name'             => $geofence->name,
                    'center_latitude'  => (float) $geofence->center_latitude,
                    'center_longitude' => (float) $geofence->center_longitude,
                    'coordinates'      => is_string($geofence->coordinates)
                        ? json_decode($geofence->coordinates, true)
                        : $geofence->coordinates ?? [],
                ];
            });
    }

    public function getRoutes(): array
    {
        $team = Auth::user()->currentTeam;

        return Route::where('team_id', $team->id)
            ->where('status', 'active')
            ->with(['waypoints' => fn($q) => $q->orderBy('sequence_order')])
            ->get()
            ->map(fn($route) => [
                'id'              => $route->id,
                'name'            => $route->name,
                'start_latitude'  => (float) $route->start_latitude,
                'start_longitude' => (float) $route->start_longitude,
                'end_latitude'    => (float) $route->end_latitude,
                'end_longitude'   => (float) $route->end_longitude,
                'total_distance'  => (float) $route->total_distance,
                'estimated_time'  => (int) $route->estimated_time,
                'route_geometry'  => $route->route_geometry,
                'waypoints'       => $route->waypoints->map(fn($w) => [
                    'sequence_order'               => $w->sequence_order,
                    'latitude'                     => (float) $w->latitude,
                    'longitude'                    => (float) $w->longitude,
                    'waypoint_type'                => $w->waypoint_type,
                    'name'                         => $w->name,
                    'distance_from_previous'       => $w->distance_from_previous,
                    'estimated_time_from_previous' => $w->estimated_time_from_previous,
                ])->toArray(),
            ])
            ->toArray();
    }

    public function getTrafficPlanData(): array
    {
        $teamId = Auth::user()->currentTeam->id;

        $restrictedZones = Geofence::where('team_id', $teamId)
            ->where('geofence_type', 'restricted')
            ->count();

        $safeZones = Geofence::where('team_id', $teamId)
            ->where('geofence_type', 'safe')
            ->count();

        $warningZones = Geofence::where('team_id', $teamId)
            ->whereNotIn('geofence_type', ['restricted', 'safe'])
            ->count();

        $activeRoutesQuery = Route::where('team_id', $teamId)->where('status', 'active');
        $activeRoutes = $activeRoutesQuery->count();

        $routesWithSpeedLimit = (clone $activeRoutesQuery)
            ->whereNotNull('speed_limit')
            ->count();

        return [
            'restricted_zones' => $restrictedZones,
            'safe_zones' => $safeZones,
            'warning_zones' => $warningZones,
            'active_routes' => $activeRoutes,
            'routes_with_speed_limit' => $routesWithSpeedLimit,
            'default_speed_limits' => [
                'haul_road' => 40,
                'loading_zone' => 20,
                'shared_zone' => 15,
            ],
            'rules' => [
                'avoid_restricted' => true,
                'one_way_flow' => true,
                'pedestrian_priority_shared_zones' => true,
            ],
        ];
    }

    public function render()
    {
        $machines = $this->getMachines();
        $geofences = $this->getGeofences();
        $routes = $this->showRoutes ? $this->getRoutes() : [];
        $machineStatuses = Machine::where('team_id', Auth::user()->currentTeam->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('livewire.live-map', [
            'machines'        => $machines,
            'geofences'       => $geofences,
            'routes'          => $routes,
            'showRoutes'      => $this->showRoutes,
            'trafficPlanData' => $this->getTrafficPlanData(),
            'machineStatuses' => $machineStatuses,
            'mineAreas'       => $this->getMineAreas(),
        ]);
    }
}
