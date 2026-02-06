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
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class FuelManagement extends Component
{
    public $selectedPeriod = 'week';
    public $showLowFuelOnly = false;
    
    // Monthly allocation form
    public $showAllocationModal = false;
    public $allocationYear;
    public $allocationMonth;
    public $allocatedLiters;
    public $fuelPricePerLiter;
    public $allocationNotes = '';
    public $mineAreaId = null;
    
    // Tank creation form
    public $showTankModal = false;
    public $tankName = '';
    public $tankNumber = '';
    public $tankMineAreaId = null;
    public $tankCapacity = '';
    public $tankMinimumLevel = '';
    public $tankFuelType = 'diesel';
    public $tankLocationDescription = '';
    public $tankNotes = '';
    
    public function mount()
    {
        $this->allocationYear = now()->year;
        $this->allocationMonth = now()->month;
    }
    
    public function openAllocationModal()
    {
        $this->showAllocationModal = true;
    }
    
    public function closeAllocationModal()
    {
        $this->showAllocationModal = false;
        $this->reset(['allocatedLiters', 'fuelPricePerLiter', 'allocationNotes', 'mineAreaId']);
    }
    
    public function openTankModal()
    {
        $this->showTankModal = true;
    }
    
    public function closeTankModal()
    {
        $this->showTankModal = false;
        $this->reset(['tankName', 'tankNumber', 'tankMineAreaId', 'tankCapacity', 'tankMinimumLevel', 'tankFuelType', 'tankLocationDescription', 'tankNotes']);
    }
    
    public function saveTank()
    {
        $this->validate([
            'tankName' => 'required|string|max:255',
            'tankNumber' => 'nullable|string|max:50',
            'tankMineAreaId' => 'nullable|exists:mine_areas,id',
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
        
        // If mine area is specified, verify it belongs to the team
        if ($this->tankMineAreaId) {
            $mineArea = MineArea::where('id', $this->tankMineAreaId)
                ->where('team_id', $teamId)
                ->first();
                
            if (!$mineArea) {
                $this->dispatch('notify', type: 'error', message: 'Invalid mine area selected');
                return;
            }
        }
        
        try {
            FuelTank::create([
                'team_id' => $teamId,
                'mine_area_id' => $this->tankMineAreaId,
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
            
            $this->dispatch('notify', type: 'success', message: 'Fuel tank created successfully');
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
            'mineAreaId' => 'nullable|exists:mine_areas,id',
            'allocationNotes' => 'nullable|string|max:1000',
        ]);
        
        $user = Auth::user();
        if (!$user || !$user->current_team_id) {
            $this->dispatch('notify', type: 'error', message: 'User session invalid');
            return;
        }
        
        $teamId = $user->current_team_id;
        
        // If mine area is specified, verify it belongs to the team
        if ($this->mineAreaId) {
            $mineArea = MineArea::where('id', $this->mineAreaId)
                ->where('team_id', $teamId)
                ->first();
                
            if (!$mineArea) {
                $this->dispatch('notify', type: 'error', message: 'Invalid mine area selected');
                return;
            }
        }
        
        try {
            $totalBudget = $this->allocatedLiters * $this->fuelPricePerLiter;
            
            $allocation = FuelMonthlyAllocation::updateOrCreate(
                [
                    'team_id' => $teamId,
                    'year' => $this->allocationYear,
                    'month' => $this->allocationMonth,
                    'mine_area_id' => $this->mineAreaId,
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
        
        // Tanks overview
        $tanks = FuelTank::where('team_id', $teamId)
            ->with('mineArea')
            ->when($this->showLowFuelOnly, fn($q) => $q->lowFuel())
            ->get();
        
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

        return view('livewire.fuel-management', [
            'tanks' => $tanks,
            'tankStats' => $tankStats,
            'recentTransactions' => $recentTransactions,
            'transactionStats' => $transactionStats,
            'activeAlerts' => $activeAlerts,
            'topConsumers' => $topConsumers,
            'currentAllocation' => $currentAllocation,
            'aiRecommendations' => $aiRecommendations,
            'aiInsights' => $aiInsights,
            'mineAreas' => MineArea::where('team_id', $teamId)->where('status', 'active')->orderBy('name')->get(),
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
