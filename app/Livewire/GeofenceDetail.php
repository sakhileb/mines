<?php

namespace App\Livewire;

use App\Models\Geofence;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class GeofenceDetail extends Component
{
    public Geofence $geofence;

    public function mount(Geofence $geofence)
    {
        if ($geofence->team_id !== Auth::user()->currentTeam->id) {
            abort(403);
        }
        $this->geofence = $geofence;
    }

    public function render()
    {
        $recentEntries = $this->geofence->entries()
            ->with('machine')
            ->latest('entry_time')
            ->take(10)
            ->get();

        $machineCount = $this->geofence->entries()
            ->select('machine_id')
            ->distinct()
            ->count();

        $totalEntries = $this->geofence->entries()->count();

        return view('livewire.geofence-detail', [
            'recentEntries' => $recentEntries,
            'machineCount' => $machineCount,
            'totalEntries' => $totalEntries,
        ]);
    }
}
