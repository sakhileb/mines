<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Integration;
use App\Services\Integration\IntegrationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class IntegrationManager extends Component
{
    public $team;
    public $integrations = [];
    public $availableManufacturers = [];
    public $showAddModal = false;
    public $showTestModal = false;
    public $selectedIntegration = null;
    public $testResult = null;
    public $formData = [
        'provider' => '',
        'name' => '',
        'endpoint' => '',
        'connection_type' => '',
        'sync_frequency' => 'manual',
        'notification_email' => '',
        'credentials' => [
            'api_key' => '',
            'api_secret' => '',
        ],
    ];

    protected $listeners = ['refresh' => '$refresh'];

    public function mount()
    {
        $this->team = Auth::user()->currentTeam;
        
        if (!$this->team) {
            abort(403, 'No team context available.');
        }
        
        $this->loadIntegrations();
        $this->loadAvailableManufacturers();
    }

    public function loadIntegrations()
    {
        if (!$this->team) {
            return;
        }
        
        $this->integrations = Integration::where('team_id', $this->team->id)
            ->get()
            ->map(function ($integration) {
                return [
                    'id' => $integration->id,
                    'provider' => $integration->provider,
                    'status' => $integration->status,
                    'created_at' => $integration->created_at->format('M d, Y'),
                    'last_sync_at' => $integration->last_sync_at?->format('M d, Y H:i') ?? 'Never',
                    'last_sync_status' => $integration->last_sync_status ?? 'pending',
                ];
            })
            ->toArray();
    }

    public function loadAvailableManufacturers()
    {
        $service = app(IntegrationService::class);
        $this->availableManufacturers = $service->getAvailableManufacturers();
    }

    public function openAddModal()
    {
        $this->showAddModal = true;
        $this->resetForm();
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->formData = [
            'provider' => '',
            'name' => '',
            'endpoint' => '',
            'connection_type' => '',
            'sync_frequency' => 'manual',
            'notification_email' => '',
            'credentials' => [
                'api_key' => '',
                'api_secret' => '',
            ],
        ];
    }

    public function createIntegration()
    {
        if (!$this->team) {
            $this->addError('general', 'No team context available');
            return;
        }

        $this->validate([
            'formData.provider' => 'required|string',
            'formData.name' => 'required|string|max:100',
            'formData.connection_type' => 'required|string',
            'formData.sync_frequency' => 'required|string',
            'formData.credentials.api_key' => 'required|string',
            'formData.credentials.api_secret' => 'required|string',
            'formData.notification_email' => 'nullable|email',
            'formData.endpoint' => 'nullable|string',
        ]);

        try {
            $integration = Integration::create([
                'team_id' => $this->team->id,
                'provider' => $this->formData['provider'],
                'name' => $this->formData['name'],
                'credentials' => json_encode($this->formData['credentials']),
                'status' => 'pending',
                'webhook_url' => $this->formData['connection_type'] === 'webhook' ? route('webhook.receive', ['provider' => $this->formData['provider']]) : null,
                'config' => json_encode([
                    'endpoint' => $this->formData['endpoint'],
                    'connection_type' => $this->formData['connection_type'],
                    'sync_frequency' => $this->formData['sync_frequency'],
                    'notification_email' => $this->formData['notification_email'],
                ]),
            ]);

            $this->dispatch('notify', message: "Integration created successfully!");
            $this->closeAddModal();
            $this->loadIntegrations();
        } catch (\Exception $e) {
            Log::error('Failed to create integration', ['error' => $e->getMessage()]);
            $this->addError('general', 'Failed to create integration. Please try again.');
        }
    }

    public function testConnection($integrationId)
    {
        if (!$this->team) {
            $this->testResult = [
                'success' => false,
                'message' => 'No team context available',
            ];
            $this->showTestModal = true;
            return;
        }
        
        try {
            $integration = Integration::where('team_id', $this->team->id)
                ->findOrFail($integrationId);

            $service = app(IntegrationService::class);
            $result = $service->testConnection($integration);

            if ($result['success']) {
                $integration->update(['status' => 'connected']);
                $this->testResult = [
                    'success' => true,
                    'message' => 'Connection successful!',
                ];
            } else {
                $this->testResult = [
                    'success' => false,
                    'message' => $result['error'] ?? 'Connection failed',
                ];
            }

            $this->selectedIntegration = $integrationId;
            $this->showTestModal = true;
            $this->loadIntegrations();
        } catch (\Exception $e) {
            Log::error('Test connection failed', ['error' => $e->getMessage()]);
            $this->testResult = [
                'success' => false,
                'message' => 'Error testing connection',
            ];
            $this->showTestModal = true;
        }
    }

    public function syncMachines($integrationId)
    {
        if (!$this->team) {
            $this->dispatch('notify', message: "No team context available");
            return;
        }
        
        try {
            $integration = Integration::where('team_id', $this->team->id)
                ->findOrFail($integrationId);

            $service = app(IntegrationService::class);
            $result = $service->syncMachines($integration);

            if ($result['success']) {
                $integration->update([
                    'last_sync_at' => now(),
                    'last_sync_status' => 'success',
                ]);
                $this->dispatch('notify', message: "Sync started successfully!");
            } else {
                $integration->update(['last_sync_status' => 'failed']);
                $this->dispatch('notify', message: "Sync failed: " . $result['error']);
            }

            $this->loadIntegrations();
        } catch (\Exception $e) {
            Log::error('Sync machines failed', ['error' => $e->getMessage()]);
            $this->dispatch('notify', message: "Error starting sync");
        }
    }

    public function deleteIntegration($integrationId)
    {
        if (!$this->team) {
            $this->dispatch('notify', message: "No team context available");
            return;
        }
        
        try {
            Integration::where('team_id', $this->team->id)
                ->findOrFail($integrationId)
                ->delete();

            $this->dispatch('notify', message: "Integration deleted successfully!");
            $this->loadIntegrations();
        } catch (\Exception $e) {
            Log::error('Delete integration failed', ['error' => $e->getMessage()]);
            $this->dispatch('notify', message: "Error deleting integration");
        }
    }

    public function render()
    {
        return view('livewire.integration-manager');
    }
}
