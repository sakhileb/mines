<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Feed channel — scoped to team (mine)
 * Only members of that team may subscribe.
 */
Broadcast::channel('feed.team.{teamId}', function ($user, $teamId) {
    return (int) $user->current_team_id === (int) $teamId;
});
