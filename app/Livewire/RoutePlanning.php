<?php

namespace App\Livewire;

use App\Models\Geofence;
use App\Models\Machine;
use App\Models\MineArea;
use App\Models\Route;
use App\Models\Waypoint;
use App\Services\RoutePlanningService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RoutePlanning extends Component
{
    public $name = '';
    public $description = '';
    public $machineId = null;
    public $mineAreaId = null;
    public $routeType = 'optimal';
    
    // Route coordinates
    public $startLat = null;
    public $startLon = null;
    public $endLat = null;
    public $endLon = null;
    
    // Calculated route data
    public $calculatedRoute = null;
    public $savedRoute = null;
    
    // UI State
    public $showCalculatedRoute = false;
    public $isCalculating = false;
    public $routeSaved = false;
    public $isLoading = false;
    
    // Map settings
    public $centerLat = -26.2041;
    public $centerLng = 28.0473;
    public $zoomLevel = 10;
    
    // View mode
    public $viewMode = 'create'; // create, view
    public $routes = [];
    public $selectedRouteId = null;
    
    protected $rules = [
        'name' => 'required|min:3|max:255',
        'machineId' => 'nullable|exists:machines,id',
        'mineAreaId' => 'nullable|exists:mine_areas,id',
        'startLat' => 'required|numeric|between:-90,90',
        'startLon' => 'required|numeric|between:-180,180',
        'endLat' => 'required|numeric|between:-90,90',
        'endLon' => 'required|numeric|between:-180,180',
        'routeType' => 'required|in:optimal,shortest,safest,custom',
    ];
    
    public function mount()
    {
        $this->isLoading = true;
        $this->loadRoutes();
        $this->isLoading = false;
    }
    
    public function render()
    {
        $team = Auth::user()->currentTeam;
        
        $machines = Machine::where('team_id', $team->id)
            ->orderBy('name')
            ->get();
        
        $mineAreas = MineArea::where('team_id', $team->id)
            ->orderBy('name')
            ->get();
        
        // Convert geofences to plain array for safe JavaScript serialization
        $geofences = Geofence::where('team_id', $team->id)
            ->get()
            ->map(function ($geofence) {
                // Ensure coordinates are in the right format
                $coordinates = $geofence->coordinates;
                // If coordinates is a string, parse it; otherwise use as-is
                if (is_string($coordinates)) {
                    $coordinates = json_decode($coordinates, true);
                }
                
                return [
                    'id' => $geofence->id,
                    'name' => $geofence->name,
                    'geofence_type' => $geofence->geofence_type,
                    'coordinates' => $coordinates, // Already an array thanks to cast
                ];
            })
            ->toArray();
        
        return view('livewire.route-planning', [
            'machines' => $machines,
            'mineAreas' => $mineAreas,
            'geofences' => $geofences,
        ]);
    }
    
    public function calculateRoute()
    {
        $this->validate();
        
        $this->isCalculating = true;
        $this->routeSaved = false;
        
        try {
            $team = Auth::user()->currentTeam;
            $service = new RoutePlanningService();
            
            $this->calculatedRoute = $service->calculateOptimalRoute(
                $this->startLat,
                $this->startLon,
                $this->endLat,
                $this->endLon,
                $this->machineId,
                $team->id
            );
            
            $this->showCalculatedRoute = true;
            $this->dispatch('routeCalculated', $this->calculatedRoute);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to calculate route: ' . $e->getMessage());
        } finally {
            $this->isCalculating = false;
        }
    }

    public function saveRoute()
    {
        if (!$this->calculatedRoute) {
            session()->flash('error', 'Please calculate a route first.');
            return;
        }
        
        $this->validate(['name' => 'required|min:3|max:255']);
        
        try {
            $team = Auth::user()->currentTeam;
            
            DB::beginTransaction();
            
            // Create the route
            $route = Route::create([
                'team_id' => $team->id,
                'machine_id' => $this->machineId,
                'mine_area_id' => $this->mineAreaId,
                'name' => $this->name,
                'description' => $this->description,
                'start_latitude' => $this->calculatedRoute['start_latitude'],
                'start_longitude' => $this->calculatedRoute['start_longitude'],
                'end_latitude' => $this->calculatedRoute['end_latitude'],
                'end_longitude' => $this->calculatedRoute['end_longitude'],
                'total_distance' => $this->calculatedRoute['total_distance'],
                'estimated_time' => $this->calculatedRoute['estimated_time'],
                'estimated_fuel' => $this->calculatedRoute['estimated_fuel'],
                'route_type' => $this->routeType,
                'status' => 'active',
                'route_geometry' => $this->calculatedRoute['route_geometry'] ?? null,
            ]);
            
            // Create waypoints
            foreach ($this->calculatedRoute['waypoints'] as $waypointData) {
                Waypoint::create([
                    'route_id' => $route->id,
                    'sequence_order' => $waypointData['sequence_order'],
                    'latitude' => $waypointData['latitude'],
                    'longitude' => $waypointData['longitude'],
                    'waypoint_type' => $waypointData['waypoint_type'] ?? 'standard',
                    'name' => $waypointData['name'] ?? null,
                    'distance_from_previous' => $waypointData['distance_from_previous'] ?? null,
                    'estimated_time_from_previous' => $waypointData['estimated_time_from_previous'] ?? null,
                ]);
            }
            
            DB::commit();
            
            $this->savedRoute = $route;
            $this->routeSaved = true;
            $this->loadRoutes();
            
            session()->flash('success', 'Route saved successfully!');
            
            // Reset form
            $this->reset(['name', 'description', 'calculatedRoute', 'showCalculatedRoute']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to save route: ' . $e->getMessage());
        }
    }
    
    public function loadRoutes()
    {
        $team = Auth::user()->currentTeam;
        $this->routes = Route::where('team_id', $team->id)
            ->with(['machine', 'mineArea', 'waypoints'])
            ->latest()
            ->get()
            ->toArray();
    }
    
    public function viewRoute($routeId)
    {
        $team = Auth::user()->currentTeam;
        $route = Route::where('team_id', $team->id)
            ->where('id', $routeId)
            ->with('waypoints')
            ->first();
        
        if ($route) {
            $this->selectedRouteId = $routeId;
            $this->viewMode = 'view';
            
            // Prepare route data for map
            $routeData = [
                'id' => $route->id,
                'name' => $route->name,
                'start_latitude' => $route->start_latitude,
                'start_longitude' => $route->start_longitude,
                'end_latitude' => $route->end_latitude,
                'end_longitude' => $route->end_longitude,
                'total_distance' => $route->total_distance,
                'estimated_time' => $route->estimated_time,
                'estimated_fuel' => $route->estimated_fuel,
                'route_geometry' => $route->route_geometry,
                'waypoints' => $route->waypoints->map(fn($w) => [
                    'latitude' => $w->latitude,
                    'longitude' => $w->longitude,
                    'name' => $w->name,
                    'type' => $w->waypoint_type,
                    'distance_from_previous' => $w->distance_from_previous,
                    'estimated_time_from_previous' => $w->estimated_time_from_previous,
                ])->toArray(),
            ];
            
            $this->dispatch('viewRoute', $routeData);
        }
    }
    
    public function deleteRoute($routeId)
    {
        $team = Auth::user()->currentTeam;
        $route = Route::where('team_id', $team->id)
            ->where('id', $routeId)
            ->first();
        
        if ($route) {
            $route->delete();
            
            // Reset component state properly after delete
            $this->reset([
                'selectedRouteId',
                'calculatedRoute', 
                'showCalculatedRoute', 
                'savedRoute', 
                'routeSaved',
                'startLat',
                'startLon',
                'endLat',
                'endLon'
            ]);
            
            $this->loadRoutes();
            
            // Clear map markers via JavaScript
            $this->dispatch('clearMapMarkers');
            
            session()->flash('success', 'Route deleted successfully.');
        }
    }
    
    public function switchToCreateMode()
    {
        $this->viewMode = 'create';
        
        // Reset all form and state variables
        $this->reset([
            'selectedRouteId',
            'calculatedRoute', 
            'showCalculatedRoute', 
            'savedRoute', 
            'routeSaved',
            'startLat',
            'startLon',
            'endLat',
            'endLon',
            'name',
            'description',
            'machineId',
            'mineAreaId',
            'routeType'
        ]);
        
        // Set default route type
        $this->routeType = 'optimal';
        
        // Clear map markers
        $this->dispatch('clearMapMarkers');
    }
    
    public function updateStartPoint($lat, $lon)
    {
        $this->startLat = $lat;
        $this->startLon = $lon;
    }
    
    public function updateEndPoint($lat, $lon)
    {
        $this->endLat = $lat;
        $this->endLon = $lon;
    }
    
    public function clearPoints()
    {
        $this->startLat = null;
        $this->startLon = null;
        $this->endLat = null;
        $this->endLon = null;
        $this->calculatedRoute = null;
        $this->showCalculatedRoute = false;
        $this->dispatch('clearMapMarkers');
    }
}
