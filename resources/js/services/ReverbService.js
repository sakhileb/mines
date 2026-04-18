/**
 * Reverb WebSocket Service
 * Handles real-time event subscriptions and listeners
 * 
 * This service manages connections to Laravel Reverb for real-time updates
 * including machine locations, alerts, geofence events, and machine status changes.
 */

class ReverbService {
    constructor() {
        this.subscriptions = new Map();
        this.listeners = new Map();
        this.isConnected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 3000; // 3 seconds
    }

    /**
     * Initialize Reverb connection
     * @param {string} userId - Current user ID
     * @param {string} teamId - Current team ID
     * @returns {Promise<void>}
     */
    async init(userId, teamId) {
        try {
            // Check if Echo is already loaded
            if (typeof window.Echo === 'undefined') {
                console.error('Laravel Echo not found. Ensure it is loaded before ReverbService.');
                return;
            }

            this.userId = userId;
            this.teamId = teamId;
            this.isConnected = true;
            this.reconnectAttempts = 0;

            console.log('✅ Reverb service initialized for user:', userId, 'team:', teamId);
        } catch (error) {
            console.error('❌ Failed to initialize Reverb service:', error);
            this.handleReconnection();
        }
    }

    /**
     * Subscribe to machine location updates
     * @param {string} machineId - Machine ID to subscribe to
     * @param {Function} callback - Function called when location updates
     */
    subscribeMachineLocation(machineId, callback) {
        const channelName = `machine.${machineId}`;

        if (this.subscriptions.has(channelName)) {
            console.warn(`Already subscribed to ${channelName}`);
            return;
        }

        try {
            const channel = window.Echo.channel(channelName);

            channel.listen('MachineLocationUpdated', (data) => {
                console.log('📍 Location update received:', data);
                callback(data);
            });

            this.subscriptions.set(channelName, channel);
            console.log(`✅ Subscribed to machine location updates: ${channelName}`);
        } catch (error) {
            console.error(`❌ Failed to subscribe to ${channelName}:`, error);
        }
    }

    /**
     * Subscribe to all team machine location updates
     * @param {Function} callback - Function called when any location updates
     */
    subscribeTeamLocations(callback) {
        const channelName = `team.${this.teamId}`;

        if (this.subscriptions.has(channelName)) {
            console.warn(`Already subscribed to ${channelName}`);
            return;
        }

        try {
            const channel = window.Echo.channel(channelName);

            channel.listen('MachineLocationUpdated', (data) => {
                console.log('📍 Team location update:', data);
                callback(data);
            });

            this.subscriptions.set(channelName, channel);
            console.log(`✅ Subscribed to team locations: ${channelName}`);
        } catch (error) {
            console.error(`❌ Failed to subscribe to ${channelName}:`, error);
        }
    }

    /**
     * Subscribe to team alerts
     * @param {Function} callback - Function called when alerts are triggered
     */
    subscribeTeamAlerts(callback) {
        const channelName = `alerts.team.${this.teamId}`;

        if (this.subscriptions.has(channelName)) {
            console.warn(`Already subscribed to ${channelName}`);
            return;
        }

        try {
            const channel = window.Echo.channel(channelName);

            channel.listen('AlertTriggered', (data) => {
                console.log('🚨 Alert triggered:', data);
                callback(data);
            });

            this.subscriptions.set(channelName, channel);
            console.log(`✅ Subscribed to team alerts: ${channelName}`);
        } catch (error) {
            console.error(`❌ Failed to subscribe to ${channelName}:`, error);
        }
    }

    /**
     * Subscribe to geofence events (entries and exits)
     * @param {string} geofenceId - Geofence ID to subscribe to
     * @param {Function} entryCallback - Called on geofence entry
     * @param {Function} exitCallback - Called on geofence exit
     */
    subscribeGeofenceEvents(geofenceId, entryCallback, exitCallback) {
        const channelName = `geofence.${geofenceId}`;

        if (this.subscriptions.has(channelName)) {
            console.warn(`Already subscribed to ${channelName}`);
            return;
        }

        try {
            const channel = window.Echo.channel(channelName);

            channel.listen('GeofenceEntryDetected', (data) => {
                console.log('🚪 Geofence entry detected:', data);
                entryCallback(data);
            });

            channel.listen('GeofenceExitDetected', (data) => {
                console.log('🚪 Geofence exit detected:', data);
                exitCallback(data);
            });

            this.subscriptions.set(channelName, channel);
            console.log(`✅ Subscribed to geofence events: ${channelName}`);
        } catch (error) {
            console.error(`❌ Failed to subscribe to ${channelName}:`, error);
        }
    }

