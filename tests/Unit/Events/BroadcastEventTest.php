<?php

namespace Tests\Unit\Events;

use Tests\TestCase;
use App\Events\SensorReadingRecorded;
use App\Events\MaintenanceAlertTriggered;
use App\Events\ComplianceViolationDetected;
use App\Events\ProductionAnomalyDetected;
use App\Events\SensorStatusChanged;
use App\Models\IoTSensor;
use App\Models\Machine;
use App\Models\Team;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BroadcastEventTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test SensorReadingRecorded event broadcasts to correct channel
     */
    public function test_sensor_reading_recorded_broadcasts_to_team_channel()
    {
        // Create a real IoTSensor
        $team = Team::factory()->create();
        $sensor = IoTSensor::factory()->create(['team_id' => $team->id]);

        $event = new SensorReadingRecorded($sensor, [
            'sensor_id' => $sensor->id,
            'name' => 'Temperature',
            'value' => 85,
            'unit' => '°C',
            'timestamp' => now(),
            'anomaly' => false,
        ], $team->id);

        $channel = $event->broadcastOn();
        $this->assertInstanceOf(PrivateChannel::class, $channel);
        $this->assertEquals("private-team.{$team->id}.sensors", $channel->name);
    }

    /**
     * Test MaintenanceAlertTriggered broadcasts to correct channel
     */
    public function test_maintenance_alert_triggered_broadcasts_to_team_channel()
    {
        $team = Team::factory()->create();
        $machine = Machine::factory()->create(['team_id' => $team->id]);

        $event = new MaintenanceAlertTriggered($machine, 0.95, now()->addDays(3), $team->id);

        $channel = $event->broadcastOn();
        $this->assertInstanceOf(PrivateChannel::class, $channel);
        $this->assertEquals("private-team.{$team->id}.alerts", $channel->name);
    }

    /**
     * Test ComplianceViolationDetected broadcasts to correct channel
     */
    public function test_compliance_violation_broadcasts_to_team_channel()
    {
        $team = Team::factory()->create();
        
        // Create mock violation object
        $violation = (object)[
            'id' => 1,
            'violation_type' => 'Safety inspection overdue',
            'severity' => 'high',
        ];
        
        $event = new ComplianceViolationDetected($violation, $team->id);

        $channel = $event->broadcastOn();
        $this->assertInstanceOf(PrivateChannel::class, $channel);
        $this->assertEquals("private-team.{$team->id}.compliance", $channel->name);
    }

    /**
     * Test ProductionAnomalyDetected broadcasts to correct channel
     */
    public function test_production_anomaly_broadcasts_to_team_channel()
    {
        $team = Team::factory()->create();
        
        // Create mock mine area object
        $mineArea = (object)[
            'id' => 1,
            'name' => 'North Pit',
        ];
        
        $event = new ProductionAnomalyDetected(
            $mineArea,
            'sudden_drop',
            'warning',
            ['value' => 45],
            $team->id
        );

        $channel = $event->broadcastOn();
        $this->assertInstanceOf(PrivateChannel::class, $channel);
        $this->assertEquals("private-team.{$team->id}.operations", $channel->name);
    }

    /**
     * Test SensorStatusChanged broadcasts to correct channel
     */
    public function test_sensor_status_changed_broadcasts_to_team_channel()
    {
        $team = Team::factory()->create();
        $sensor = IoTSensor::factory()->create(['team_id' => $team->id]);

        $event = new SensorStatusChanged($sensor, 'online', 'offline', $team->id);

        $channel = $event->broadcastOn();
        $this->assertInstanceOf(PrivateChannel::class, $channel);
        $this->assertEquals("private-team.{$team->id}.alerts", $channel->name);
    }

    /**
     * Test SensorReadingRecorded broadcasts data
     */
    public function test_sensor_reading_recorded_broadcasts_data()
    {
        $team = Team::factory()->create();
        $sensor = IoTSensor::factory()->create(['team_id' => $team->id]);

        $data = [
            'sensor_id' => $sensor->id,
            'name' => 'Temperature',
            'value' => 85,
            'unit' => '°C',
            'anomaly' => false,
        ];

        $event = new SensorReadingRecorded($sensor, $data, $team->id);
        $broadcasted = $event->broadcastWith();

        $this->assertArrayHasKey('value', $broadcasted);
        $this->assertEquals(85, $broadcasted['value']);
    }

    /**
     * Test MaintenanceAlertTriggered calculates severity
     */
    public function test_maintenance_alert_triggered_calculates_severity()
    {
        $team = Team::factory()->create();
        $machine = Machine::factory()->create(['team_id' => $team->id]);

        $event = new MaintenanceAlertTriggered($machine, 0.95, now()->addDays(2), $team->id);
        $broadcasted = $event->broadcastWith();

        $this->assertArrayHasKey('severity', $broadcasted);
    }

    /**
     * Test all events have required broadcast structure
     */
    public function test_all_events_have_event_name()
    {
        $team = Team::factory()->create();
        $sensor = IoTSensor::factory()->create(['team_id' => $team->id]);
        $machine = Machine::factory()->create(['team_id' => $team->id]);
        
        // Create mock objects
        $violation = (object)['id' => 1, 'violation_type' => 'test', 'severity' => 'high'];
        $mineArea = (object)['id' => 1, 'name' => 'Test Area'];

        $events = [
            new SensorReadingRecorded($sensor, ['value' => 100, 'unit' => 'C', 'is_anomaly' => false], $team->id),
            new MaintenanceAlertTriggered($machine, 0.8, now(), $team->id),
            new ComplianceViolationDetected($violation, $team->id),
            new ProductionAnomalyDetected($mineArea, 'drop', 'warning', [], $team->id),
            new SensorStatusChanged($sensor, 'online', 'offline', $team->id),
        ];

        foreach ($events as $event) {
            $this->assertNotEmpty($event->broadcastAs());
        }
    }
}
