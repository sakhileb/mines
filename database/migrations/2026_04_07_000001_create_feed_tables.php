<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates all six feed-related tables for Phase 1 MVP.
     */
    public function up(): void
    {
        // ── 1. feed_posts ──────────────────────────────────────────────────────
        Schema::create('feed_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mine_area_id')->nullable()->constrained('mine_areas')->nullOnDelete();
            $table->string('shift', 1)->nullable();                         // A | B | C
            $table->string('category', 50);                                 // breakdown | shift_update | safety_alert | production | general
            $table->string('priority', 20)->default('normal');              // normal | high | critical
            $table->text('body');
            $table->json('meta')->nullable();                               // category-specific fields (machine_id, failure_type, etc.)
            $table->unsignedInteger('like_count')->default(0);
            $table->unsignedInteger('comment_count')->default(0);
            $table->unsignedInteger('acknowledgement_count')->default(0);
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('team_id');
            $table->index('mine_area_id');
            $table->index('category');
            $table->index('created_at');
            $table->index(['team_id', 'category']);
            $table->index(['team_id', 'created_at']);
            $table->index(['team_id', 'priority']);
        });

        // ── 2. feed_acknowledgements ───────────────────────────────────────────
        Schema::create('feed_acknowledgements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('feed_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('acknowledged_at');

            $table->unique(['post_id', 'user_id']);
            $table->index('post_id');
        });

        // ── 3. feed_attachments ────────────────────────────────────────────────
        Schema::create('feed_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('feed_posts')->cascadeOnDelete();
            $table->string('file_url', 2048);
            $table->string('file_type', 100);                              // image/jpeg, audio/mpeg, application/pdf, etc.
            $table->string('file_name', 255)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();           // bytes
            $table->timestamp('uploaded_at');

            $table->index('post_id');
        });

        // ── 4. feed_comments ───────────────────────────────────────────────────
        Schema::create('feed_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('feed_posts')->cascadeOnDelete();
            $table->foreignId('parent_comment_id')->nullable()->constrained('feed_comments')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_edited')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('post_id');
            $table->index('parent_comment_id');
        });

        // ── 5. feed_likes ──────────────────────────────────────────────────────
        Schema::create('feed_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('feed_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('liked_at');

            $table->unique(['post_id', 'user_id']);
            $table->index('post_id');
        });

        // ── 6. feed_approvals ──────────────────────────────────────────────────
        Schema::create('feed_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('feed_posts')->cascadeOnDelete();
            $table->foreignId('approver_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('pending');               // pending | approved | rejected
            $table->text('reason')->nullable();                             // required when rejected
            $table->timestamp('reviewed_at')->nullable();

            $table->unique('post_id');                                      // One approval record per post
            $table->index(['post_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_approvals');
        Schema::dropIfExists('feed_likes');
        Schema::dropIfExists('feed_comments');
        Schema::dropIfExists('feed_attachments');
        Schema::dropIfExists('feed_acknowledgements');
        Schema::dropIfExists('feed_posts');
    }
};
