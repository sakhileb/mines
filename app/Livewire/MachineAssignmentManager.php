<?php

namespace App\Livewire;

use App\Models\MineArea;
use App\Models\Machine;
use App\Services\MineAreaService;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MachineAssignmentManager extends Component
{
    public bool $showUpgradePrompt = false;

    /**
     * Check if user can add more machines based on subscription plan.
     */
    public function canAddMoreMachines($countToAdd = 1)
    {
        $team = Auth::user()->currentTeam;
        $subscription = $team->subscription;
        $plan = $subscription ? $subscription->plan : null;
        $maxMachines = $plan ? $plan->max_machines : 0;
        $currentCount = $team->machines()->count();
        return ($currentCount + $countToAdd) <= $maxMachines;
    }
    public MineArea $mineArea;
    public MineAreaService $mineAreaService;

    // UI state
    public string $view = 'overview'; // overview, manage, assign, history
    public bool $showAssignForm = false;
    public string $assignmentMode = 'bulk'; // bulk or individual
    
    // Assignment data
    #[Validate('required|array|min:1')]
    public array $selectedMachineIds = [];

    #[Validate('nullable|array')]
    public array $machineNotes = [];

    public ?Machine $selectedMachine = null;
    public ?string $selectedNotes = null;

    // Filtering
    public string $filterStatus = ''; // assigned, unassigned, all
    public string $searchTerm = '';
    public bool $showOnlyUnassigned = true;

    // Bulk operations
    public bool $selectAll = false;
    public array $bulkOperations = [];

    public function mount(MineArea $mineArea)
    {
        if (!$mineArea || !$mineArea instanceof \App\Models\MineArea || !$mineArea->exists) {
            abort(404, 'Invalid mine area');
        }
        $this->mineArea = $mineArea;
        $this->mineAreaService = resolve(MineAreaService::class);
        $this->authorize('update', $mineArea);
    }

    public function render()
    {
        $query = Auth::user()->currentTeam->machines();

        // Filter by search term
        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->searchTerm}%")
                    ->orWhere('model', 'like', "%{$this->searchTerm}%");
            });
        }

        // Get machines with assignment info
        $machines = $query->with('mineAreas')
            ->orderBy('name')
            ->paginate(15);

        // Add assignment flag to each machine
        $machinesWithStatus = $machines->map(function ($machine) {
            $machine->is_assigned = $machine->mineAreas()
                ->where('mine_area_id', $this->mineArea->id)
                ->exists();
            return $machine;
        });

        // Filter by assignment status
        if ($this->showOnlyUnassigned) {
            $machinesWithStatus = $machinesWithStatus->filter(fn($m) => !$m->is_assigned);
        } elseif ($this->filterStatus === 'assigned') {
            $machinesWithStatus = $machinesWithStatus->filter(fn($m) => $m->is_assigned);
        } elseif ($this->filterStatus === 'unassigned') {
            $machinesWithStatus = $machinesWithStatus->filter(fn($m) => !$m->is_assigned);
        }

        $assignedMachines = $this->mineArea->machines()->with('latestMetric')->get();
        $assignmentHistory = $this->mineArea->machines()
            ->withPivot('assigned_at', 'unassigned_at', 'notes')
            ->wherePivotNotNull('unassigned_at')
            ->latest('mine_area_machine.unassigned_at')
            ->limit(20)
            ->get();

        return view('livewire.machine-assignment-manager', [
            'machines' => $machinesWithStatus,
            'assignedMachines' => $assignedMachines,
            'assignmentHistory' => $assignmentHistory,
            'unassignedCount' => Auth::user()->currentTeam->machines()
                ->whereDoesntHave('mineAreas', fn($q) => $q->where('mine_area_id', $this->mineArea->id))
                ->count(),
            'totalMachines' => Auth::user()->currentTeam->machines()->count(),
        ]);
    }

    /**
     * Switch to overview view.
     */
    public function switchToOverview()
    {
        $this->view = 'overview';
        $this->reset(['selectedMachineIds', 'selectedMachine', 'showAssignForm']);
    }

    /**
     * Switch to manage view.
     */
    public function switchToManage()
    {
        $this->view = 'manage';
        $this->showOnlyUnassigned = true;
    }

    /**
     * Switch to assignment view.
     */
    public function switchToAssign()
    {
        $this->view = 'assign';
        $this->showOnlyUnassigned = true;
    }

    /**
     * Switch to history view.
     */
    public function switchToHistory()
    {
        $this->view = 'history';
    }

    /**
     * Toggle machine selection.
     */
    public function toggleMachineSelection(Machine $machine)
    {
        if (in_array($machine->id, $this->selectedMachineIds)) {
            $this->selectedMachineIds = array_filter(
                $this->selectedMachineIds,
                fn($id) => $id !== $machine->id
            );
        } else {
            $this->selectedMachineIds[] = $machine->id;
        }
        $this->selectAll = false;
    }

    /**
     * Toggle select all.
     */
    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            $this->selectAll = false;
            $this->selectedMachineIds = [];
        } else {
            $this->selectAll = true;
            // Get all unassigned machine IDs
            $machineIds = Auth::user()->currentTeam->machines()
                ->whereDoesntHave('mineAreas', fn($q) => $q->where('mine_area_id', $this->mineArea->id))
                ->pluck('id')
                ->toArray();
            $this->selectedMachineIds = $machineIds;
        }
    }

    /**
     * Assign selected machines to mine area.
     */
    public function assignSelectedMachines()
    {
        if (empty($this->selectedMachineIds)) {
            $this->dispatch('notify', type: 'error', message: 'No machines selected');
            return;
        }
        // Enforce plan machine limit
        if (!$this->canAddMoreMachines(count($this->selectedMachineIds))) {
            $this->showUpgradePrompt = true;
            $this->dispatch('notify', type: 'error', message: 'You have reached your plan limit for machines. Please upgrade your subscription to add more.');
            return;
        }

        try {
            $this->mineAreaService->assignMachines(
                $this->mineArea,
                $this->selectedMachineIds,
                $this->bulkOperations['notes'] ?? null
            );

            Log::info('Machines assigned to mine area', [
                'mine_area_id' => $this->mineArea->id,
                'machine_count' => count($this->selectedMachineIds),
                'user_id' => Auth::id(),
            ]);

            $this->dispatch('notify',
                type: 'success',
                message: count($this->selectedMachineIds) . ' machine(s) assigned successfully'
            );

            $this->reset(['selectedMachineIds', 'selectAll', 'bulkOperations']);

        } catch (\Exception $e) {
            Log::error('Failed to assign machines', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            $this->dispatch('notify',
                type: 'error',
                message: 'Failed to assign machines: ' . $e->getMessage()
            );
        }
    }

    /**
     * Assign single machine.
     */
    public function assignSingleMachine(Machine $machine)
    {
        // Enforce plan machine limit
        if (!$this->canAddMoreMachines(1)) {
            $this->showUpgradePrompt = true;
            $this->dispatch('notify', type: 'error', message: 'You have reached your plan limit for machines. Please upgrade your subscription to add more.');
            return;
        }
        try {
            $this->mineAreaService->assignMachines(
                $this->mineArea,
                [$machine->id],
                $this->selectedNotes
            );

            Log::info('Machine assigned to mine area', [
                'mine_area_id' => $this->mineArea->id,
                'machine_id' => $machine->id,
                'user_id' => Auth::id(),
            ]);

            $this->dispatch('notify',
                type: 'success',
                message: "{$machine->name} assigned successfully"
            );

            $this->reset(['selectedMachine', 'selectedNotes', 'showAssignForm']);

        } catch (\Exception $e) {
            $this->dispatch('notify',
                type: 'error',
                message: 'Failed to assign machine'
            );
        }
    }

    /**
     * Unassign machine from mine area.
     */
    public function unassignMachine(Machine $machine)
    {
        try {
            $this->mineAreaService->unassignMachines(
                $this->mineArea,
                [$machine->id]
            );

            Log::info('Machine unassigned from mine area', [
                'mine_area_id' => $this->mineArea->id,
                'machine_id' => $machine->id,
                'user_id' => Auth::id(),
            ]);

            $this->dispatch('notify',
                type: 'success',
                message: "{$machine->name} unassigned successfully"
            );

        } catch (\Exception $e) {
            $this->dispatch('notify',
                type: 'error',
                message: 'Failed to unassign machine'
            );
        }
    }

    /**
     * Unassign multiple machines.
     */
    public function unassignMultipleMachines()
    {
        try {
            $this->mineAreaService->unassignMachines($this->mineArea, $this->selectedMachineIds);

            Log::info('Multiple machines unassigned', [
                'mine_area_id' => $this->mineArea->id,
                'count' => count($this->selectedMachineIds),
                'user_id' => Auth::id(),
            ]);

            $this->dispatch('notify',
                type: 'success',
                message: count($this->selectedMachineIds) . ' machine(s) unassigned'
            );

            $this->reset(['selectedMachineIds', 'selectAll']);

        } catch (\Exception $e) {
            $this->dispatch('notify',
                type: 'error',
                message: 'Failed to unassign machines'
            );
        }
    }

    /**
     * Update machine notes.
     */
    public function updateMachineNotes(Machine $machine, string $notes)
    {
        try {
            $machine->mineAreas()
                ->syncWithoutDetaching([
                    $this->mineArea->id => ['notes' => $notes, 'assigned_at' => now()]
                ]);

            $this->dispatch('notify',
                type: 'success',
                message: 'Notes updated'
            );

        } catch (\Exception $e) {
            $this->dispatch('notify',
                type: 'error',
                message: 'Failed to update notes'
            );
        }
    }

    /**
     * Show assign form for individual machine.
     */
    public function showAssignForm(Machine $machine)
    {
        $this->selectedMachine = $machine;
        $this->showAssignForm = true;
    }

    /**
     * Cancel assign form.
     */
    public function cancelAssignForm()
    {
        $this->reset(['selectedMachine', 'showAssignForm', 'selectedNotes']);
    }

    /**
     * Get assignment statistics.
     */
    public function getStats()
    {
        $total = Auth::user()->currentTeam->machines()->count();
        $assigned = $this->mineArea->machines()->count();
        $unassigned = $total - $assigned;

        return [
            'total' => $total,
            'assigned' => $assigned,
            'unassigned' => $unassigned,
            'percentage' => $total > 0 ? round(($assigned / $total) * 100) : 0,
        ];
    }

    /**
     * Get active machines in area (currently online).
     */
    public function getActiveMachinesInArea()
    {
        return $this->mineArea->machines()
            ->where('status', 'online')
            ->with('latestMetric')
            ->get();
    }

    /**
     * Export assignment report.
     */
    public function exportAssignmentReport()
    {
        try {
            $data = [
                'mine_area' => $this->mineArea->name,
                'total_machines' => $this->mineArea->machines()->count(),
                'assignments' => $this->mineArea->machines()
                    ->get(['name', 'model'])
                    ->map(fn($m) => [
                        'name' => $m->name,
                        'model' => $m->model,
                        'assigned_at' => $m->pivot->assigned_at ?? null,
                    ])
                    ->toArray(),
            ];

            $this->dispatch('download-report',
                data: json_encode($data),
                filename: 'mine-area-assignments-' . now()->format('Y-m-d') . '.json'
            );

        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to export report');
        }
    }
}
