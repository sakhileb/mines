<?php

namespace App\Livewire;

use App\Models\Alert;
use App\Traits\RealtimeUpdates;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Alerts extends Component
{
    use WithPagination, RealtimeUpdates;

    public $search = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $selectedPriority = 'all';
    public $selectedStatus = 'all';
    public $selectedType = 'all';
    public $showDetailsModal = false;
    public $selectedAlertId = null;

    protected $alertPriorities = [
        'critical' => 'Critical',
        'high' => 'High',
        'medium' => 'Medium',
        'low' => 'Low',
    ];

    protected $alertTypes = [
        'temperature' => 'Temperature Warning',
        'fuel' => 'Fuel Level',
        'maintenance' => 'Maintenance Due',
        'sensor' => 'Sensor Fault',
        'geofence' => 'Geofence Breach',
        'downtime' => 'Extended Downtime',
    ];

    public function mount(): void
    {
        // Initialize real-time updates
        $this->initializeRealtimeUpdates();
        $this->subscribeToTeamAlerts();
    }

    public function getAlerts()
    {
        $team = Auth::user()->currentTeam;

        return Alert::where('team_id', $team->id)
            ->when($this->search, function ($query) {
                $query->where('title', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%");
            })
            ->when($this->selectedPriority !== 'all', function ($query) {
                $query->where('priority', $this->selectedPriority);
            })
            ->when($this->selectedStatus !== 'all', function ($query) {
                $query->where('status', $this->selectedStatus);
            })
            ->when($this->selectedType !== 'all', function ($query) {
                $query->where('type', $this->selectedType);
            })
            ->with('machine')
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(15);
    }

    public function setSortBy($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function acknowledgeAlert($alertId)
    {
        $team = Auth::user()->currentTeam;
        $alert = Alert::where('team_id', $team->id)->find($alertId);

        if ($alert) {
            $alert->update([
                'status' => 'acknowledged',
                'acknowledged_by' => Auth::id(),
                'acknowledged_at' => now(),
            ]);
            $this->dispatch('notify', message: 'Alert acknowledged');
        }
    }

    public function resolveAlert($alertId)
    {
        $team = Auth::user()->currentTeam;
        $alert = Alert::where('team_id', $team->id)->find($alertId);

        if ($alert) {
            $alert->update([
                'status' => 'resolved',
                'resolved_by' => Auth::id(),
                'resolved_at' => now(),
            ]);
            $this->dispatch('notify', message: 'Alert resolved');
        }
    }

    public function dismissAlert($alertId)
    {
        $team = Auth::user()->currentTeam;
        $alert = Alert::where('team_id', $team->id)->find($alertId);

        if ($alert) {
            $alert->update([
                'status' => 'dismissed',
                'dismissed_by' => Auth::id(),
                'dismissed_at' => now(),
            ]);
            $this->dispatch('notify', message: 'Alert dismissed');
        }
    }

    public function showDetails($alertId)
    {
        $this->selectedAlertId = $alertId;
        $this->showDetailsModal = true;
    }

    public function closeDetails()
    {
        $this->showDetailsModal = false;
        $this->selectedAlertId = null;
    }

    public function getSelectedAlert()
    {
        if ($this->selectedAlertId) {
            return Alert::with('machine')->find($this->selectedAlertId);
        }
        return null;
    }

    public function render()
    {
        return view('livewire.alerts', [
            'alerts' => $this->getAlerts(),
            'alertPriorities' => $this->alertPriorities,
            'alertTypes' => $this->alertTypes,
            'selectedAlert' => $this->getSelectedAlert(),
        ]);
    }
}
