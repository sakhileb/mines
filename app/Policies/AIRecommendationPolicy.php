<?php

namespace App\Policies;

use App\Models\AIRecommendation;
use App\Models\User;

class AIRecommendationPolicy
{
    /**
     * Determine whether the user can view any recommendations.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_recommendations');
    }

    /**
     * Determine whether the user can view the recommendation.
     */
    public function view(User $user, AIRecommendation $recommendation): bool
    {
        return $user->current_team_id === $recommendation->team_id &&
               ($user->hasPermission('view_recommendations') || $user->hasRole('owner'));
    }

    /**
     * Determine whether the user can create recommendations.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create_recommendations');
    }

    /**
     * Determine whether the user can update (implement/reject) the recommendation.
     */
    public function update(User $user, AIRecommendation $recommendation): bool
    {
        // Owners and admins may act across teams
        if ($user->hasRole('owner') || $user->hasRole('admin') || $user->hasRole('administrator')) {
            return true;
        }

        // Allow any user on the same team to act (team members can manage recommendations),
        // or users with the explicit permission. Admin/owner roles were handled above.
        if ($user->current_team_id === $recommendation->team_id) {
            return true;
        }

        return $user->hasPermission('update_recommendations');
    }

    /**
     * Determine whether the user can delete the recommendation.
     */
    public function delete(User $user, AIRecommendation $recommendation): bool
    {
        return $user->current_team_id === $recommendation->team_id &&
               $user->hasPermission('delete_recommendations');
    }
}
