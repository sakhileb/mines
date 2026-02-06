<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\Team;
use App\Models\User;
use App\Models\Machine;
use App\Models\AIAgent;
use App\Models\AIRecommendation;
use App\Models\AIInsight;
use App\Models\AIAnalysisSession;
use App\Services\AI\AIOptimizationService;
use App\Services\AI\FleetOptimizerAgent;
use App\Services\AI\RouteAdvisorAgent;
use App\Services\AI\FuelPredictorAgent;
use App\Services\AI\MaintenancePredictorAgent;
use App\Services\AI\ProductionOptimizerAgent;
use App\Services\AI\CostAnalyzerAgent;
use App\Services\AI\AnomalyDetectorAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AIOptimizationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AIOptimizationService $service;
    protected Team $team;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->team = Team::factory()->create();
        $this->user = User::factory()->create();
        $this->user->teams()->attach($this->team);
        
        // Create AI service with all agents
        $this->service = new AIOptimizationService(
            app(FleetOptimizerAgent::class),
            app(RouteAdvisorAgent::class),
            app(FuelPredictorAgent::class),
            app(MaintenancePredictorAgent::class),
            app(ProductionOptimizerAgent::class),
            app(CostAnalyzerAgent::class),
            app(AnomalyDetectorAgent::class)
        );
    }

    /** @test */
    public function it_creates_ai_agents_if_not_exists(): void
    {
        $this->assertEquals(0, AIAgent::count());
        
        $result = $this->service->runComprehensiveAnalysis($this->team, $this->user);
        
        // Should have created 7 agents
        $this->assertEquals(7, AIAgent::count());
    }

    /** @test */
    public function it_generates_recommendations(): void
    {
        // Create some machines for analysis
        Machine::factory()->count(5)->create([
            'team_id' => $this->team->id,
            'status' => 'active',
        ]);

        $result = $this->service->runComprehensiveAnalysis($this->team, $this->user);

        $this->assertNotNull($result);
        $this->assertArrayHasKey('recommendations', $result);
        $this->assertArrayHasKey('insights', $result);
        $this->assertArrayHasKey('summary', $result);
    }

    /** @test */
    public function it_creates_analysis_sessions(): void
    {
        Machine::factory()->count(3)->create(['team_id' => $this->team->id]);

        $this->assertEquals(0, AIAnalysisSession::count());
        
        $result = $this->service->runComprehensiveAnalysis($this->team, $this->user);
        
        // Should create sessions for active agents
        $this->assertGreaterThan(0, AIAnalysisSession::count());
    }

    /** @test */
    public function it_generates_summary_statistics(): void
    {
        Machine::factory()->count(3)->create(['team_id' => $this->team->id]);

        $result = $this->service->runComprehensiveAnalysis($this->team, $this->user);
        
        $summary = $result['summary'];
        
        $this->assertArrayHasKey('total_recommendations', $summary);
        $this->assertArrayHasKey('critical_recommendations', $summary);
        $this->assertArrayHasKey('high_priority_recommendations', $summary);
        $this->assertArrayHasKey('total_insights', $summary);
    }

    /** @test */
    public function it_respects_agent_status(): void
    {
        // Create agent and set it to inactive
        $agent = AIAgent::factory()->create([
            'type' => AIAgent::TYPE_FLEET_OPTIMIZER,
            'status' => 'inactive',
        ]);

        $result = $this->service->runComprehensiveAnalysis($this->team, $this->user);
        
        // Should skip inactive agents
        $sessions = AIAnalysisSession::where('ai_agent_id', $agent->id)->get();
        $this->assertEquals(0, $sessions->count());
    }

    /** @test */
    public function it_gets_dashboard_insights(): void
    {
        // Create some test data
        AIInsight::factory()->count(5)->create([
            'team_id' => $this->team->id,
            'is_read' => false,
        ]);

        AIRecommendation::factory()->count(3)->create([
            'team_id' => $this->team->id,
            'status' => 'pending',
            'priority' => 'high',
        ]);

        $dashboard = $this->service->getDashboardInsights($this->team);

        $this->assertArrayHasKey('insights', $dashboard);
        $this->assertArrayHasKey('recommendations', $dashboard);
        $this->assertArrayHasKey('stats', $dashboard);
        
        $this->assertCount(5, $dashboard['insights']);
        $this->assertCount(3, $dashboard['recommendations']);
    }

    /** @test */
    public function it_calculates_savings_correctly(): void
    {
        AIRecommendation::factory()->create([
            'team_id' => $this->team->id,
            'status' => 'implemented',
            'estimated_savings' => 50000,
        ]);

        AIRecommendation::factory()->create([
            'team_id' => $this->team->id,
            'status' => 'implemented',
            'estimated_savings' => 30000,
        ]);

        $dashboard = $this->service->getDashboardInsights($this->team);

        $this->assertEquals(80000, $dashboard['stats']['total_savings']);
    }

    /** @test */
    public function it_filters_by_category(): void
    {
        Machine::factory()->count(2)->create(['team_id' => $this->team->id]);

        $recommendations = $this->service->getRecommendationsForCategory(
            $this->team,
            'fleet',
            $this->user
        );

        $this->assertNotNull($recommendations);
        
        // All recommendations should be fleet category
        foreach ($recommendations as $rec) {
            $this->assertEquals('fleet', $rec->category);
        }
    }

    /** @test */
    public function it_stores_recommendations_with_confidence_scores(): void
    {
        Machine::factory()->count(3)->create([
            'team_id' => $this->team->id,
            'status' => 'idle',
        ]);

        $result = $this->service->runComprehensiveAnalysis($this->team, $this->user);
        
        $recommendations = AIRecommendation::where('team_id', $this->team->id)->get();
        
        foreach ($recommendations as $rec) {
            $this->assertGreaterThanOrEqual(0, $rec->confidence_score);
            $this->assertLessThanOrEqual(1, $rec->confidence_score);
        }
    }

    /** @test */
    public function it_handles_empty_fleet_gracefully(): void
    {
        // No machines created - test that service doesn't crash
        $result = $this->service->runComprehensiveAnalysis($this->team, $this->user);
        
        $this->assertNotNull($result);
        // Recommendations could be a Collection or array
        $this->assertTrue(
            is_array($result['recommendations']) || $result['recommendations'] instanceof \Illuminate\Support\Collection
        );
        // Service should run without errors even with empty fleet
        $this->assertArrayHasKey('insights', $result);
    }
}
