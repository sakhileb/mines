<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureTeamContext Middleware
 * 
 * Ensures every request has a valid team context
 * Sets the current team for the authenticated user
 * Used to enforce multi-tenancy throughout the application
 */
class EnsureTeamContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get authenticated user
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        // Get team_id from route or use user's current team
        $teamId = $request->route('team_id') ?? $user->current_team_id;

        // If no team_id, set to user's default team
        if (!$teamId) {
            $teamId = $user->teams()->first()?->id;
            if ($teamId) {
                $user->update(['current_team_id' => $teamId]);
            }
        }

        // Verify user has access to the team
        if ($teamId) {
            $team = \App\Models\Team::find($teamId);
            if (!$team || !$user->belongsToTeam($team)) {
                abort(403, 'Unauthorized to access this team.');
            }
        }

        // Store team context in request
        $request->attributes->set('team_id', $teamId);

        return $next($request);
    }
}
