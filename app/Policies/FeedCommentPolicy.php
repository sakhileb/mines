<?php

namespace App\Policies;

use App\Models\FeedComment;
use App\Models\User;

class FeedCommentPolicy
{
    public function before(User $user): ?bool
    {
        if ($user->hasRole(['admin', 'supervisor', 'manager'])) {
            return true;
        }

        return null;
    }

    public function update(User $user, FeedComment $comment): bool
    {
        return $comment->isEditableBy($user);
    }

    public function delete(User $user, FeedComment $comment): bool
    {
        return $user->id === $comment->author_id
            && $user->current_team_id === $comment->post->team_id;
    }
}
