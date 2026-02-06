<?php

namespace Tests\Feature\Phase7;

use Tests\TestCase;
use App\Models\User;
use App\Models\Team;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Team $team;

    protected function setUp(): void
    {
        parent::setUp();
        $this->team = Team::factory()->create();
        $this->user = User::factory()->create();
        $this->user->teams()->attach($this->team, ['role' => 'owner']);
        $this->user->update(['current_team_id' => $this->team->id]);
    }

    /**
     * Test notification index endpoint
     */
    public function test_can_list_notifications()
    {
        Notification::factory(5)->for($this->team)->create();
        
        $response = $this->actingAs($this->user)
            ->getJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'type', 'title', 'message', 'alert_level', 'created_at']
                ],
                'meta'
            ]);
    }

    /**
     * Test notifications are team-scoped
     */
    public function test_notifications_are_team_scoped()
    {
        $otherTeam = Team::factory()->create();
        Notification::factory(3)->for($this->team)->create();
        Notification::factory(5)->for($otherTeam)->create();
        
        $response = $this->actingAs($this->user)
            ->getJson('/api/notifications');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test can get unread notifications
     */
    public function test_can_get_unread_notifications()
    {
        $unread = Notification::factory(3)->for($this->team)->create();
        $read = Notification::factory(2)->for($this->team)->create(['is_read' => true]);
        
        $response = $this->actingAs($this->user)
            ->getJson('/api/notifications/unread');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test can mark single notification as read
     */
    public function test_can_mark_notification_as_read()
    {
        $notification = Notification::factory()->for($this->team)->create(['is_read' => false]);
        
        $response = $this->actingAs($this->user)
            ->putJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200);
        $this->assertTrue($notification->fresh()->is_read);
    }

    /**
     * Test can batch mark notifications as read
     */
    public function test_can_batch_mark_notifications_as_read()
    {
        $notifications = Notification::factory(3)->for($this->team)->create(['is_read' => false]);
        $ids = $notifications->pluck('id')->toArray();
        
        $response = $this->actingAs($this->user)
            ->putJson('/api/notifications/batch-read', ['notification_ids' => $ids]);

        $response->assertStatus(200);
        foreach ($notifications as $notification) {
            $this->assertTrue($notification->fresh()->is_read);
        }
    }

    /**
     * Test can get notification statistics
     */
    public function test_can_get_notification_statistics()
    {
        Notification::factory(5)->for($this->team)->create(['alert_level' => 'critical']);
        Notification::factory(3)->for($this->team)->create(['alert_level' => 'warning']);
        Notification::factory(2)->for($this->team)->create(['alert_level' => 'info']);
        
        $response = $this->actingAs($this->user)
            ->getJson('/api/notifications/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_notifications',
                'unread_count',
                'by_alert_level',
                'by_type',
                'by_time_period'
            ]);
    }

    /**
     * Test can clear old notifications
     */
    public function test_can_clear_old_notifications()
    {
        Notification::factory(5)->for($this->team)->create(['created_at' => now()->subMonths(3)]);
        Notification::factory(3)->for($this->team)->create(['created_at' => now()]);
        
        $response = $this->actingAs($this->user)
            ->deleteJson('/api/notifications', ['days_old' => 30]);

        $response->assertStatus(200);
        $this->assertCount(3, Notification::where('team_id', $this->team->id)->get());
    }

    /**
     * Test notifications can be filtered by alert level
     */
    public function test_can_filter_notifications_by_alert_level()
    {
        Notification::factory(5)->for($this->team)->create(['alert_level' => 'critical']);
        Notification::factory(3)->for($this->team)->create(['alert_level' => 'warning']);
        
        $response = $this->actingAs($this->user)
            ->getJson('/api/notifications?alert_level=critical');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
    }

    /**
     * Test unauthorized user cannot access notifications
     */
    public function test_unauthorized_user_cannot_access_notifications()
    {
        $response = $this->getJson('/api/notifications');

        $response->assertStatus(401);
    }
}
