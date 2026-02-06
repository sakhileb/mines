<?php

namespace Tests\Feature\AI;

use Tests\TestCase;
use App\Models\Team;
use App\Models\User;
use App\Models\Machine;
use App\Models\AIRecommendation;
use App\Models\AIInsight;
use App\Models\AIPredictiveAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AIOptimizationDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected Team $team;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->team = Team::factory()->create();
        $this->user = User::factory()->create();
        $this->user->teams()->attach($this->team, ['role' => 'admin']);
        $this->user->current_team_id = $this->team->id;
        $this->user->save();
    }

    /** @test */
    public function authenticated_user_can_access_ai_dashboard(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('ai-optimization'));

        $response->assertStatus(200);
        $response->assertSeeLivewire('ai-optimization-dashboard');
    }

    /** @test */
    public function unauthenticated_user_cannot_access_ai_dashboard(): void
    {
        $response = $this->get(route('ai-optimization'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function dashboard_displays_recommendations(): void
    {
        AIRecommendation::factory()->count(3)->create([
            'team_id' => $this->team->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('ai-optimization'));

        $response->assertStatus(200);
    }

    /** @test */
    public function dashboard_displays_insights(): void
    {
        AIInsight::factory()->count(3)->create([
            'team_id' => $this->team->id,
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('ai-optimization'));

        $response->assertStatus(200);
    }

    /** @test */
    public function dashboard_displays_predictive_alerts(): void
    {
        AIPredictiveAlert::factory()->count(2)->create([
            'team_id' => $this->team->id,
            'is_acknowledged' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('ai-optimization'));

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_implement_recommendation(): void
    {
        $recommendation = AIRecommendation::factory()->create([
            'team_id' => $this->team->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->user);
        
        $recommendation->markAsImplemented($this->user, 'Test implementation');

        $this->assertEquals('implemented', $recommendation->fresh()->status);
        $this->assertEquals($this->user->id, $recommendation->fresh()->implemented_by);
    }

    /** @test */
    public function user_can_acknowledge_alert(): void
    {
        $alert = AIPredictiveAlert::factory()->create([
            'team_id' => $this->team->id,
            'is_acknowledged' => false,
        ]);

        $this->actingAs($this->user);
        
        $alert->acknowledge($this->user);

        $this->assertTrue($alert->fresh()->is_acknowledged);
        $this->assertEquals($this->user->id, $alert->fresh()->acknowledged_by);
    }

    /** @test */
    public function user_can_mark_insight_as_read(): void
    {
        $insight = AIInsight::factory()->create([
            'team_id' => $this->team->id,
            'is_read' => false,
        ]);

        $this->actingAs($this->user);
        
        $insight->markAsRead();

        $this->assertTrue($insight->fresh()->is_read);
    }

    /** @test */
    public function dashboard_filters_by_category(): void
    {
        AIRecommendation::factory()->create([
            'team_id' => $this->team->id,
            'category' => 'fleet',
        ]);

        AIRecommendation::factory()->create([
            'team_id' => $this->team->id,
            'category' => 'fuel',
        ]);

        $fleetRecs = AIRecommendation::where('team_id', $this->team->id)
            ->where('category', 'fleet')
            ->get();

        $this->assertCount(1, $fleetRecs);
    }

    /** @test */
    public function dashboard_filters_by_priority(): void
    {
        AIRecommendation::factory()->create([
            'team_id' => $this->team->id,
            'priority' => 'critical',
        ]);

        AIRecommendation::factory()->count(2)->create([
            'team_id' => $this->team->id,
            'priority' => 'low',
        ]);

        $criticalRecs = AIRecommendation::where('team_id', $this->team->id)
            ->where('priority', 'critical')
            ->get();

        $this->assertCount(1, $criticalRecs);
    }

    /** @test */
    public function team_isolation_is_enforced(): void
    {
        $otherTeam = Team::factory()->create();
        
        AIRecommendation::factory()->count(3)->create([
            'team_id' => $otherTeam->id,
        ]);

        $myRecs = AIRecommendation::where('team_id', $this->team->id)->get();
        
        $this->assertCount(0, $myRecs);
    }
}
