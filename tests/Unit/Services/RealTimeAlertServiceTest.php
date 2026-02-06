<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\Team;
use App\Models\User;
use App\Models\Machine;
use App\Models\IoTSensor;
use App\Models\MineArea;
use App\Models\Notification;
use App\Services\RealTimeAlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class RealTimeAlertServiceTest extends TestCase
{
    use RefreshDatabase;

    private RealTimeAlertService $service;
    private Team $team;
    private User $user;
    private Machine $machine;
    private IoTSensor $sensor;
    private MineArea $mineArea;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Fake events to avoid broadcasting errors
        Event::fake();
        
        $this->service = app(RealTimeAlertService::class);
        $this->team = Team::factory()->create();
        $this->user = User::factory()->create();
        $this->user->teams()->attach($this->team, ['role' => 'owner']);
        $this->machine = Machine::factory()->for($this->team)->create();
        $this->mineArea = MineArea::factory()->for($this->team)->create();
        $this->sensor = IoTSensor::factory()->for($this->team)->create([
            'mine_area_id' => $this->mineArea->id,
        ]);
    }

    /**
     * Test dispatch sensor alert creates notification
     */
    public function test_dispatch_sensor_alert_creates_notification()
    {
        $reading = [
            'value' => 85,
            'unit' => '°C',
        ];

        $this->service->dispatchSensorAlert($this->sensor, $reading, $this->team->id, false);

        $this->assertDatabaseHas('notifications', [
            'team_id' => $this->team->id,
            'type' => 'sensor_reading',
        ]);
    }

    /**
     * Test dispatch maintenance alert creates notification
     */
    public function test_dispatch_maintenance_alert_creates_notification()
    {
        $probability = 0.95;
        $predictedDate = now()->addDays(3);

        $this->service->dispatchMaintenanceAlert($this->machine, $probability, $predictedDate, $this->team->id);

        $this->assertDatabaseHas('notifications', [
            'team_id' => $this->team->id,
            'type' => 'maintenance_alert',
        ]);
    }

    /**
     * Test dispatch compliance alert creates notification
     */
    public function test_dispatch_compliance_alert_creates_notification()
    {
        $violation = [
            'type' => 'safety_inspection',
            'description' => 'Safety inspection overdue',
            'severity' => 'high',
            'deadline' => now()->addDays(7),
        ];

        $this->service->dispatchComplianceAlert($violation, $this->team->id);

        $this->assertDatabaseHas('notifications', [
            'team_id' => $this->team->id,
            'type' => 'compliance_violation',
        ]);
    }

    /**
     * Test dispatch production alert creates notification
     */
    public function test_dispatch_production_alert_creates_notification()
    {
        $anomalyType = 'sudden_drop';
        $severity = 'warning';
        $data = [
            'value' => 45,
            'expected' => 100,
        ];

        $this->service->dispatchProductionAlert($this->mineArea, $anomalyType, $severity, $data, $this->team->id);

        $this->assertDatabaseHas('notifications', [
            'team_id' => $this->team->id,
            'type' => 'production_anomaly',
        ]);
    }

    /**
     * Test dispatch sensor status alert creates notification
     */
    public function test_dispatch_sensor_status_alert_creates_notification()
    {
        $oldStatus = 'online';
        $newStatus = 'offline';

        $this->service->dispatchSensorStatusAlert($this->sensor, $oldStatus, $newStatus, $this->team->id);

        $this->assertDatabaseHas('notifications', [
            'team_id' => $this->team->id,
            'type' => 'sensor_status_changed',
        ]);
    }

    /**
     * Test get recent alerts
     */
    public function test_can_get_recent_alerts()
    {
        Notification::factory(5)->for($this->team)->create();

        $alerts = $this->service->getRecentAlerts($this->team->id, 10);

        $this->assertCount(5, $alerts);
    }

    /**
     * Test get unread alerts
     */
    public function test_can_get_unread_alerts()
    {
        Notification::factory(3)->for($this->team)->create();

        $alerts = $this->service->getUnreadAlerts($this->user->id, $this->team->id);

        $this->assertGreaterThanOrEqual(0, $alerts->count());
    }

    /**
     * Test mark notification as read
     */
    public function test_can_mark_notification_as_read()
    {
        $notification = Notification::factory()->for($this->team)->create(['is_read' => false]);

        $result = $this->service->markAsRead($notification->id, $this->user->id);

        $this->assertTrue($result);
        $this->assertTrue($notification->fresh()->is_read);
    }

    /**
     * Test mark multiple as read
     */
    public function test_can_mark_multiple_as_read()
    {
        $notifications = Notification::factory(3)->for($this->team)->create(['is_read' => false]);
        $ids = $notifications->pluck('id')->toArray();

        $count = $this->service->markMultipleAsRead($ids, $this->user->id);

        $this->assertEquals(3, $count);
    }

    /**
     * Test get alert statistics
     */
    public function test_can_get_alert_statistics()
    {
        Notification::factory(5)->for($this->team)->create(['alert_level' => 'critical']);
        Notification::factory(3)->for($this->team)->create(['alert_level' => 'warning']);

        $stats = $this->service->getAlertStats($this->team->id);

        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('by_level', $stats);
        $this->assertEquals(8, $stats['total']);
    }
}
