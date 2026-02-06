<?php

namespace App\Livewire;

use App\Models\MineAreaProduction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ProductionDashboard extends Component
{
    public string $dateFilter = 'day';
    public ?string $startDate = null;
    public ?string $endDate = null;
    public bool $isCustomRange = false;

    public function mount(): void
    {
        // Set default dates based on filter
        $this->updateDateRange();
    }

    public function updatedDateFilter(): void
    {
        $this->isCustomRange = false;
        $this->updateDateRange();
    }

    public function updatedStartDate(): void
    {
        if ($this->startDate && $this->endDate) {
            $this->isCustomRange = true;
            $this->dateFilter = 'custom';
        }
    }

    public function updatedEndDate(): void
    {
        if ($this->startDate && $this->endDate) {
            $this->isCustomRange = true;
            $this->dateFilter = 'custom';
        }
    }

    public function applyCustomRange(): void
    {
        if ($this->startDate && $this->endDate) {
            $this->isCustomRange = true;
            $this->dateFilter = 'custom';
        }
    }

    private function updateDateRange(): void
    {
        $now = Carbon::now();
        
        switch ($this->dateFilter) {
            case 'day':
                $this->startDate = $now->toDateString();
                $this->endDate = $now->toDateString();
                break;
            case 'week':
                $this->startDate = $now->startOfWeek()->toDateString();
                $this->endDate = $now->endOfWeek()->toDateString();
                break;
            case 'month':
                $this->startDate = $now->startOfMonth()->toDateString();
                $this->endDate = $now->endOfMonth()->toDateString();
                break;
            case 'year':
                $this->startDate = $now->startOfYear()->toDateString();
                $this->endDate = $now->endOfYear()->toDateString();
                break;
        }
    }

    private function getDateRange(): array
    {
        if ($this->isCustomRange && $this->startDate && $this->endDate) {
            return [
                Carbon::parse($this->startDate),
                Carbon::parse($this->endDate)
            ];
        }

        return [
            Carbon::parse($this->startDate),
            Carbon::parse($this->endDate)
        ];
    }

    public function getProductionSummary(): array
    {
        $team = Auth::user()->currentTeam;
        [$start, $end] = $this->getDateRange();

        $summary = MineAreaProduction::whereHas('mineArea', function ($query) use ($team) {
                $query->where('team_id', $team->id);
            })
            ->whereBetween('recorded_date', [$start, $end])
            ->select([
                DB::raw('SUM(loads) as total_loads'),
                DB::raw('SUM(cycles) as total_cycles'),
                DB::raw('SUM(tonnage) as total_tonnage'),
                DB::raw('SUM(bcm) as total_bcm'),
                DB::raw('COUNT(DISTINCT mine_area_id) as active_areas'),
            ])
            ->first();

        return [
            'total_loads' => $summary->total_loads ?? 0,
            'total_cycles' => $summary->total_cycles ?? 0,
            'total_tonnage' => $summary->total_tonnage ?? 0,
            'total_bcm' => $summary->total_bcm ?? 0,
            'active_areas' => $summary->active_areas ?? 0,
        ];
    }

    public function getDailyProductionChart(): array
    {
        $team = Auth::user()->currentTeam;
        [$start, $end] = $this->getDateRange();

        $dailyData = MineAreaProduction::whereHas('mineArea', function ($query) use ($team) {
                $query->where('team_id', $team->id);
            })
            ->whereBetween('recorded_date', [$start, $end])
            ->select([
                'recorded_date',
                DB::raw('SUM(loads) as daily_loads'),
                DB::raw('SUM(cycles) as daily_cycles'),
                DB::raw('SUM(tonnage) as daily_tonnage'),
                DB::raw('SUM(bcm) as daily_bcm'),
            ])
            ->groupBy('recorded_date')
            ->orderBy('recorded_date')
            ->get();

        return $dailyData->map(function ($item) {
            return [
                'date' => Carbon::parse($item->recorded_date)->format('M d'),
                'loads' => $item->daily_loads ?? 0,
                'cycles' => $item->daily_cycles ?? 0,
                'tonnage' => $item->daily_tonnage ?? 0,
                'bcm' => $item->daily_bcm ?? 0,
            ];
        })->toArray();
    }

    public function getMaterialBreakdown(): array
    {
        $team = Auth::user()->currentTeam;
        [$start, $end] = $this->getDateRange();

        return MineAreaProduction::whereHas('mineArea', function ($query) use ($team) {
                $query->where('team_id', $team->id);
            })
            ->whereBetween('recorded_date', [$start, $end])
            ->whereNotNull('material_type')
            ->select([
                'material_type',
                DB::raw('SUM(tonnage) as total_tonnage'),
                DB::raw('SUM(loads) as total_loads'),
                DB::raw('COUNT(*) as records'),
            ])
            ->groupBy('material_type')
            ->orderByDesc('total_tonnage')
            ->get()
            ->map(function ($item) {
                return [
                    'material' => $item->material_type,
                    'tonnage' => $item->total_tonnage ?? 0,
                    'loads' => $item->total_loads ?? 0,
                    'records' => $item->records,
                ];
            })
            ->toArray();
    }

    public function getAreaPerformance(): array
    {
        $team = Auth::user()->currentTeam;
        [$start, $end] = $this->getDateRange();

        return MineAreaProduction::whereHas('mineArea', function ($query) use ($team) {
                $query->where('team_id', $team->id);
            })
            ->with('mineArea:id,name,type')
            ->whereBetween('recorded_date', [$start, $end])
            ->select([
                'mine_area_id',
                DB::raw('SUM(loads) as total_loads'),
                DB::raw('SUM(cycles) as total_cycles'),
                DB::raw('SUM(tonnage) as total_tonnage'),
                DB::raw('SUM(bcm) as total_bcm'),
            ])
            ->groupBy('mine_area_id')
            ->orderByDesc('total_tonnage')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'area_name' => $item->mineArea->name ?? 'Unknown',
                    'area_type' => $item->mineArea->type ?? 'Unknown',
                    'loads' => $item->total_loads ?? 0,
                    'cycles' => $item->total_cycles ?? 0,
                    'tonnage' => $item->total_tonnage ?? 0,
                    'bcm' => $item->total_bcm ?? 0,
                ];
            })
            ->toArray();
    }

    public function render()
    {
        return view('livewire.production-dashboard', [
            'summary' => $this->getProductionSummary(),
            'dailyChart' => $this->getDailyProductionChart(),
            'materialBreakdown' => $this->getMaterialBreakdown(),
            'areaPerformance' => $this->getAreaPerformance(),
        ]);
    }
}