    /**
     * Subscribe to machine status changes (online/offline)
     * @param {string} machineId - Machine ID to subscribe to
     * @param {Function} callback - Called when machine status changes
     */
    subscribeMachineStatus(machineId, callback) {
        const channelName = `machine.${machineId}`;

        try {
            const channel = window.Echo.channel(channelName);

            channel.listen('MachineOffline', (data) => {
                console.log('📴 Machine offline:', data);
                callback({
                    type: 'offline',
                    ...data
                });
            });

            this.subscriptions.set(channelName, channel);
            console.log(`✅ Subscribed to machine status: ${channelName}`);
        } catch (error) {
            console.error(`❌ Failed to subscribe to ${channelName}:`, error);
        }
    }

    /**
     * Subscribe to user presence (active users in team)
     * @param {Function} joinCallback - Called when user joins
     * @param {Function} leaveCallback - Called when user leaves
     */
    subscribePresence(joinCallback, leaveCallback) {
        const channelName = `team.presence.${this.teamId}`;

        if (this.subscriptions.has(channelName)) {
            console.warn(`Already subscribed to ${channelName}`);
            return;
        }

        try {
            const channel = window.Echo.join(channelName)
                .here((users) => {
                    console.log('👥 Current users:', users);
                    joinCallback(users);
                })
                .joining((user) => {
                    console.log('👤 User joined:', user);
                    joinCallback([user]);
                })
                .leaving((user) => {
                    console.log('👤 User left:', user);
                    leaveCallback(user);
                });

            this.subscriptions.set(channelName, channel);
            console.log(`✅ Subscribed to presence: ${channelName}`);
        } catch (error) {
            console.error(`❌ Failed to subscribe to ${channelName}:`, error);
        }
    }

    /**
     * Subscribe to the feed channel for a team.
     * Handles live events AND missed-event catch-up on reconnection.
     *
     * @param {string} teamId
     * @param {Object} callbacks
     *   - onNewPost(postData)
     *   - onAcknowledgementUpdated(data)
     *   - onNewComment(data)
     *   - onCommentUpdated(data)
     *   - onCommentDeleted(data)
     *   - onPostLiked(data)
     *   - onPostStatusChanged(data)
     *   - onMissedPosts(posts[])   – called once on reconnect with any posts missed offline
     */
    subscribeFeed(teamId, callbacks = {}) {
        const channelName = `feed.team.${teamId}`;
        const storageKey  = `feed_last_seen_${teamId}`;

        if (this.subscriptions.has(channelName)) {
            console.warn(`Already subscribed to ${channelName}`);
            return;
        }

        // Record the last time this client received a live feed event so we
        // can request missed posts after a reconnect.
        const touchLastSeen = () => {
            localStorage.setItem(storageKey, new Date().toISOString());
        };

        try {
            const channel = window.Echo.private(channelName);

            channel
                .listen('.FeedPostCreated', (data) => {
                    touchLastSeen();
                    callbacks.onNewPost?.(data.post);
                })
                .listen('.FeedAcknowledgementUpdated', (data) => {
                    touchLastSeen();
                    callbacks.onAcknowledgementUpdated?.(data);
                })
                .listen('.FeedCommentCreated', (data) => {
                    touchLastSeen();
                    callbacks.onNewComment?.(data);
                })
                .listen('.FeedCommentUpdated', (data) => {
                    touchLastSeen();
                    callbacks.onCommentUpdated?.(data);
                })
                .listen('.FeedCommentDeleted', (data) => {
                    touchLastSeen();
                    callbacks.onCommentDeleted?.(data);
                })
                .listen('.FeedPostLiked', (data) => {
                    touchLastSeen();
                    callbacks.onPostLiked?.(data);
                })
                .listen('.FeedPostStatusChanged', (data) => {
                    touchLastSeen();
                    callbacks.onPostStatusChanged?.(data);
                });

            this.subscriptions.set(channelName, channel);
            console.log(`✅ Subscribed to feed channel: ${channelName}`);

            // ── Reconnection / missed-event catch-up ──────────────────────────
            // Echo uses Pusher-JS under the hood. We watch for the connection
            // transitioning back to "connected" and fetch any posts published
            // while the socket was down.
            const pusherConn = window.Echo.connector?.pusher?.connection;

            if (pusherConn) {
                pusherConn.bind('state_change', ({ previous, current }) => {
                    const wasDisconnected = ['disconnected', 'unavailable', 'failed'].includes(previous);

                    if (wasDisconnected && current === 'connected') {
                        console.log('🔄 Feed channel reconnected — fetching missed posts');
                        this._fetchMissedFeedPosts(storageKey, teamId, callbacks.onMissedPosts);
                    }
                });
            }

            // If the page loads while already connected (normal page load), set
            // the baseline timestamp so the next reconnect knows where to start.
            if (!localStorage.getItem(storageKey)) {
                touchLastSeen();
            }
        } catch (error) {
            console.error(`❌ Failed to subscribe to feed channel ${channelName}:`, error);
        }
    }

