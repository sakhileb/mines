<?php

namespace App\Policies;

use App\Models\FeedPost;
use App\Models\User;

class FeedPostPolicy
{
    /**
     * Admins bypass all policy checks.
     */
    public function before(User $user): ?bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return true; // All authenticated team members can view the feed
    }

    public function view(User $user, FeedPost $post): bool
    {
        return $user->current_team_id === $post->team_id;
    }

    public function create(User $user): bool
    {
        return true; // All team members can post
    }

    public function update(User $user, FeedPost $post): bool
    {
        // Authors can update their own posts (e.g. add attachments)
        return $user->current_team_id === $post->team_id
            && $user->id === $post->author_id;
    }

    public function delete(User $user, FeedPost $post): bool
    {
        return $user->current_team_id === $post->team_id
            && ($user->id === $post->author_id || $user->hasRole(['supervisor', 'manager']));
    }

    /**
     * Supervisors, managers, and safety officers can approve/reject.
     */
    public function approve(User $user, FeedPost $post): bool
    {
        return $user->current_team_id === $post->team_id
            && $user->hasRole(['supervisor', 'manager', 'safety_officer']);
    }
}
