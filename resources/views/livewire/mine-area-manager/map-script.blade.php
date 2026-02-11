@once
<script>
(function() {
    'use strict';
    
    console.log('🔴 [MAP SCRIPT] Executing map initialization script');
    
    // Initialize global namespace
    window.MineAreaMapManager = window.MineAreaMapManager || {
        map: null,
        markers: [],
        polygon: null,
        initRetryCount: 0,
        MAX_INIT_RETRIES: 50
    };
    
    const mgr = window.MineAreaMapManager;
    
    const updateDebugStatus = (message) => {
        console.log('[MAP STATUS]', message);
        const statusEl = document.getElementById('map-status');
        if (statusEl) statusEl.textContent = message;
    };
    
    function initializeMap() {
        console.log('🗺️ [MAP] initializeMap() called');
        updateDebugStatus('Initializing...');
        
        // Check if Leaflet is loaded
        if (typeof L === 'undefined') {
            mgr.initRetryCount++;
            if (mgr.initRetryCount > mgr.MAX_INIT_RETRIES) {
                console.error('❌ [MAP] Leaflet failed to load');
                updateDebugStatus('❌ Error: Leaflet not loaded');
                return;
            }
            console.log(`⏳ [MAP] Waiting for Leaflet... (${mgr.initRetryCount}/${mgr.MAX_INIT_RETRIES})`);
            setTimeout(initializeMap, 100);
            return;
        }
        
        console.log('✅ [MAP] Leaflet loaded (v' + L.version + ')');
        
        // Check if map container exists
        const mapContainer = document.getElementById('map');
        if (!mapContainer) {
            console.error('❌ [MAP] Container not found, retrying...');
            if (mgr.initRetryCount < mgr.MAX_INIT_RETRIES) {
                setTimeout(initializeMap, 100);
            }
            return;
        }
        
        console.log('✅ [MAP] Container found:', mapContainer.offsetWidth + 'x' + mapContainer.offsetHeight);
        
        // Check if already initialized
        if (mgr.map) {
            console.log('⚠️ [MAP] Already initialized, invalidating size');
            mgr.map.invalidateSize();
            updateDebugStatus('✅ Map updated');
            return;
        }
        
        try {
            console.log('🚀 [MAP] Creating map instance...');
            
            // Remove any existing leaflet ID
            if (mapContainer._leaflet_id) {
                delete mapContainer._leaflet_id;
            }
            
            // Create map
            const map = L.map('map', {
                preferCanvas: true,
                renderer: L.canvas()
            }).setView([-25.7479, 28.1872], 6);
            
            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap',
                maxZoom: 19,
                minZoom: 3
            }).addTo(map);
            
            // Store globally
            mgr.map = map;
            window.mineAreaDrawing = false;
            window.previewMarker = null;
            window.tempHoverMarker = null;
            
            console.log('✅ [MAP] Map initialized successfully!');
            updateDebugStatus('✅ Map ready!');
            
            // Force invalidate after a delay
            setTimeout(() => {
                if (mgr.map) {
                    mgr.map.invalidateSize();
                    console.log('✅ [MAP] Size invalidated');
                }
            }, 250);
            
            // Setup event listeners
            setupMapEventListeners(map);
            
        } catch (error) {
            console.error('❌ [MAP] Error:', error.message);
            updateDebugStatus('❌ Error: ' + error.message);
        }
    }
    
    function setupMapEventListeners(map) {
        // Preview coordinate
        window.addEventListener('preview-coordinate', (event) => {
            const data = event.detail[0] || event.detail;
            const lat = parseFloat(data.lat);
            const lon = parseFloat(data.lon);
            
            if (isNaN(lat) || isNaN(lon)) return;
            
            if (window.previewMarker) {
                map.removeLayer(window.previewMarker);
            }
            
            window.previewMarker = L.circleMarker([lat, lon], {
                radius: 8,
                fillColor: '#f59e0b',
                color: '#fff',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.8
            }).addTo(map);
            
            map.setView([lat, lon], Math.max(map.getZoom(), 13), { animate: true });
        });
        
        // Drawing mode toggle
        window.addEventListener('drawing-mode-changed', (event) => {
            const data = event.detail[0] || event.detail;
            window.mineAreaDrawing = data.drawing;
            
            if (data.drawing) {
                map.getContainer().style.cursor = 'crosshair';
                if (window.drawingPopup) map.closePopup(window.drawingPopup);
                window.drawingPopup = L.popup({ 
                    closeButton: false, 
                    autoClose: false, 
                    closeOnClick: false,
                    className: 'drawing-instruction-popup'
                })
                .setLatLng(map.getCenter())
                .setContent('<div style="text-align: center; font-weight: bold; padding: 8px;">🎯 Click to add points</div>')
                .openOn(map);
            } else {
                map.getContainer().style.cursor = '';
                if (window.drawingPopup) {
                    map.closePopup(window.drawingPopup);
                    window.drawingPopup = null;
                }
            }
        });
        
        // Coordinates updated
        window.addEventListener('coordinates-updated', (event) => {
            const data = event.detail[0] || event.detail;
            const coordinates = data.coordinates;
            
            // Clear existing
            mgr.markers.forEach(marker => map.removeLayer(marker));
            mgr.markers = [];
            
            if (mgr.polygon) {
                map.removeLayer(mgr.polygon);
                mgr.polygon = null;
            }
            
            if (window.previewMarker) {
                map.removeLayer(window.previewMarker);
                window.previewMarker = null;
            }
            
            // Redraw
            if (Array.isArray(coordinates) && coordinates.length > 0) {
                coordinates.forEach((coord, idx) => {
                    const marker = L.circleMarker([coord.lat, coord.lon], {
                        radius: 12,
                        fillColor: '#2563eb',
                        color: '#fff',
                        weight: 3,
                        opacity: 1,
                        fillOpacity: 0.9
                    }).addTo(map);
                    
                    const numberIcon = L.divIcon({
                        className: 'number-label',
                        html: `<div style="background: transparent; color: white; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px; text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">${idx + 1}</div>`,
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    });
                    
                    const numberMarker = L.marker([coord.lat, coord.lon], {
                        icon: numberIcon,
                        interactive: false
                    }).addTo(map);
                    
                    marker.bindPopup(`<b>Point ${idx + 1}</b><br>Lat: ${coord.lat}<br>Lon: ${coord.lon}`);
                    
                    mgr.markers.push(marker, numberMarker);
                });
                
                // Draw polygon/polyline
                if (coordinates.length >= 2) {
                    const latlngs = coordinates.map(c => [c.lat, c.lon]);
                    
                    if (coordinates.length >= 3) {
                        mgr.polygon = L.polygon(latlngs, {
                            color: coordinates.length === 4 ? '#10b981' : '#f59e0b',
                            fillColor: coordinates.length === 4 ? '#34d399' : '#fbbf24',
                            fillOpacity: 0.2,
                            weight: 3,
                            dashArray: coordinates.length === 4 ? null : '10, 10'
                        }).addTo(map);
                    } else {
                        mgr.polygon = L.polyline(latlngs, {
                            color: '#f59e0b',
                            weight: 3,
                            dashArray: '10, 10'
                        }).addTo(map);
                    }
                }
                
                // Fit bounds
                if (mgr.markers.length > 0) {
                    const group = L.featureGroup(mgr.markers);
                    map.fitBounds(group.getBounds().pad(0.2));
                }
            }
        });
        
        // Hover marker
        map.on('mousemove', function(e) {
            if (window.mineAreaDrawing) {
                if (window.tempHoverMarker) {
                    map.removeLayer(window.tempHoverMarker);
                }
                window.tempHoverMarker = L.circleMarker(e.latlng, {
                    radius: 8,
                    fillColor: '#3b82f6',
                    color: '#fff',
                    weight: 2,
                    opacity: 0.6,
                    fillOpacity: 0.4
                }).addTo(map);
            } else if (window.tempHoverMarker) {
                map.removeLayer(window.tempHoverMarker);
                window.tempHoverMarker = null;
            }
        });
        
        // Map click
        map.on('click', function(e) {
            if (window.mineAreaDrawing) {
                if (window.tempHoverMarker) {
                    map.removeLayer(window.tempHoverMarker);
                    window.tempHoverMarker = null;
                }
                
                const flashMarker = L.circleMarker(e.latlng, {
                    radius: 20,
                    fillColor: '#10b981',
                    color: '#fff',
                    weight: 3,
                    opacity: 1,
                    fillOpacity: 0.6
                }).addTo(map);
                
                setTimeout(() => map.removeLayer(flashMarker), 500);
                
                // Use Livewire to call component method
                try {
                    if (window.Livewire) {
                        // Find the Livewire component - try multiple methods
                        let component = null;
                        
                        // Method 1: Find by wire:id in parent elements
                        const wireElement = document.querySelector('[wire\\:id]');
                        if (wireElement) {
                            const wireId = wireElement.getAttribute('wire:id');
                            component = window.Livewire.find(wireId);
                        }
                        
                        // Method 2: Get first component if method 1 fails
                        if (!component && window.Livewire.all) {
                            const components = window.Livewire.all();
                            if (components.length > 0) {
                                component = components[0];
                            }
                        }
                        
                        if (component && typeof component.call === 'function') {
                            component.call('addCoordinateFromMap', 
                                parseFloat(e.latlng.lat.toFixed(6)),
                                parseFloat(e.latlng.lng.toFixed(6))
                            );
                            console.log('✅ [MAP] Coordinate sent to Livewire');
                        } else {
                            console.error('❌ [MAP] Livewire component not found');
                        }
                    } else {
                        console.error('❌ [MAP] Livewire not available');
                    }
                } catch (error) {
                    console.error('❌ [MAP] Error calling Livewire:', error);
                }
            }
        });
    }
    
    // Expose global init function
    window.initMineAreaMap = function() {
        console.log('🌍 [GLOBAL] initMineAreaMap() called');
        if (!mgr.map) {
            mgr.initRetryCount = 0;
            initializeMap();
        } else {
            mgr.map.invalidateSize();
        }
    };
    
    // Auto-initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeMap);
    } else {
        setTimeout(initializeMap, 100);
    }
    
    // Livewire events - handle component updates
    document.addEventListener('livewire:init', function() {
        console.log('[MAP] Livewire initialized');
        Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
            // After Livewire commits changes, check if map needs initialization
            succeed(({ snapshot, effect }) => {
                setTimeout(() => {
                    if (!mgr.map && document.getElementById('map')) {
                        console.log('[MAP] Component updated, initializing map');
                        mgr.initRetryCount = 0;
                        initializeMap();
                    } else if (mgr.map) {
                        mgr.map.invalidateSize();
                    }
                }, 100);
            });
        });
    });
    
    document.addEventListener('livewire:navigated', function() {
        console.log('[MAP] Livewire navigated');
        if (!mgr.map) {
            mgr.initRetryCount = 0;
            setTimeout(initializeMap, 100);
        } else {
            setTimeout(() => {
                if (mgr.map) mgr.map.invalidateSize();
            }, 250);
        }
    });
    
    console.log('🔴 [MAP SCRIPT] Initialization script loaded');
})();
</script>
@endonce
