<?php

namespace App\Policies;

use App\Models\Alert;
use App\Models\User;

class AlertPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_alerts');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Alert $alert): bool
    {
        return $user->current_team_id === $alert->team_id &&
               $user->hasPermission('view_alerts');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create_alerts');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Alert $alert): bool
    {
        return $user->current_team_id === $alert->team_id &&
               $user->hasPermission('update_alerts');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Alert $alert): bool
    {
        return $user->current_team_id === $alert->team_id &&
               $user->hasPermission('delete_alerts');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Alert $alert): bool
    {
        return $user->current_team_id === $alert->team_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Alert $alert): bool
    {
        return $user->current_team_id === $alert->team_id &&
               $user->hasPermission('delete_alerts');
    }

    /**
     * Determine whether the user can acknowledge the alert.
     */
    public function acknowledge(User $user, Alert $alert): bool
    {
        return $user->current_team_id === $alert->team_id &&
               $user->hasPermission('acknowledge_alerts');
    }

    /**
     * Determine whether the user can resolve the alert.
     */
    public function resolve(User $user, Alert $alert): bool
    {
        return $user->current_team_id === $alert->team_id &&
               $user->hasPermission('resolve_alerts');
    }
}
