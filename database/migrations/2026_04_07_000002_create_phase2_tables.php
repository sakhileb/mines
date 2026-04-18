<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 2.1 Shift Templates ───────────────────────────────────────────────
        Schema::create('shift_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('category'); // breakdown, shift_update, safety_alert, production, general
            $table->string('title');
            $table->text('template_body');
            $table->json('required_fields')->nullable(); // array of field names that must be filled
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['team_id', 'category']);
        });

        // ── 2.2 User Feed Notification Preferences ────────────────────────────
        Schema::create('user_feed_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            // per-category notification preferences stored as JSON
            // e.g. {"breakdown":true,"shift_update":false,"safety_alert":true,"production":false,"general":false}
            $table->json('category_preferences')->nullable();
            // push for specific events
            $table->boolean('notify_on_comment')->default(true);
            $table->boolean('notify_on_reply')->default(true);
            $table->boolean('notify_on_approval')->default(true);
            $table->boolean('notify_on_mention')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'team_id']);
        });

        // ── 2.3 Digest Subscriptions ──────────────────────────────────────────
        Schema::create('digest_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'team_id']);
        });

        // ── 2.4 Feed Mentions ─────────────────────────────────────────────────
        Schema::create('feed_mentions', function (Blueprint $table) {
            $table->id();
            $table->string('mentionable_type'); // App\Models\FeedPost or App\Models\FeedComment
            $table->unsignedBigInteger('mentionable_id');
            $table->foreignId('mentioned_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mentioned_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['mentionable_type', 'mentionable_id']);
            $table->index(['mentioned_user_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_mentions');
        Schema::dropIfExists('digest_subscriptions');
        Schema::dropIfExists('user_feed_preferences');
        Schema::dropIfExists('shift_templates');
    }
};
