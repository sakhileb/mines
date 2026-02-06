<?php

namespace App\Policies;

use App\Models\Geofence;
use App\Models\User;

class GeofencePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_geofences');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Geofence $geofence): bool
    {
        return $user->current_team_id === $geofence->team_id &&
               $user->hasPermission('view_geofences');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create_geofences');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Geofence $geofence): bool
    {
        return $user->current_team_id === $geofence->team_id &&
               $user->hasPermission('update_geofences');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Geofence $geofence): bool
    {
        return $user->current_team_id === $geofence->team_id &&
               $user->hasPermission('delete_geofences');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Geofence $geofence): bool
    {
        return $user->current_team_id === $geofence->team_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Geofence $geofence): bool
    {
        return $user->current_team_id === $geofence->team_id &&
               $user->hasPermission('delete_geofences');
    }
}
