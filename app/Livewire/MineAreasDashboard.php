<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MineArea;
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

    public function render()
    {
        return view('livewire.mine-areas-dashboard', [
            'statistics' => $this->getStatistics(),
            'productionTrend' => $this->getProductionTrend(),
            'topAreas' => $this->getTopAreas(),
            'machineDistribution' => $this->getMachineDistribution(),
        ]);
    }
}
