<?php

namespace Tests\Feature\Phase7;

use Tests\TestCase;
use App\Models\Team;
use App\Models\User;
use App\Models\Machine;
use App\Models\MineArea;
use App\Models\IoTSensor;
use App\Models\Notification;
use App\Models\ComplianceViolation;
use App\Services\IoTSensorService;
use App\Services\RealTimeAlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class AlertIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private IoTSensorService $iotService;
    private RealTimeAlertService $alertService;
    private Team $team;
    private User $user;
    private Machine $machine;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Fake events to avoid broadcasting errors
        Event::fake();
        
        $this->iotService = app(IoTSensorService::class);
        $this->alertService = app(RealTimeAlertService::class);
        $this->team = Team::factory()->create();
        $this->user = User::factory()->create();
        $this->user->teams()->attach($this->team, ['role' => 'owner']);
        $this->machine = Machine::factory()->for($this->team)->create();
    }

    /**
     * Test complete sensor alert flow
     */
    public function test_complete_sensor_alert_flow()
    {
        // Create sensor first
        $sensor = IoTSensor::factory()->for($this->team)->create([
            'name' => 'Temperature Sensor',
            'sensor_type' => 'temperature',
            'device_id' => 'TEMP-001',
        ]);

        // Dispatch sensor alert
        $this->alertService->dispatchSensorAlert($sensor, [
            'value' => 95,
            'unit' => '°C',
        ], $this->team->id, true);

        // Verify notification created
        $this->assertDatabaseHas('notifications', [
            'team_id' => $this->team->id,
            'type' => 'sensor_reading',
        ]);

        // Verify alert level is appropriate for anomaly
        $notification = Notification::where('team_id', $this->team->id)->first();
        $this->assertNotNull($notification);
        $this->assertEquals('warning', $notification->alert_level);
    }

    /**
     * Test maintenance alert triggers for high probability
     */
    public function test_maintenance_alert_triggers_for_high_probability()
    {
        $this->alertService->dispatchMaintenanceAlert(
            $this->machine,
            0.92,
            now()->addDays(5),
            $this->team->id
        );

        $notification = Notification::where('team_id', $this->team->id)
            ->where('type', 'maintenance_alert')
            ->first();

        $this->assertNotNull($notification);
        $this->assertEquals('critical', $notification->alert_level);
    }

    /**
     * Test compliance alert escalation
     */
    public function test_compliance_alert_escalation()
    {
        // Create compliance violation model
        $violation = ComplianceViolation::create([
            'team_id' => $this->team->id,
            'violation_type' => 'Monthly safety inspection overdue',
            'description' => 'The monthly safety inspection has not been completed.',
            'severity' => 'critical',
            'remediation_deadline' => now()->addDays(7),
            'detected_at' => now(),
        ]);

        // Dispatch compliance alert
        $this->alertService->dispatchComplianceAlert($violation, $this->team->id);

        $notification = Notification::where('team_id', $this->team->id)
            ->where('type', 'compliance_violation')
            ->first();

        $this->assertNotNull($notification);
        $this->assertEquals('critical', $notification->alert_level);
    }

    /**
     * Test production anomaly triggers when drop is significant
     */
    public function test_production_anomaly_triggers_for_significant_drop()
    {
        $mineArea = MineArea::factory()->for($this->team)->create();

        $this->alertService->dispatchProductionAlert(
            $mineArea,
            'sudden_drop',
            'warning',
            [
                'current_production' => 35,
                'expected_production' => 100,
                'deviation_percent' => 65,
            ],
            $this->team->id
        );

        $notification = Notification::where('team_id', $this->team->id)
            ->where('type', 'production_anomaly')
            ->first();

        $this->assertNotNull($notification);
    }

    /**
     * Test sensor offline status creates urgent alert
     */
    public function test_sensor_offline_creates_urgent_alert()
    {
        $sensor = IoTSensor::factory()->for($this->team)->create([
            'name' => 'Main GPS Tracker',
            'sensor_type' => 'custom',
            'device_id' => 'GPS-001',
            'status' => 'active',
        ]);

        $this->alertService->dispatchSensorStatusAlert(
            $sensor,
            'active',
            'inactive',
            $this->team->id
        );

        $notification = Notification::where('team_id', $this->team->id)
            ->where('type', 'sensor_status_changed')
            ->first();

        $this->assertNotNull($notification);
        $this->assertEquals('warning', $notification->alert_level);
    }

    /**
     * Test alert history retrieval
     */
    public function test_can_retrieve_alert_history()
    {
        // Create multiple alerts
        for ($i = 0; $i < 5; $i++) {
            $sensor = IoTSensor::factory()->for($this->team)->create([
                'sensor_type' => 'temperature',
                'device_id' => 'SENSOR-' . $i,
            ]);
            $this->alertService->dispatchSensorAlert($sensor, [
                'value' => 50 + $i,
                'unit' => 'psi',
            ], $this->team->id, $i % 2 == 0);
        }

        $alerts = $this->alertService->getRecentAlerts($this->team->id, 10);

        $this->assertCount(5, $alerts);
    }

    /**
     * Test team isolation in alerts
     */
    public function test_alerts_are_team_isolated()
    {
        $otherTeam = Team::factory()->create();
        $otherMachine = Machine::factory()->for($otherTeam)->create();

        // Create alerts for both teams
        $sensor1 = IoTSensor::factory()->for($this->team)->create([
            'sensor_type' => 'pressure',
            'device_id' => 'SENSOR-TEAM1',
        ]);
        $sensor2 = IoTSensor::factory()->for($otherTeam)->create([
            'sensor_type' => 'pressure',
            'device_id' => 'SENSOR-TEAM2',
        ]);

        $this->alertService->dispatchSensorAlert($sensor1, [
            'value' => 50,
            'unit' => 'bar',
        ], $this->team->id);

        $this->alertService->dispatchSensorAlert($sensor2, [
            'value' => 60,
            'unit' => 'bar',
        ], $otherTeam->id);

        $teamAlerts = $this->alertService->getRecentAlerts($this->team->id, 10);
        $otherAlerts = $this->alertService->getRecentAlerts($otherTeam->id, 10);

        $this->assertCount(1, $teamAlerts);
        $this->assertCount(1, $otherAlerts);
    }

    /**
     * Test alert statistics aggregation
     */
    public function test_alert_statistics_aggregation()
    {
        // Create alerts of different types
        $sensor = IoTSensor::factory()->for($this->team)->create([
            'sensor_type' => 'temperature',
            'device_id' => 'SENSOR-STATS',
        ]);
        $this->alertService->dispatchSensorAlert($sensor, [
            'value' => 50,
            'unit' => 'bar',
        ], $this->team->id);

        $this->alertService->dispatchMaintenanceAlert(
            $this->machine,
            0.8,
            now()->addDays(3),
            $this->team->id
        );

        $violation = ComplianceViolation::create([
            'team_id' => $this->team->id,
            'violation_type' => 'Inspection due',
            'description' => 'Routine inspection required',
            'severity' => 'medium',
            'remediation_deadline' => now()->addDays(7),
            'detected_at' => now(),
        ]);
        $this->alertService->dispatchComplianceAlert($violation, $this->team->id);

        $stats = $this->alertService->getAlertStats($this->team->id);

        $this->assertEquals(3, $stats['total']);
        $this->assertGreaterThan(0, count($stats['by_type']));
    }
}