    /**
     * Fetch any feed posts published after the last seen timestamp and pass
     * them to the caller's onMissedPosts callback.
     *
     * @private
     */
    async _fetchMissedFeedPosts(storageKey, teamId, onMissedPosts) {
        const since = localStorage.getItem(storageKey);

        if (!since || typeof onMissedPosts !== 'function') {
            return;
        }

        try {
            const url = new URL('/api/feed', window.location.origin);
            url.searchParams.set('since', since);
            url.searchParams.set('per_page', '50');

            const response = await fetch(url.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                console.warn('Feed catch-up fetch failed:', response.status);
                return;
            }

            const json = await response.json();
            const missed = json.data ?? [];

            if (missed.length > 0) {
                console.log(`📬 Delivering ${missed.length} missed feed post(s)`);
                onMissedPosts(missed);
            }

            // Advance the cursor so the next reconnect starts from now
            localStorage.setItem(storageKey, new Date().toISOString());
        } catch (err) {
            console.error('❌ Feed catch-up fetch error:', err);
        }
    }

    /**
     * Unsubscribe from a specific channel
     * @param {string} channelName - Channel to unsubscribe from
     */
    unsubscribe(channelName) {
        if (this.subscriptions.has(channelName)) {
            try {
                window.Echo.leave(channelName);
                this.subscriptions.delete(channelName);
                console.log(`✅ Unsubscribed from: ${channelName}`);
            } catch (error) {
                console.error(`❌ Failed to unsubscribe from ${channelName}:`, error);
            }
        }
    }

    /**
     * Unsubscribe from all channels
     */
    unsubscribeAll() {
        for (const channelName of this.subscriptions.keys()) {
            this.unsubscribe(channelName);
        }
        console.log('✅ Unsubscribed from all channels');
    }

    /**
     * Register a custom event listener
     * @param {string} eventName - Unique listener name
     * @param {Function} callback - Callback function
     */
    on(eventName, callback) {
        if (!this.listeners.has(eventName)) {
            this.listeners.set(eventName, []);
        }
        this.listeners.get(eventName).push(callback);
    }

    /**
     * Emit a custom event
     * @param {string} eventName - Event name
     * @param {*} data - Event data
     */
    emit(eventName, data) {
        if (this.listeners.has(eventName)) {
            this.listeners.get(eventName).forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    console.error(`Error in listener for ${eventName}:`, error);
                }
            });
        }
    }

    /**
     * Handle reconnection attempts
     */
    handleReconnection() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`⏳ Reconnecting... Attempt ${this.reconnectAttempts}/${this.maxReconnectAttempts}`);
            setTimeout(() => {
                this.init(this.userId, this.teamId);
            }, this.reconnectDelay);
        } else {
            console.error('❌ Max reconnection attempts reached. Please refresh the page.');
            this.isConnected = false;
        }
    }

    /**
     * Check connection status
     * @returns {boolean}
     */
    getConnectionStatus() {
        return this.isConnected;
    }

    /**
     * Get active subscriptions count
     * @returns {number}
     */
    getSubscriptionCount() {
        return this.subscriptions.size;
    }

    /**
     * Dispose service and cleanup
     */
    dispose() {
        this.unsubscribeAll();
        this.listeners.clear();
        this.isConnected = false;
        console.log('✅ ReverbService disposed');
    }
}

// Export singleton instance
export default new ReverbService();
