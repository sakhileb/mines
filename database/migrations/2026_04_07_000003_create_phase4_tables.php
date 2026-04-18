<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── feed_audit_logs ────────────────────────────────────────────────────
        Schema::create('feed_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete();
            $table->string('action', 60);           // pin|unpin|admin_delete|override_approval|invite_sent|go_live_set
            $table->string('subject_type', 100);    // App\Models\FeedPost etc.
            $table->unsignedBigInteger('subject_id');
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['team_id', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
        });

        // ── mine_areas: add is_active ──────────────────────────────────────────
        Schema::table('mine_areas', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('name');
        });

        // ── teams: add active_shifts + feed_go_live_at ────────────────────────
        Schema::table('teams', function (Blueprint $table) {
            $table->json('active_shifts')->nullable()->after('personal_team');
            $table->timestamp('feed_go_live_at')->nullable()->after('active_shifts');
        });

        // ── user_feed_preferences: add seen_onboarding_at ────────────────────
        Schema::table('user_feed_preferences', function (Blueprint $table) {
            $table->timestamp('seen_onboarding_at')->nullable()->after('notify_on_mention');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_audit_logs');
        Schema::table('mine_areas', fn (Blueprint $t) => $t->dropColumn('is_active'));
        Schema::table('teams', fn (Blueprint $t) => $t->dropColumn(['active_shifts', 'feed_go_live_at']));
        Schema::table('user_feed_preferences', fn (Blueprint $t) => $t->dropColumn('seen_onboarding_at'));
    }
};
