/**
 * Livewire Echo Utilities
 * Helper functions to integrate Reverb with Livewire components
 */

import ReverbService from '../services/ReverbService.js';

/**
 * Initialize Reverb for a Livewire component
 * @param {object} component - Livewire component instance
 */
export function initializeReverb(component) {
    if (typeof window.Livewire === 'undefined') {
        console.error('Livewire not loaded');
        return;
    }

    const userId = document.querySelector('meta[name="user-id"]')?.content;
    const teamId = document.querySelector('meta[name="team-id"]')?.content;

    if (userId && teamId) {
        ReverbService.init(userId, teamId);
    } else {
        console.warn('User ID or Team ID not found in meta tags');
    }
}

/**
 * Update Livewire component when location changes
 * @param {object} component - Livewire component instance
 * @param {string} machineId - Machine ID to listen to
 * @param {string} property - Property name to update (default: 'machines')
 */
export function listenMachineLocations(component, machineId, property = 'machines') {
    ReverbService.subscribeMachineLocation(machineId, (data) => {
        // Emit custom event that Livewire can listen to
        window.dispatchEvent(new CustomEvent('machineLocationUpdated', {
            detail: {
                machineId: machineId,
                location: data
            }
        }));

        // Update Livewire component if needed
        if (component && component.$refresh) {
            component.$refresh();
        }
    });
}

/**
 * Listen to all team location updates
 * @param {object} component - Livewire component instance
 */
export function listenTeamLocations(component) {
    ReverbService.subscribeTeamLocations((data) => {
        window.dispatchEvent(new CustomEvent('teamLocationUpdated', {
            detail: data
        }));

        if (component && component.$refresh) {
            component.$refresh();
        }
    });
}

/**
 * Listen to team alerts
 * @param {object} component - Livewire component instance
 */
export function listenTeamAlerts(component) {
    ReverbService.subscribeTeamAlerts((data) => {
        window.dispatchEvent(new CustomEvent('alertTriggered', {
            detail: data
        }));

        if (component && component.$refresh) {
            component.$refresh();
        }
    });
}

/**
 * Listen to geofence events
 * @param {object} component - Livewire component instance
 * @param {string} geofenceId - Geofence ID
 */
export function listenGeofenceEvents(component, geofenceId) {
    ReverbService.subscribeGeofenceEvents(
        geofenceId,
        // Entry callback
        (data) => {
            window.dispatchEvent(new CustomEvent('geofenceEntry', {
                detail: data
            }));
            if (component && component.$refresh) {
                component.$refresh();
            }
        },
        // Exit callback
        (data) => {
            window.dispatchEvent(new CustomEvent('geofenceExit', {
                detail: data
            }));
            if (component && component.$refresh) {
                component.$refresh();
            }
        }
    );
}

/**
 * Listen to machine status changes
 * @param {object} component - Livewire component instance
 * @param {string} machineId - Machine ID
 */
export function listenMachineStatus(component, machineId) {
    ReverbService.subscribeMachineStatus(machineId, (data) => {
        window.dispatchEvent(new CustomEvent('machineStatusChanged', {
            detail: {
                machineId: machineId,
                status: data
            }
        }));

        if (component && component.$refresh) {
            component.$refresh();
        }
    });
}

/**
 * Listen to team presence (active users)
 * @param {object} component - Livewire component instance
 */
export function listenPresence(component) {
    ReverbService.subscribePresence(
        // Join callback
        (users) => {
            window.dispatchEvent(new CustomEvent('usersJoined', {
                detail: users
            }));
        },
        // Leave callback
        (user) => {
            window.dispatchEvent(new CustomEvent('userLeft', {
                detail: user
            }));
        }
    );
}

/**
 * Add event listener for real-time updates
 * @param {string} eventName - Event name ('machineLocationUpdated', 'alertTriggered', etc.)
 * @param {Function} callback - Callback function
 */
export function onRealtimeUpdate(eventName, callback) {
    window.addEventListener(eventName, (e) => {
        callback(e.detail);
    });
}

/**
 * Stop listening to a specific machine
 * @param {string} machineId - Machine ID
 */
export function stopListeningMachine(machineId) {
    const channelName = `machine.${machineId}`;
    ReverbService.unsubscribe(channelName);
}

/**
 * Stop all listeners and cleanup
 */
export function stopAllListeners() {
    ReverbService.unsubscribeAll();
}

/**
 * Get connection status
 * @returns {boolean}
 */
export function isConnected() {
    return ReverbService.getConnectionStatus();
}

/**
 * Get active subscription count
 * @returns {number}
 */
export function getActiveSubscriptions() {
    return ReverbService.getSubscriptionCount();
}

export default {
    initializeReverb,
    listenMachineLocations,
    listenTeamLocations,
    listenTeamAlerts,
    listenGeofenceEvents,
    listenMachineStatus,
    listenPresence,
    onRealtimeUpdate,
    stopListeningMachine,
    stopAllListeners,
    isConnected,
    getActiveSubscriptions
};
