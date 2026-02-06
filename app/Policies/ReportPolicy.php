<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_reports');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Report $report): bool
    {
        return $user->current_team_id === $report->team_id &&
               $user->hasPermission('view_reports');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create_reports');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Report $report): bool
    {
        return $user->current_team_id === $report->team_id &&
               $user->hasPermission('update_reports');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Report $report): bool
    {
        return $user->current_team_id === $report->team_id &&
               $user->hasPermission('delete_reports');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Report $report): bool
    {
        return $user->current_team_id === $report->team_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Report $report): bool
    {
        return $user->current_team_id === $report->team_id &&
               $user->hasPermission('delete_reports');
    }

    /**
     * Generate report
     */
    public function generate(User $user): bool
    {
        return $user->hasPermission('create_reports');
    }

    /**
     * Download report file
     */
    public function download(User $user, Report $report): bool
    {
        return $user->current_team_id === $report->team_id &&
               $user->hasPermission('view_reports');
    }
}
