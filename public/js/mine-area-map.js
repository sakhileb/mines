// === MINE AREA MAP - EXTERNAL SCRIPT ===
console.log('🟢 [MAP] External script loading');

(function() {
    'use strict';
    
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
        console.log('📊 [STATUS]', message);
        const statusEl = document.getElementById('map-status');
        if (statusEl) {
            statusEl.textContent = message;
            if (message.includes('✅')) {
                statusEl.style.backgroundColor = '#065f46';
                statusEl.style.color = '#d1fae5';
            } else if (message.includes('❌')) {
                statusEl.style.backgroundColor = '#7f1d1d';
                statusEl.style.color = '#fecaca';
            } else {
                statusEl.style.backgroundColor = '#374151';
                statusEl.style.color = '#e5e7eb';
            }
        }
    };
    
    function initializeMineAreaMap() {
        console.log('🗺️ [MAP] initializeMineAreaMap() called (attempt ' + mgr.initRetryCount + ')');
        updateDebugStatus('Initializing...');
        
        // Check if Leaflet is loaded
        if (typeof window.L === 'undefined' && typeof L === 'undefined') {
            mgr.initRetryCount++;
            if (mgr.initRetryCount > mgr.MAX_INIT_RETRIES) {
                console.error('❌ [MAP] Leaflet failed to load after maximum retries');
                updateDebugStatus('❌ Error: Leaflet library failed to load');
                return;
            }
            console.log('⏳ [MAP] Waiting for Leaflet... retry ' + mgr.initRetryCount);
            setTimeout(initializeMineAreaMap, 200);
            return;
        }
        
        console.log('✅ [MAP] Leaflet loaded (v' + L.version + ')');
        
        // Check if map container exists
        const mapContainer = document.getElementById('map');
        if (!mapContainer) {
            console.error('❌ [MAP] Container #map not found, retrying...');
            if (mgr.initRetryCount < mgr.MAX_INIT_RETRIES) {
                setTimeout(initializeMineAreaMap, 100);
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
        // Drawing mode toggle
        window.addEventListener('drawing-mode-changed', (event) => {
            const data = event.detail[0] || event.detail;
            window.mineAreaDrawing = data.drawing;
            
            if (data.drawing) {
                map.getContainer().style.cursor = 'crosshair';
            } else {
                map.getContainer().style.cursor = '';
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
                    
                    mgr.markers.push(marker);
                });
                
                // Draw polygon/polyline
                if (coordinates.length >= 2) {
                    const latlngs = coordinates.map(c => [c.lat, c.lon]);
                    
                    if (coordinates.length >= 3) {
                        mgr.polygon = L.polygon(latlngs, {
                            color: coordinates.length === 4 ? '#10b981' : '#f59e0b',
                            fillColor: coordinates.length === 4 ? '#34d399' : '#fbbf24',
                            fillOpacity: 0.2,
                            weight: 3
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
        
        // Map click
        map.on('click', function(e) {
            if (window.mineAreaDrawing) {
                try {
                    if (window.Livewire) {
                        const wireElement = document.querySelector('[wire\\:id]');
                        if (wireElement) {
                            const wireId = wireElement.getAttribute('wire:id');
                            const component = window.Livewire.find(wireId);
                            if (component) {
                                component.call('addCoordinateFromMap', 
                                    parseFloat(e.latlng.lat.toFixed(6)),
                                    parseFloat(e.latlng.lng.toFixed(6))
                                );
                                console.log('✅ [MAP] Coordinate sent to Livewire');
                            }
                        }
                    }
                } catch (error) {
                    console.error('❌ [MAP] Error sending coordinate:', error);
                }
            }
        });
    }
    
    // Initialize map
    console.log('🔍 [MAP] Checking Leaflet availability:', typeof L);
    if (typeof L !== 'undefined') {
        console.log('✅ [MAP] Leaflet already loaded, initializing map');
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeMineAreaMap);
        } else {
            initializeMineAreaMap();
        }
    } else {
        console.log('⏳ [MAP] Leaflet not immediately available, will retry');
        setTimeout(function() {
            if (typeof L !== 'undefined') {
                console.log('✅ [MAP] Leaflet loaded after delay');
                initializeMineAreaMap();
            } else {
                console.error('❌ [MAP] Leaflet still not available after delay');
                setTimeout(initializeMineAreaMap, 500);
            }
        }, 500);
    }
    
    console.log('🔴 [MAP] External script loaded and execution started');
})();
