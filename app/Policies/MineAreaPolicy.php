<?php

namespace App\Policies;

use App\Models\MineArea;
use App\Models\User;

class MineAreaPolicy
{
    /**
     * Determine if the user can view any mine areas.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the mine area.
     */
    public function view(User $user, MineArea $mineArea): bool
    {
        return $user->currentTeam && $user->currentTeam->id === $mineArea->team_id;
    }

    /**
     * Determine if the user can create mine areas.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the mine area.
     */
    public function update(User $user, MineArea $mineArea): bool
    {
        return $user->currentTeam && $user->currentTeam->id === $mineArea->team_id;
    }

    /**
     * Determine if the user can delete the mine area.
     */
    public function delete(User $user, MineArea $mineArea): bool
    {
        return $user->currentTeam && $user->currentTeam->id === $mineArea->team_id;
    }
}
