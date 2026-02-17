<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\FuelTank;
use App\Models\FuelTransaction;
use App\Models\FuelAlert;
use App\Models\FuelMonthlyAllocation;
use App\Models\Machine;
use App\Models\MineArea;
use App\Services\AI\FuelPredictorAgent;
use App\Services\FuelManagementService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class FuelManagement extends Component
{
    // Unified modal state
    public $showManageModal = false;
    public $manageTab = 'dispense'; // 'dispense', 'allocation', 'tank'

    // Dispense Fuel form
    public $transactionTankId = '';
    public $transactionQuantity = '';
    public $transactionType = 'dispensing';
    public $transactionMineAreaId = '';
    public $transactionError = '';

    public $selectedPeriod = 'week';
    public $showLowFuelOnly = false;

    // Monthly allocation form
    public $allocationYear;
    public $allocationMonth;
    public $allocatedLiters;
    public $fuelPricePerLiter;
    public $allocationNotes = '';
    public $mineAreaId = '';

    // Tank creation form
    public $tankName = '';
    public $tankNumber = '';
    public $tankCapacity = '';
    public $tankMinimumLevel = '';
    public $tankFuelType = 'diesel';
    public $tankLocationDescription = '';
    public $tankNotes = '';
    public $tankMineAreaId = '';
    public $selectedTankId = '';

    public function recordDispensingTransaction()
    {
        $this->transactionError = '';
        $this->validate([
            'transactionTankId' => 'required|exists:fuel_tanks,id',
            'transactionQuantity' => 'required|numeric|min:1',
        ]);

        $tank = FuelTank::find($this->transactionTankId);
        if (!$tank) {
            $this->transactionError = 'Selected tank not found.';
            return;
        }

        $mineAreaId = $tank->mine_area_id;
        $year = now()->year;
        $month = now()->month;
        $allocation = FuelMonthlyAllocation::where('team_id', $tank->team_id)
            ->where('mine_area_id', $mineAreaId)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if (!$allocation) {
            $this->transactionError = 'No monthly allocation set for this mine area.';
            return;
        }

        $remaining = $allocation->remaining_liters;
        if ($this->transactionQuantity > $remaining) {
            $this->transactionError = 'Dispensing this amount would exceed the monthly allocation for this mine area. Remaining: ' . number_format($remaining, 2) . 'L.';
            return;
        }

        // Build transaction payload and record via service
        $unitPrice = $allocation->fuel_price_per_liter ?? 0;
        $totalCost = round($unitPrice * $this->transactionQuantity, 2);

        $service = new FuelManagementService();
        try {
            $transaction = $service->recordTransaction([
                'team_id' => $tank->team_id,
                'fuel_tank_id' => $tank->id,
                'machine_id' => $this->transactionMineAreaId ?: null,
                'user_id' => auth()->id(),
                'transaction_type' => 'dispensing',
                'quantity_liters' => $this->transactionQuantity,
                'unit_price' => $unitPrice,
                'total_cost' => $totalCost,
                'fuel_type' => $tank->fuel_type,
                'transaction_date' => now(),
                'monthly_allocation_id' => $allocation->id ?? null,
                'notes' => null,
            ]);

            // Refresh allocation consumption
            if ($allocation) {
                $allocation->updateConsumption();
            }

            $this->dispatch('notify', type: 'success', message: 'Dispensing transaction recorded.');
            $this->reset(['transactionTankId', 'transactionQuantity', 'transactionMineAreaId']);

        } catch (\Exception $e) {
            \Log::error('Failed to record dispensing transaction', ['error' => $e->getMessage()]);
            $this->transactionError = 'Failed to record transaction. ' . $e->getMessage();
        }
    }
    
    public function mount()
    {
        $this->allocationYear = now()->year;
        $this->allocationMonth = now()->month;
    }
    
    // Unified modal open/close
    public function openManageModal($tab = 'dispense')
    {
        $this->showManageModal = true;
        $this->setManageTab($tab);
    }

    public function closeManageModal()
    {
        $this->showManageModal = false;
    }

    public function closeTankModal()
    {
        $this->showManageModal = false;
        $this->manageTab = 'dispense';
        $this->reset(['tankName', 'tankNumber', 'tankCapacity', 'tankMinimumLevel', 'tankFuelType', 'tankLocationDescription', 'tankNotes', 'tankMineAreaId']);
    }

    public function closeAllocationModal()
    {
        $this->showManageModal = false;
        $this->manageTab = 'dispense';
        $this->reset(['allocationYear', 'allocationMonth', 'allocatedLiters', 'fuelPricePerLiter', 'allocationNotes']);
    }

    public function setManageTab($tab)
    {
        $this->manageTab = $tab;
        // Optionally reset form fields when switching tabs
        if ($tab === 'dispense') {
            $this->reset(['transactionTankId', 'transactionQuantity', 'transactionError']);
        } elseif ($tab === 'allocation') {
            $this->reset(['allocatedLiters', 'fuelPricePerLiter', 'allocationNotes']);
        } elseif ($tab === 'tank') {
            $this->reset(['tankName', 'tankNumber', 'tankCapacity', 'tankMinimumLevel', 'tankFuelType', 'tankLocationDescription', 'tankNotes']);
        }
    }
    
    public function saveTank()
    {
        $this->validate([
            'tankName' => 'required|string|max:255',
            'tankNumber' => 'nullable|string|max:50',
            'tankCapacity' => 'required|numeric|min:1|max:999999999',
            'tankMinimumLevel' => 'required|numeric|min:0|max:999999999',
            'tankFuelType' => 'required|in:diesel,petrol,aviation_fuel,biodiesel',
            'tankLocationDescription' => 'nullable|string|max:500',
            'tankNotes' => 'nullable|string|max:1000',
        ]);
        
        $user = Auth::user();
        if (!$user || !$user->current_team_id) {
            $this->dispatch('notify', type: 'error', message: 'User session invalid');
            return;
        }
        
        $teamId = $user->current_team_id;
        
        try {
            $tank = FuelTank::create([
                'team_id' => $teamId,
                'name' => $this->tankName,
                'tank_number' => $this->tankNumber,
                'location_description' => $this->tankLocationDescription,
                'capacity_liters' => $this->tankCapacity,
                'current_level_liters' => $this->tankCapacity, // Start full
                'minimum_level_liters' => $this->tankMinimumLevel,
                'fuel_type' => $this->tankFuelType,
                'status' => 'active',
                'notes' => strip_tags($this->tankNotes),
            ]);

            // Ensure the newly created tank is immediately selected in the dispense dropdown
            $this->transactionTankId = $tank->id;
            $this->selectedTankId = $tank->id;

            $this->dispatch('notify', type: 'success', message: 'Fuel tank created successfully');
            // Notify frontend and keep selection so new tank appears in dispense dropdown
            $this->dispatch('tank-created', ['id' => $tank->id, 'name' => $tank->name]);
            $this->closeTankModal();
            
        } catch (\Exception $e) {
            \Log::error('Failed to create fuel tank', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            $this->dispatch('notify', type: 'error', message: 'Failed to create tank');
        }
    }
    
    public function saveAllocation()
    {
        $this->validate([
            'allocationYear' => 'required|integer|min:2020|max:2100',
            'allocationMonth' => 'required|integer|min:1|max:12',
            'allocatedLiters' => 'required|numeric|min:1|max:999999999',
            'fuelPricePerLiter' => 'required|numeric|min:0.01|max:999999',
            'allocationNotes' => 'nullable|string|max:1000',
        ]);
        
        $user = Auth::user();
        if (!$user || !$user->current_team_id) {
            $this->dispatch('notify', type: 'error', message: 'User session invalid');
            return;
        }
        
        $teamId = $user->current_team_id;
        
        try {
            $totalBudget = $this->allocatedLiters * $this->fuelPricePerLiter;
            
            $allocation = FuelMonthlyAllocation::updateOrCreate(
                [
                    'team_id' => $teamId,
                    'year' => $this->allocationYear,
                    'month' => $this->allocationMonth,
                ],
                [
                    'allocated_liters' => $this->allocatedLiters,
                    'fuel_price_per_liter' => $this->fuelPricePerLiter,
                    'total_budget_zar' => $totalBudget,
                    'remaining_liters' => $this->allocatedLiters,
                    'remaining_budget_zar' => $totalBudget,
                    'status' => 'active',
                    'notes' => strip_tags($this->allocationNotes), // Sanitize HTML
                ]
            );
            
            $allocation->updateConsumption();
            
            $this->dispatch('notify', type: 'success', message: 'Monthly allocation saved successfully');
            $this->closeAllocationModal();
            
        } catch (\Exception $e) {
            \Log::error('Failed to save fuel allocation', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            $this->dispatch('notify', type: 'error', message: 'Failed to save allocation');
        }
    }
    
    public function render()
    {
        $teamId = auth()->user()->current_team_id;
        
        // Get date range based on period
        $dateRange = $this->getDateRange();

        // Get current month allocation
        $currentAllocation = FuelMonthlyAllocation::where('team_id', $teamId)
            ->where('year', now()->year)
            ->where('month', now()->month)
            ->with('mineArea')
            ->first();

        // Determine whether the current user can see inactive tanks (admins per-team)
        $currentUser = auth()->user();
        $canSeeInactive = $currentUser?->hasRole('admin') ?? false;

        // Tanks overview: include inactive tanks for admins, otherwise only active tanks
        $tanks = FuelTank::where('team_id', $teamId)
            ->when(!$canSeeInactive, fn($q) => $q->where('status', 'active'))
            ->with('mineArea')
            ->when($this->showLowFuelOnly, fn($q) => $q->lowFuel())
            ->get();

        // Machines for dispensing form
        $machines = Machine::where('team_id', $teamId)->orderBy('name')->get();

        // Get AI-powered fuel insights
        $aiAgent = new FuelPredictorAgent();
        $aiAnalysis = $aiAgent->analyze(auth()->user()->currentTeam);
        $aiRecommendations = collect($aiAnalysis['recommendations'] ?? [])->take(5);
        $aiInsights = collect($aiAnalysis['insights'] ?? [])->take(3);

        $tankStats = [
            'total' => $tanks->count(),
            'active' => $tanks->where('status', 'active')->count(),
            'low_fuel' => $tanks->filter(fn($t) => $t->isBelowMinimum())->count(),
            'critical' => $tanks->filter(fn($t) => $t->isCritical())->count(),
            'total_capacity' => $tanks->sum('capacity_liters'),
            'current_level' => $tanks->sum('current_level_liters'),
        ];

        // Recent transactions
        $recentTransactions = FuelTransaction::where('team_id', $teamId)
            ->with(['fuelTank', 'machine', 'user'])
            ->whereBetween('transaction_date', [$dateRange['start'], $dateRange['end']])
            ->latest('transaction_date')
            ->limit(10)
            ->get();

        // Transaction statistics
        $transactionStats = [
            'total_refueled' => FuelTransaction::where('team_id', $teamId)
                ->whereBetween('transaction_date', [$dateRange['start'], $dateRange['end']])
                ->whereIn('transaction_type', ['refill', 'delivery'])
                ->sum('quantity_liters'),
            'total_consumed' => FuelTransaction::where('team_id', $teamId)
                ->whereBetween('transaction_date', [$dateRange['start'], $dateRange['end']])
                ->where('transaction_type', 'dispensing')
                ->sum('quantity_liters'),
            'total_cost' => FuelTransaction::where('team_id', $teamId)
                ->whereBetween('transaction_date', [$dateRange['start'], $dateRange['end']])
                ->sum('total_cost'),
            'transaction_count' => FuelTransaction::where('team_id', $teamId)
                ->whereBetween('transaction_date', [$dateRange['start'], $dateRange['end']])
                ->count(),
        ];
        
        // Active alerts
        $activeAlerts = FuelAlert::where('team_id', $teamId)
            ->with(['fuelTank', 'machine'])
            ->active()
            ->latest('triggered_at')
            ->limit(5)
            ->get();
        
        // Top consumers
        $topConsumers = FuelTransaction::where('team_id', $teamId)
            ->whereBetween('transaction_date', [$dateRange['start'], $dateRange['end']])
            ->where('transaction_type', 'dispensing')
            ->whereNotNull('machine_id')
            ->selectRaw('machine_id, SUM(quantity_liters) as total_consumed, SUM(total_cost) as total_cost')
            ->groupBy('machine_id')
            ->orderByDesc('total_consumed')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $machine = Machine::find($item->machine_id);
                return [
                    'machine' => $machine,
                    'total_consumed' => $item->total_consumed,
                    'total_cost' => $item->total_cost ?? 0,
                ];
            });

        $mineAreas = MineArea::where('team_id', $teamId)->orderBy('name')->get();

        return view('livewire.fuel-management', [
            'tanks' => $tanks,
            'machines' => $machines,
            'tankStats' => $tankStats,
            'recentTransactions' => $recentTransactions,
            'transactionStats' => $transactionStats,
            'activeAlerts' => $activeAlerts,
            'topConsumers' => $topConsumers,
            'currentAllocation' => $currentAllocation,
            'aiRecommendations' => $aiRecommendations,
            'aiInsights' => $aiInsights,
            'mineAreas' => $mineAreas,
            'canSeeInactiveTanks' => $canSeeInactive,
        ]);
    }
    
    protected function getDateRange()
    {
        return match($this->selectedPeriod) {
            'today' => ['start' => now()->startOfDay(), 'end' => now()->endOfDay()],
            'week' => ['start' => now()->startOfWeek(), 'end' => now()->endOfWeek()],
            'month' => ['start' => now()->startOfMonth(), 'end' => now()->endOfMonth()],
            'quarter' => ['start' => now()->startOfQuarter(), 'end' => now()->endOfQuarter()],
            'year' => ['start' => now()->startOfYear(), 'end' => now()->endOfYear()],
            default => ['start' => now()->startOfWeek(), 'end' => now()->endOfWeek()],
        };
    }
}
