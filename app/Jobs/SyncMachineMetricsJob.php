<?php

namespace App\Jobs;

use App\Models\Machine;
use App\Services\Integration\IntegrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncMachineMetricsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Machine $machine;
    public int $tries = 2;
    public int $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(Machine $machine)
    {
        $this->machine = $machine;
    }

    /**
     * Execute the job.
     */
    public function handle(IntegrationService $integrationService): void
    {
        Log::info('Starting metrics sync for machine', [
            'machine_id' => $this->machine->id,
            'machine_name' => $this->machine->name,
        ]);

        try {
            // Ensure model queries are scoped to the machine's team in queue context
            app()->instance('current_team_id', $this->machine->team_id);

            // Get the integration for this machine
            $integration = $this->machine->team->integrations()
                ->where('provider', $this->machine->manufacturer)
                ->first();

            if (!$integration || $integration->status !== 'connected') {
                Log::warning('Integration not available or not connected', [
                    'machine_id' => $this->machine->id,
                ]);
                return;
            }

            // Get service and fetch metrics
            $service = $this->getServiceForIntegration($integration);
            if (!$service) {
                return;
            }

            $metrics = $service->fetchMachineMetrics($this->machine->external_id);

            if (!empty($metrics)) {
                $this->machine->metrics()->create($metrics);
                
                Log::info('Machine metrics synced successfully', [
                    'machine_id' => $this->machine->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception during metrics sync', [
                'machine_id' => $this->machine->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        } finally {
            if (app()->hasInstance('current_team_id')) {
                app()->forgetInstance('current_team_id');
            }
        }
    }

    /**
     * Get service instance for integration
     */
    private function getServiceForIntegration($integration)
    {
        $credentials = json_decode($integration->credentials, true) ?? [];
        
        return match ($integration->provider) {
            'volvo' => app(\App\Services\Integration\VolvoService::class, ['credentials' => $credentials]),
            'cat' => app(\App\Services\Integration\CATService::class, ['credentials' => $credentials]),
            'komatsu' => app(\App\Services\Integration\KomatsuService::class, ['credentials' => $credentials]),
            'bell' => app(\App\Services\Integration\BellService::class, ['credentials' => $credentials]),
            'ctrack' => app(\App\Services\Integration\CTrackService::class, ['credentials' => $credentials]),
            default => null,
        };
    }
}
