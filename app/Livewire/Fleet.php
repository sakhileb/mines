<?php

namespace App\Livewire;

use App\Models\Machine;
use App\Services\AI\FleetOptimizerAgent;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Fleet extends Component
{
    public bool $showMineAreaAssignModal = false;
    public ?int $assigningMineAreaMachineId = null;
    public ?int $selectedMineAreaId = null;
    public function openMineAreaAssignModal(int $machineId): void
    {
        $this->assigningMineAreaMachineId = $machineId;
        $this->selectedMineAreaId = null;
        $this->showMineAreaAssignModal = true;
    }

    public function closeMineAreaAssignModal(): void
    {
        $this->showMineAreaAssignModal = false;
        $this->assigningMineAreaMachineId = null;
        $this->selectedMineAreaId = null;
    }

    public function assignToMineArea(): void
    {
        if (!$this->assigningMineAreaMachineId || !$this->selectedMineAreaId) {
            $this->dispatch('alert', message: 'Please select a mine area', type: 'error');
            return;
        }

        $machine = \App\Models\Machine::find($this->assigningMineAreaMachineId);
        $mineArea = \App\Models\MineArea::find($this->selectedMineAreaId);
        if (!$machine || !$mineArea || $machine->team_id !== Auth::user()->currentTeam->id || $mineArea->team_id !== Auth::user()->currentTeam->id) {
            abort(403);
        }

        // Use the MineAreaService to assign via the pivot table
        try {
            app(\App\Services\MineAreaService::class)->assignMachines($mineArea, [$machine->id]);
            $this->dispatch('alert', message: "Machine '{$machine->name}' assigned to mine area '{$mineArea->name}'", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('alert', message: 'Assignment failed: ' . $e->getMessage(), type: 'error');
            return;
        }
        $this->closeMineAreaAssignModal();
    }

public array $activityFeed = [];
public bool $isLoading = true;
use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public bool $showCreateModal = false;
    public bool $showAssignModal = false;
    public ?int $assigningMachineId = null;
    public ?int $selectedExcavatorId = null;

    // Create/Edit form properties
    public ?int $editingMachineId = null;
    public string $name = '';
    public string $model = '';
    public string $manufacturer = '';
    public string $machineType = '';
    public string $status = 'active';
    public string $serialNumber = '';
    public float $capacity = 0;
    public float $latitude = 0;
    public float $longitude = 0;

    protected $listeners = ['machineCreated' => 'machineCreated', 'machineDeleted' => 'machineDeleted'];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function toggleSort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function closeModal(): void
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->editingMachineId = null;
        $this->name = '';
        $this->model = '';
        $this->manufacturer = '';
        $this->machineType = '';
        $this->status = 'active';
        $this->serialNumber = '';
        $this->capacity = 0;
        $this->latitude = 0;
        $this->longitude = 0;
    }

    public function editMachine(Machine $machine): void
    {
        $this->editingMachineId = $machine->id;
        $this->name = $machine->name;
        $this->model = $machine->model;
        $this->manufacturer = $machine->manufacturer ?? '';
        $this->machineType = $machine->machine_type;
        $this->status = $machine->status;
        $this->serialNumber = $machine->serial_number;
        $this->capacity = $machine->capacity ?? 0;
        $this->latitude = $machine->latitude ?? 0;
        $this->longitude = $machine->longitude ?? 0;
        $this->showCreateModal = true;
    }

    public function saveMachine(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'machineType' => 'required|string|max:255',
            'status' => 'required|in:active,idle,maintenance',
            'serialNumber' => 'nullable|string|max:255',
            'capacity' => 'nullable|numeric|min:0',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $team = Auth::user()->currentTeam;

        if ($this->editingMachineId) {
            $machine = Machine::findOrFail($this->editingMachineId);
            if ($machine->team_id !== $team->id) {
                abort(403);
            }
            $machine->update([
                'name' => $this->name,
                'model' => $this->model,
                'manufacturer' => $this->manufacturer ?: null,
                'machine_type' => $this->machineType,
                'status' => $this->status,
                'serial_number' => $this->serialNumber,
                'capacity' => $this->capacity ?: null,
                'latitude' => $this->latitude ?: null,
                'longitude' => $this->longitude ?: null,
            ]);
            $this->dispatch('alert', message: 'Machine updated successfully', type: 'success');
        } else {
            Machine::create([
                'team_id' => $team->id,
                'name' => $this->name,
                'model' => $this->model,
                'manufacturer' => $this->manufacturer ?: null,
                'machine_type' => $this->machineType,
                'status' => $this->status,
                'serial_number' => $this->serialNumber,
                'capacity' => $this->capacity ?: null,
                'latitude' => $this->latitude ?: null,
                'longitude' => $this->longitude ?: null,
            ]);
            $this->dispatch('alert', message: 'Machine created successfully', type: 'success');
        }

        $this->closeModal();
    }

    public function deleteMachine(Machine $machine): void
    {
        if ($machine->team_id !== Auth::user()->currentTeam->id) {
            abort(403);
        }

        $machineName = $machine->name;
        $machine->delete();
        $this->dispatch('alert', message: "Machine '{$machineName}' deleted successfully", type: 'success');
    }

    public function openAssignModal(int $machineId): void
    {
        $this->assigningMachineId = $machineId;
        $this->selectedExcavatorId = null;
        
        $machine = Machine::find($machineId);
        if ($machine && $machine->excavator_id) {
            $this->selectedExcavatorId = $machine->excavator_id;
        }
        
        $this->showAssignModal = true;
    }

    public function closeAssignModal(): void
    {
        $this->showAssignModal = false;
        $this->assigningMachineId = null;
        $this->selectedExcavatorId = null;
    }

    public function assignToExcavator(): void
    {
        if (!$this->assigningMachineId || !$this->selectedExcavatorId) {
            $this->dispatch('alert', message: 'Please select an excavator', type: 'error');
            return;
        }

        $machine = Machine::find($this->assigningMachineId);
        
        if (!$machine || $machine->team_id !== Auth::user()->currentTeam->id) {
            abort(403);
        }

        // Prevent assigning an excavator to itself
        if ($machine->id === $this->selectedExcavatorId) {
            $this->dispatch('alert', message: 'Cannot assign a machine to itself', type: 'error');
            return;
        }

        $machine->assignToExcavator($this->selectedExcavatorId);
        
        $excavator = Machine::find($this->selectedExcavatorId);
        $this->dispatch('alert', message: "Machine '{$machine->name}' assigned to '{$excavator->name}'", type: 'success');
        
        $this->closeAssignModal();
    }

    public function unassignFromExcavator(int $machineId): void
    {
        $machine = Machine::find($machineId);
        
        if (!$machine || $machine->team_id !== Auth::user()->currentTeam->id) {
            abort(403);
        }

        $machineName = $machine->name;
        $machine->unassignFromExcavator();
        
        $this->dispatch('alert', message: "Machine '{$machineName}' unassigned from excavator", type: 'success');
    }

    public function render()
    {
        $this->isLoading = true;
        $team = Auth::user()->currentTeam;

        $machinesQuery = Machine::where('team_id', $team->id)
            ->with('excavator')
            ->when($this->search, function ($query) {
                return $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('model', 'like', "%{$this->search}%")
                    ->orWhere('manufacturer', 'like', "%{$this->search}%");
            })
            ->when($this->statusFilter, function ($query) {
                return $query->where('status', $this->statusFilter);
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        // Get all excavators for assignment dropdown
        $excavators = Machine::where('team_id', $team->id)
            ->whereIn('machine_type', ['excavator', 'digger', 'loader'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $statusStats = [
            'active' => Machine::where('team_id', $team->id)->where('status', 'active')->count(),
            'idle' => Machine::where('team_id', $team->id)->where('status', 'idle')->count(),
            'maintenance' => Machine::where('team_id', $team->id)->where('status', 'maintenance')->count(),
        ];

        // Activity Feed
        $this->activityFeed = \App\Models\ActivityLog::where('team_id', $team->id)
            ->with('user')
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

        // AI Fleet Optimization Analysis
        $aiAgent = new FleetOptimizerAgent();
        $aiAnalysis = $aiAgent->analyze($team);
        $aiRecommendations = collect($aiAnalysis['recommendations'])->take(5);
        $aiInsights = collect($aiAnalysis['insights'])->take(3);

        $this->isLoading = false;

        $mineAreas = \App\Models\MineArea::where('team_id', $team->id)->where('status', 'active')->orderBy('name')->get();
        return view('livewire.fleet', [
            'machines' => $machinesQuery,
            'excavators' => $excavators,
            'mineAreas' => $mineAreas,
            'statusStats' => $statusStats,
            'aiRecommendations' => $aiRecommendations,
            'aiInsights' => $aiInsights,
            'activityFeed' => $this->activityFeed,
            'isLoading' => $this->isLoading,
        ]);
    }
}
