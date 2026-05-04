<?php

namespace App\Policies;

use App\Models\Machine;
use App\Models\Subscription;
use App\Models\User;

class MachinePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_machines');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Machine $machine): bool
    {
        return $user->current_team_id === $machine->team_id &&
               $user->hasPermission('view_machines');
    }

    /**
     * Determine whether the user can create models.
     * Enforces both RBAC permission and subscription fleet slot limit.
     */
    public function create(User $user): bool
    {
        if (! $user->hasPermission('create_machines')) {
            return false;
        }

        if ($user->current_team_id && Subscription::teamHasReachedMachineLimit($user->current_team_id)) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Machine $machine): bool
    {
        return $user->current_team_id === $machine->team_id &&
               $user->hasPermission('update_machines');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Machine $machine): bool
    {
        return $user->current_team_id === $machine->team_id &&
               $user->hasPermission('delete_machines');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Machine $machine): bool
    {
        return $user->current_team_id === $machine->team_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Machine $machine): bool
    {
        return $user->current_team_id === $machine->team_id &&
               $user->hasPermission('delete_machines');
    }

    /**
     * Track machine location
     */
    public function trackLocation(User $user, Machine $machine): bool
    {
        return $user->current_team_id === $machine->team_id &&
               $user->hasPermission('track_machines');
    }

    /**
     * View machine metrics
     */
    public function viewMetrics(User $user, Machine $machine): bool
    {
        return $user->current_team_id === $machine->team_id &&
               $user->hasPermission('view_metrics');
    }
}
