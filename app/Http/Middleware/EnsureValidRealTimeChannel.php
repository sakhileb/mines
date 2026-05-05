<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware to validate Reverb (real-time) channel access.
 *
 * The Broadcast::channel() callbacks in routes/channels.php handle the
 * authorisation logic. This middleware enforces that:
 *   1. The request is authenticated before the channel auth endpoint is hit.
 *   2. The channel name is not empty.
 *   3. The requested channel belongs to a known namespace prefix to prevent
 *      enumeration of arbitrary internal channels.
 */
class EnsureValidRealTimeChannel
{
    /**
     * Permitted channel name prefixes (without trailing dot or brace).
     * Any channel whose name does not start with one of these prefixes will be rejected.
     */
    private const ALLOWED_PREFIXES = [
        'App.Models.User.',
        'feed.team.',
        'machines.team.',
        'alerts.team.',
        'geofences.team.',
        'production.team.',
        'maintenance.team.',
        'ai.user.',
        'private-',    // Reverb private-channel prefix
        'presence-',   // Reverb presence-channel prefix
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Must be authenticated to subscribe to any channel.
        if (! $request->user()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $channelName = $request->input('channel_name', '');

        if (empty($channelName)) {
            return response()->json(['error' => 'Channel name is required.'], 422);
        }

        // Check that the channel matches a known prefix.
        $allowed = false;
        foreach (self::ALLOWED_PREFIXES as $prefix) {
            if (str_starts_with($channelName, $prefix)) {
                $allowed = true;
                break;
            }
        }

        if (! $allowed) {
            return response()->json(['error' => 'Unknown channel.'], 403);
        }

        return $next($request);
    }
}
