<?php

namespace App\Livewire;

use App\Models\MineArea;
use App\Models\Team;
use App\Services\MineAreaService;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MineAreaManager extends Component
{
    public function hydrate()
    {
        $user = auth()->user();
        if (!$this->team || !$this->team instanceof Team || !$this->team->exists) {
            $this->team = $user && $user->currentTeam ? $user->currentTeam : null;
        }
    }

public ?Team $team = null;

    // View modes
    public string $view = 'list'; // list, create, edit, detail

    // Mine area data
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:1000')]
    public ?string $description = '';

    #[Validate('required|in:pit,stockpile,dump,processing,facility')]
    public string $type = 'pit';

    #[Validate('required|array|size:4')]
    public array $coordinates = [];

    #[Validate('nullable|string|max:500')]
    public ?string $notes = '';

    // New features
    public array $shifts = [];
    public array $material_types = [];
    public array $mining_targets = [
        'daily' => 0,
        'weekly' => 0,
        'monthly' => 0,
        'yearly' => 0,
        'unit' => 'tonnes'
    ];

    // Shift modal state
    public bool $showShiftModal = false;
    public ?int $editingShiftIndex = null;
    public string $shiftName = '';
    public string $shiftStartTime = '';
    public string $shiftEndTime = '';
    public array $shiftDays = [];

    // Material types modal
    public bool $showMaterialModal = false;
    public string $newMaterialType = '';

    // Targets modal
    public bool $showTargetsModal = false;

    // Edit mode
    public ?MineArea $currentMineArea = null;

    // UI state
    public bool $showMap = true;
    public string $mapCenter = '-33.8688,151.2093';
    public int $mapZoom = 13;
    public array $editingCoordinates = [];
    public bool $isDrawing = false;

    // Filtering
    public string $filterType = '';
    public string $filterStatus = 'active';
    public string $searchTerm = '';

    // Temporary coordinates for manual input
    public ?string $tempLat = null;
    public ?string $tempLon = null;

    public function mount(?Team $team = null)
    {
        $this->team = $team ?? auth()->user()->currentTeam;
        // Check if user can view any mine areas (using the policy)
        $this->authorize('viewAny', MineArea::class);
    }

    public function render()
    {
        if (!$this->team || !$this->team instanceof Team || !$this->team->exists) {
            // Optionally, redirect or show an error view
            return view('livewire.mine-area-manager.index', [
                'mineAreas' => collect(),
                'totalAreas' => 0,
                'activeAreas' => 0,
            ]);
        }

        $mineAreas = $this->getFilteredMineAreas();

        return view('livewire.mine-area-manager.index', [
            'mineAreas' => $mineAreas,
            'totalAreas' => $this->team->mineAreas()->count(),
            'activeAreas' => $this->team->mineAreas()->where('status', 'active')->count(),
        ]);
    }

    /**
     * Switch to create view.
     */
    public function startCreate()
    {
        $this->reset(['name', 'description', 'type', 'coordinates', 'notes', 'currentMineArea']);
        $this->view = 'create';
        $this->showMap = true;
    }

    /**
     * Switch to edit view.
     */
    public function startEdit(MineArea $mineArea)
    {
        $model = MineArea::find($mineArea->id);
        $this->currentMineArea = $model;
        $this->name = $model->name;
        $this->description = $model->description;
        $this->type = $model->type;
        $this->coordinates = $model->coordinates;
        $this->notes = $model->notes;
        $this->shifts = $model->shifts ?? [];
        $this->material_types = $model->material_types ?? [];
        $this->mining_targets = $model->mining_targets ?? [
            'daily' => 0,
            'weekly' => 0,
            'monthly' => 0,
            'yearly' => 0,
            'unit' => 'tonnes'
        ];
        $this->view = 'edit';
        $this->showMap = true;
    }

    /**
     * View mine area details.
     */
    public function viewDetails($mineAreaId)
    {
        $model = MineArea::find($mineAreaId);
        if (!$model) {
            $this->dispatch('notify', type: 'error', message: 'Mine area not found');
            $this->view = 'list';
            return;
        }
        
        // Authorization: Ensure user can view this mine area
        $this->authorize('view', $model);
        
        $this->currentMineArea = $model;
        $this->view = 'detail';
    }

    /**
     * Back to list view.
     */
    public function backToList()
    {
        $this->view = 'list';
        $this->reset(['currentMineArea', 'name', 'description', 'type', 'coordinates', 'notes']);
    }

    /**
     * Create new mine area.
     */
    public function create()
    {
        // Authorization: Ensure user can create mine areas
        $this->authorize('create', MineArea::class);
        
        // Custom validation with better messages
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:pit,stockpile,dump,processing,facility',
            'coordinates' => 'required|array|min:4',
            'notes' => 'nullable|string|max:500',
        ], [
            'coordinates.min' => 'At least 4 coordinate points are required to define a mine area boundary.',
        ]);

        try {
            $mineArea = resolve(MineAreaService::class)->create($this->team, [
                'name' => $this->name,
                'description' => $this->description,
                'type' => $this->type,
                'coordinates' => $this->coordinates,
                'notes' => $this->notes,
                'shifts' => $this->shifts,
                'material_types' => $this->material_types,
                'mining_targets' => $this->mining_targets,
            ]);

            Log::info('User created mine area', [
                'user_id' => Auth::id(),
                'mine_area_id' => $mineArea->id,
            ]);

            $this->dispatch('notify', 
                type: 'success', 
                message: "Mine area '{$mineArea->name}' created successfully!"
            );

            $this->backToList();

        } catch (\Exception $e) {
            Log::error('Failed to create mine area', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            $this->dispatch('notify', 
                type: 'error', 
                message: 'Failed to create mine area: ' . $e->getMessage()
            );
        }
    }

    /**
     * Update existing mine area.
     */
    public function update()
    {
        if (!$this->currentMineArea) {
            $this->dispatch('notify', type: 'error', message: 'No mine area selected');
            return;
        }
        
        // Authorization: Ensure user can update this mine area
        $this->authorize('update', $this->currentMineArea);

        // Custom validation with better messages
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:pit,stockpile,dump,processing,facility',
            'coordinates' => 'required|array|min:4',
            'notes' => 'nullable|string|max:500',
        ], [
            'coordinates.min' => 'At least 4 coordinate points are required to define a mine area boundary.',
        ]);

        try {
            resolve(MineAreaService::class)->update($this->currentMineArea, [
                'name' => $this->name,
                'description' => $this->description,
                'type' => $this->type,
                'coordinates' => $this->coordinates,
                'notes' => $this->notes,
                'shifts' => $this->shifts,
                'material_types' => $this->material_types,
                'mining_targets' => $this->mining_targets,
            ]);

            Log::info('User updated mine area', [
                'user_id' => Auth::id(),
                'mine_area_id' => $this->currentMineArea->id,
            ]);

            $this->dispatch('notify', 
                type: 'success', 
                message: 'Mine area updated successfully!'
            );

            $this->backToList();

        } catch (\Exception $e) {
            Log::error('Failed to update mine area', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            $this->dispatch('notify', 
                type: 'error', 
                message: 'Failed to update mine area'
            );
        }
    }

    /**
     * Delete mine area.
     */
    public function delete(MineArea $mineArea)
    {
        // Authorization: Ensure user can delete this mine area
        $this->authorize('delete', $mineArea);
        
        try {
            resolve(MineAreaService::class)->delete($mineArea);

            Log::info('User deleted mine area', [
                'user_id' => Auth::id(),
                'mine_area_id' => $mineArea->id,
            ]);

            $this->dispatch('notify', 
                type: 'success', 
                message: 'Mine area deleted successfully!'
            );

            $this->backToList();

        } catch (\Exception $e) {
            Log::error('Failed to delete mine area', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            $this->dispatch('notify', 
                type: 'error', 
                message: 'Failed to delete mine area'
            );
        }
    }

    /**
     * Toggle mine area status.
     */
    public function toggleStatus(MineArea $mineArea)
    {
        // Authorization: Ensure user can update this mine area
        $this->authorize('update', $mineArea);
        
        try {
            $newStatus = $mineArea->status === 'active' ? 'inactive' : 'active';
            resolve(MineAreaService::class)->update($mineArea, ['status' => $newStatus]);

            $this->dispatch('notify', 
                type: 'success', 
                message: "Mine area status changed to {$newStatus}"
            );

        } catch (\Exception $e) {
            $this->dispatch('notify', 
                type: 'error', 
                message: 'Failed to update status'
            );
        }
    }

    /**
     * Export mine area as GeoJSON.
     */
    public function exportGeoJSON(MineArea $mineArea)
    {
        $geoJSON = resolve(MineAreaService::class)->exportGeoJSON($mineArea);
        
        $this->dispatch('download-geojson', 
            data: json_encode($geoJSON),
            filename: $mineArea->name . '_' . now()->format('Y-m-d') . '.geojson'
        );
    }

    /**
     * Add a coordinate to the polygon.
     */
    public function addCoordinate()
    {
        if (!$this->tempLat && !$this->tempLon) {
            $this->dispatch('notify', type: 'error', message: 'Please enter both latitude and longitude');
            return;
        }

        $latInput = trim((string) $this->tempLat);
        $lonInput = trim((string) $this->tempLon);

        // Allow "lat, lon" entered in the latitude field
        if (!$lonInput && preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*[,\s]+\s*(-?\d+(?:\.\d+)?)\s*$/', $latInput, $matches)) {
            $latInput = $matches[1];
            $lonInput = $matches[2];
        }

        if ($latInput === '' || $lonInput === '') {
            $this->dispatch('notify', type: 'error', message: 'Please enter both latitude and longitude');
            return;
        }

        $lat = floatval($latInput);
        $lon = floatval($lonInput);

        // Auto-swap if user entered lon/lat in reverse
        if (abs($lat) > 90 && abs($lon) <= 90) {
            [$lat, $lon] = [$lon, $lat];
        }

        // Validate coordinates
        if ($lat < -90 || $lat > 90) {
            $this->dispatch('notify', type: 'error', message: 'Latitude must be between -90 and 90');
            return;
        }

        if ($lon < -180 || $lon > 180) {
            $this->dispatch('notify', type: 'error', message: 'Longitude must be between -180 and 180');
            return;
        }

        // Prevent adding more than 4 coordinates
        if (count($this->coordinates) >= 4) {
            $this->dispatch('notify', type: 'error', message: 'Maximum of 4 coordinate points allowed. Remove a point to add a new one.');
            return;
        }

        $this->coordinates[] = ['lat' => $lat, 'lon' => $lon];
        $this->tempLat = null;
        $this->tempLon = null;

        $this->dispatch('coordinates-updated', coordinates: $this->coordinates);
        $this->dispatch('notify', type: 'success', message: 'Coordinate added');
        
        // Auto-disable drawing mode when 4 coordinates are reached
        if (count($this->coordinates) >= 4) {
            $this->isDrawing = false;
            $this->dispatch('drawing-mode-changed', drawing: false);
            $this->dispatch('notify', type: 'success', message: 'All 4 corner points added! Area boundary is complete.');
        }
    }

    /**
     * Preview coordinate location on map as user types.
     */
    public function updatedTempLat()
    {
        $this->previewCoordinate();
    }

    public function updatedTempLon()
    {
        $this->previewCoordinate();
    }

    private function previewCoordinate()
    {
        $latInput = trim((string) $this->tempLat);
        $lonInput = trim((string) $this->tempLon);

        if (!$lonInput && preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*[,\s]+\s*(-?\d+(?:\.\d+)?)\s*$/', $latInput, $matches)) {
            $latInput = $matches[1];
            $lonInput = $matches[2];
        }

        if ($latInput !== '' && $lonInput !== '') {
            $lat = floatval($latInput);
            $lon = floatval($lonInput);

            if (abs($lat) > 90 && abs($lon) <= 90) {
                [$lat, $lon] = [$lon, $lat];
            }

            // Only preview if coordinates are valid
            if ($lat >= -90 && $lat <= 90 && $lon >= -180 && $lon <= 180) {
                $this->dispatch('preview-coordinate', lat: $lat, lon: $lon);
            }
        }
    }

    /**
     * Remove a coordinate from the polygon.
     */
    public function removeCoordinate(int $index)
    {
        if (isset($this->coordinates[$index])) {
            unset($this->coordinates[$index]);
            $this->coordinates = array_values($this->coordinates); // Re-index array
            
            $this->dispatch('coordinates-updated', coordinates: $this->coordinates);
            $this->dispatch('notify', type: 'success', message: 'Coordinate removed');
        }
    }

    /**
     * Clear all coordinates.
     */
    public function clearCoordinates()
    {
        $this->coordinates = [];
        $this->dispatch('coordinates-updated', coordinates: $this->coordinates);
        $this->dispatch('notify', type: 'success', message: 'All coordinates cleared');
    }

    /**
     * Toggle drawing mode on map.
     */
    public function toggleDrawing()
    {
        $this->isDrawing = !$this->isDrawing;
        $this->dispatch('drawing-mode-changed', drawing: $this->isDrawing);
    }

    /**
     * Handle coordinate added from map click.
     */
    public function addCoordinateFromMap(float $lat, float $lon)
    {
        // Prevent adding more than 4 coordinates
        if (count($this->coordinates) >= 4) {
            $this->dispatch('notify', type: 'error', message: 'Maximum of 4 coordinate points allowed. Remove a point to add a new one.');
            return;
        }

        $this->coordinates[] = ['lat' => $lat, 'lon' => $lon];
        $this->dispatch('coordinates-updated', coordinates: $this->coordinates);
        $this->dispatch('notify', type: 'success', message: 'Coordinate added from map');
        
        // Auto-disable drawing mode when 4 coordinates are reached
        if (count($this->coordinates) >= 4) {
            $this->isDrawing = false;
            $this->dispatch('drawing-mode-changed', drawing: false);
            $this->dispatch('notify', type: 'success', message: 'All 4 corner points added! Area boundary is complete.');
        }
    }

    /**
     * Paginate through filtered results.
     */
    public function getFilteredMineAreas()
    {
        if (!$this->team || !$this->team instanceof Team || !$this->team->exists) {
            return collect();
        }

        $query = $this->team->mineAreas();

        // Filter by type
        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        // Filter by status
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        // Search by name or description (parameterized to prevent SQL injection)
        if ($this->searchTerm) {
            $searchTerm = trim($this->searchTerm);
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        return $query->with('machines', 'plans', 'production')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    // ========== SHIFT MANAGEMENT ==========

    public function openShiftModal()
    {
        $this->resetShiftForm();
        $this->showShiftModal = true;
    }

    public function closeShiftModal()
    {
        $this->showShiftModal = false;
        $this->resetShiftForm();
    }

    public function resetShiftForm()
    {
        $this->editingShiftIndex = null;
        $this->shiftName = '';
        $this->shiftStartTime = '';
        $this->shiftEndTime = '';
        $this->shiftDays = [];
    }

    public function editShift($index)
    {
        $shift = $this->shifts[$index] ?? null;
        if ($shift) {
            $this->editingShiftIndex = $index;
            $this->shiftName = $shift['name'];
            $this->shiftStartTime = $shift['start_time'];
            $this->shiftEndTime = $shift['end_time'];
            $this->shiftDays = $shift['days'];
            $this->showShiftModal = true;
        }
    }

    public function saveShift()
    {
        $this->validate([
            'shiftName' => 'required|string|max:100',
            'shiftStartTime' => 'required',
            'shiftEndTime' => 'required',
            'shiftDays' => 'required|array|min:1',
        ]);

        $shift = [
            'name' => $this->shiftName,
            'start_time' => $this->shiftStartTime,
            'end_time' => $this->shiftEndTime,
            'days' => $this->shiftDays,
        ];

        if ($this->editingShiftIndex !== null) {
            $this->shifts[$this->editingShiftIndex] = $shift;
            $message = 'Shift updated successfully';
        } else {
            $this->shifts[] = $shift;
            $message = 'Shift added successfully';
        }

        // If editing existing mine area, update it
        if ($this->currentMineArea) {
            $this->currentMineArea->update(['shifts' => $this->shifts]);
        }

        $this->dispatch('notify', type: 'success', message: $message);
        $this->closeShiftModal();
    }

    public function deleteShift($index)
    {
        unset($this->shifts[$index]);
        $this->shifts = array_values($this->shifts);

        if ($this->currentMineArea) {
            $this->currentMineArea->update(['shifts' => $this->shifts]);
        }

        $this->dispatch('notify', type: 'success', message: 'Shift deleted successfully');
    }

    // ========== MATERIAL TYPES MANAGEMENT ==========

    public function openMaterialModal()
    {
        $this->newMaterialType = '';
        $this->showMaterialModal = true;
    }

    public function closeMaterialModal()
    {
        $this->showMaterialModal = false;
        $this->newMaterialType = '';
    }

    public function addMaterialType()
    {
        $this->validate([
            'newMaterialType' => 'required|string|max:100',
        ]);

        if (!in_array($this->newMaterialType, $this->material_types)) {
            $this->material_types[] = $this->newMaterialType;

            if ($this->currentMineArea) {
                $this->currentMineArea->update(['material_types' => $this->material_types]);
            }

            $this->dispatch('notify', type: 'success', message: 'Material type added successfully');
            $this->closeMaterialModal();
        } else {
            $this->dispatch('notify', type: 'error', message: 'Material type already exists');
        }
    }

    public function removeMaterialType($index)
    {
        unset($this->material_types[$index]);
        $this->material_types = array_values($this->material_types);

        if ($this->currentMineArea) {
            $this->currentMineArea->update(['material_types' => $this->material_types]);
        }

        $this->dispatch('notify', type: 'success', message: 'Material type removed successfully');
    }

    // ========== MINING TARGETS MANAGEMENT ==========

    public function openTargetsModal()
    {
        $this->showTargetsModal = true;
    }

    public function closeTargetsModal()
    {
        $this->showTargetsModal = false;
    }

    public function saveTargets()
    {
        $this->validate([
            'mining_targets.daily' => 'required|numeric|min:0',
            'mining_targets.weekly' => 'required|numeric|min:0',
            'mining_targets.monthly' => 'required|numeric|min:0',
            'mining_targets.yearly' => 'required|numeric|min:0',
            'mining_targets.unit' => 'required|string|in:tonnes,cubic_meters,units',
        ]);

        if ($this->currentMineArea) {
            $this->currentMineArea->update(['mining_targets' => $this->mining_targets]);
            $this->dispatch('notify', type: 'success', message: 'Mining targets updated successfully');
        } else {
            $this->dispatch('notify', type: 'info', message: 'Targets will be saved when mine area is created');
        }

        $this->closeTargetsModal();
    }
}
