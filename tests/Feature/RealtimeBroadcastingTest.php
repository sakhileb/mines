<?php

namespace Tests\Feature;

use App\Events\AlertTriggered;
use App\Events\GeofenceEntryDetected;
use App\Events\GeofenceExitDetected;
use App\Events\MachineLocationUpdated;
use App\Events\MachineOffline;
use App\Jobs\AlertGenerationJob;
use App\Jobs\GeofenceCrossingDetectionJob;
use App\Jobs\MachineLocationUpdateJob;
use App\Jobs\MachineStatusMonitoringJob;
use App\Models\Alert;
use App\Models\Geofence;
use App\Models\GeofenceEntry;
use App\Models\Integration;
use App\Models\Machine;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RealtimeBroadcastingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Team $team;
    protected Integration $integration;
    protected Machine $machine;

    protected function setUp(): void
    {
        parent::setUp();

        // Fake events to avoid broadcasting errors
        Event::fake();

        // Create test user and team
        $this->user = User::factory()->create();
        $this->team = Team::factory()->create(['user_id' => $this->user->id]);

        // Create test integration
        $this->integration = Integration::factory()->create([
            'team_id' => $this->team->id,
            'status' => 'connected',
        ]);

        // Create test machine
        $this->machine = Machine::factory()->create([
            'team_id' => $this->team->id,
            'integration_id' => $this->integration->id,
            'status' => 'active',
            'last_location_latitude' => -33.8688,
            'last_location_longitude' => 151.2093,
            'last_location_update' => now(),
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function machine_location_update_broadcasts_event()
    {
        Event::fake();
        Queue::fake();

        // Dispatch the job
        $job = new MachineLocationUpdateJob($this->integration);

        // Mock the integration service
        $this->mock('App\Services\Integration\IntegrationService', function ($mock) {
            $mock->shouldReceive('getMachineLocations')
                ->andReturn([
                    [
                        'manufacturer_id' => $this->machine->manufacturer_id,
                        'latitude' => -33.8700,
                        'longitude' => 151.2100,
                        'status' => 'active',
                    ],
                ]);
        });

        // Execute job
        $job->handle(resolve('App\Services\Integration\IntegrationService'));

        // Assert location update event was broadcast
        Event::assertDispatched(MachineLocationUpdated::class, function ($event) {
            return $event->machine->id === $this->machine->id &&
                   $event->location['latitude'] === -33.8700;
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function machine_offline_status_broadcasts_event()
    {
        Event::fake();
        Queue::fake();

        // Update machine to have stale location
        $this->machine->update([
            'last_location_update' => now()->subMinutes(6),
        ]);

        // Dispatch the job
        $job = new MachineStatusMonitoringJob($this->integration);

        // Mock the integration service
        $this->mock('App\Services\Integration\IntegrationService', function ($mock) {
            $mock->shouldReceive('getMachineStatuses')
                ->andReturn([
                    [
                        'manufacturer_id' => $this->machine->manufacturer_id,
                        'status' => 'online',
                    ],
                ]);
        });

        // Execute job
        $job->handle(resolve('App\Services\Integration\IntegrationService'));

        // Assert machine was marked offline
        $this->machine->refresh();
        $this->assertEquals('offline', $this->machine->status);

        // Assert offline event was broadcast
        Event::assertDispatched(MachineOffline::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function alert_generation_broadcasts_events()
    {
        Event::fake();

        // Update machine with fuel capacity
        $this->machine->update(['fuel_capacity' => 100]);

        // Create a metric for the machine with low fuel
        $this->machine->metrics()->create([
            'team_id' => $this->team->id,
            'fuel_level' => 5,
            'engine_temperature' => 85,
        ]);

        // Dispatch the job
        $job = new AlertGenerationJob($this->team);
        $job->handle();

        // Assert alert was created
        $this->assertDatabaseHas('alerts', [
            'machine_id' => $this->machine->id,
            'type' => 'fuel',
            'status' => 'active',
        ]);

        // Assert alert event was broadcast
        Event::assertDispatched(AlertTriggered::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function geofence_entry_broadcasts_event()
    {
        Event::fake();

        // Create a geofence
        $geofence = Geofence::factory()->create([
            'team_id' => $this->team->id,
            'status' => 'active',
            'coordinates' => [
                [-33.8680, 151.2080],
                [-33.8690, 151.2080],
                [-33.8690, 151.2100],
                [-33.8680, 151.2100],
            ],
        ]);

        // Update machine location to inside geofence and ensure it's active
        $this->machine->update([
            'status' => 'active',
            'last_location_latitude' => -33.8685,
            'last_location_longitude' => 151.2090,
        ]);

        // Dispatch the job
        $job = new GeofenceCrossingDetectionJob($this->team);
        $job->handle();

        // Assert geofence entry was recorded
        $this->assertDatabaseHas('geofence_entries', [
            'geofence_id' => $geofence->id,
            'machine_id' => $this->machine->id,
        ]);

        // Assert entry event was broadcast
        Event::assertDispatched(GeofenceEntryDetected::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function geofence_exit_broadcasts_event()
    {
        Event::fake();

        // Create a geofence
        $geofence = Geofence::factory()->create([
            'team_id' => $this->team->id,
            'status' => 'active',
            'coordinates' => [
                [-33.8680, 151.2080],
                [-33.8690, 151.2080],
                [-33.8690, 151.2100],
                [-33.8680, 151.2100],
            ],
        ]);

        // Create an active entry record
        $entry = GeofenceEntry::create([
            'team_id' => $this->team->id,
            'geofence_id' => $geofence->id,
            'machine_id' => $this->machine->id,
            'entry_time' => now()->subMinutes(5),
            'entry_latitude' => -33.8685,
            'entry_longitude' => 151.2090,
            'exit_time' => null,
        ]);

        // Update machine location to outside geofence and ensure it's active
        $this->machine->update([
            'status' => 'active',
            'last_location_latitude' => -33.8670,
            'last_location_longitude' => 151.2070,
        ]);

        // Dispatch the job
        $job = new GeofenceCrossingDetectionJob($this->team);
        $job->handle();

        // Assert exit event was broadcast
        Event::assertDispatched(GeofenceExitDetected::class);

        // Assert entry was marked with exit time
        $entry->refresh();
        $this->assertNotNull($entry->exit_time);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function all_jobs_can_be_queued_together()
    {
        Queue::fake();

        // Queue all jobs
        MachineLocationUpdateJob::dispatch($this->integration);
        AlertGenerationJob::dispatch($this->team);
        GeofenceCrossingDetectionJob::dispatch($this->team);
        MachineStatusMonitoringJob::dispatch($this->integration);

        // Assert all jobs were queued
        Queue::assertPushed(MachineLocationUpdateJob::class);
        Queue::assertPushed(AlertGenerationJob::class);
        Queue::assertPushed(GeofenceCrossingDetectionJob::class);
        Queue::assertPushed(MachineStatusMonitoringJob::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function jobs_dispatch_to_correct_queues()
    {
        Queue::fake();

        // Queue all jobs
        MachineLocationUpdateJob::dispatch($this->integration);
        AlertGenerationJob::dispatch($this->team);
        GeofenceCrossingDetectionJob::dispatch($this->team);
        MachineStatusMonitoringJob::dispatch($this->integration);

        // Assert jobs are on correct queues
        Queue::assertPushedOn('locations', MachineLocationUpdateJob::class);
        Queue::assertPushedOn('alerts', AlertGenerationJob::class);
        Queue::assertPushedOn('geofences', GeofenceCrossingDetectionJob::class);
        Queue::assertPushedOn('status', MachineStatusMonitoringJob::class);
    }
}
