<div>
    <!-- Leaflet CSS loaded inline -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <div class="space-y-6">
        <!-- Back Button -->
        <button 
            wire:click="backToList"
            class="flex items-center text-blue-600 hover:text-blue-800 font-medium transition"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to List
        </button>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Section -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-gray-800 rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-white mb-4">Basic Information</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Mine Area Name *</label>
                            <input 
                                type="text" 
                                wire:model="name"
                                placeholder="e.g., North Pit"
                                class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500"
                            />
                            @error('name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Type *</label>
                            <select 
                                wire:model="type"
                                class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500"
                            >
                                <option value="">Select Type</option>
                                <option value="pit">Pit</option>
                                <option value="stockpile">Stockpile</option>
                                <option value="dump">Dump</option>
                                <option value="processing">Processing</option>
                                <option value="facility">Facility</option>
                            </select>
                            @error('type') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                            <textarea 
                                wire:model="description"
                                rows="3"
                                class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Location & Map -->
                <div class="bg-gray-800 rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-white">Location</h2>
                        <button 
                            wire:click="toggleDrawing"
                            class="px-4 py-2 rounded-lg font-medium text-sm transition
                                @if($isDrawing) 
                                    bg-red-600 text-white hover:bg-red-700
                                @else 
                                    bg-blue-600 text-white hover:bg-blue-700
                                @endif
                            "
                        >
                            @if($isDrawing) Stop Drawing @else Start Drawing @endif
                        </button>
                    </div>

                    @if($isDrawing)
                        <div class="mb-4 p-3 bg-blue-500 bg-opacity-20 border border-blue-500 rounded-lg">
                            <p class="text-sm text-blue-300">Click on the map to add {{ 4 - count($coordinates) }} more point(s)</p>
                        </div>
                    @endif

                    <!-- Debug Status -->
                    <div id="map-status" class="mb-2 p-2 bg-gray-700 rounded text-xs text-gray-300 font-mono">
                        Initializing map...
                    </div>
                    
                    <!-- Map Container -->
                    <div 
                        id="map" 
                        wire:ignore 
                        class="w-full rounded-lg border border-gray-700 bg-gray-900 mb-4"
                        style="height: 384px;"
                    ></div>

                    <!-- Coordinates -->
                    <div class="space-y-3">
                        <h3 class="font-medium text-white">Coordinates ({{ count($coordinates) }} / 4)</h3>
                        @if(count($coordinates) > 0)
                            <div class="space-y-2 max-h-32 overflow-y-auto">
                                @foreach($coordinates as $idx => $coord)
                                    <div class="flex items-center justify-between bg-gray-700 p-2 rounded text-sm">
                                        <span class="text-gray-300">{{ $coord['lat'] }}, {{ $coord['lon'] }}</span>
                                        <button 
                                            wire:click="removeCoordinate({{ $idx }})"
                                            class="text-red-500 hover:text-red-700"
                                        >✕</button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-400">No coordinates added yet</p>
                        @endif

                        @if(count($coordinates) > 0)
                            <button 
                                wire:click="clearCoordinates"
                                wire:confirm="Clear all coordinates?"
                                class="text-sm text-red-500 hover:text-red-700"
                            >Clear All</button>
                        @endif
                    </div>

                    <!-- Manual Entry -->
                    <div class="mt-4 pt-4 border-t border-gray-700">
                        <div class="flex gap-2">
                            <input 
                                type="number" 
                                step="0.000001"
                                wire:model.lazy="tempLat"
                                placeholder="Lat"
                                class="flex-1 px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white text-sm"
                            />
                            <input 
                                type="number" 
                                step="0.000001"
                                wire:model.lazy="tempLon"
                                placeholder="Lon"
                                class="flex-1 px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white text-sm"
                            />
                            <button 
                                wire:click="addCoordinate"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
                            >Add</button>
                        </div>
                    </div>
                </div>

                <!-- Shifts, Materials, Targets -->
                <div class="bg-gray-800 rounded-lg shadow-lg p-6 space-y-4">
                    <button 
                        wire:click="openShiftModal"
                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                    >+ Add Shift</button>
                    <button 
                        wire:click="openMaterialModal"
                        class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition"
                    >+ Add Material Type</button>
                    <button 
                        wire:click="openTargetsModal"
                        class="w-full px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition"
                    >Set Production Targets</button>
                </div>

                <!-- Actions -->
                <div class="flex gap-3">
                    <button 
                        wire:click="backToList"
                        class="flex-1 px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition"
                    >Cancel</button>
                    <button 
                        wire:click="saveMineArea"
                        class="flex-1 px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition font-medium"
                    >Save</button>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="bg-gray-800 rounded-lg shadow-lg p-6 h-fit">
                <h3 class="text-lg font-semibold text-white mb-4">Summary</h3>
                <div class="space-y-3 text-sm text-gray-300">
                    <div>
                        <span class="text-gray-400">Name:</span>
                        <p class="text-white font-medium">{{ $name ?: 'Not set' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-400">Type:</span>
                        <p class="text-white font-medium">{{ $type ?: 'Not set' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-400">Coordinates:</span>
                        <p class="text-white font-medium">{{ count($coordinates) }} / 4</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SHIFT MODAL -->
    @if($showShiftModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click="closeShiftModal">
        <div class="bg-gray-800 rounded-lg w-full max-w-md border border-gray-700" @click.stop>
            <div class="p-6 border-b border-gray-700">
                <h3 class="text-xl font-bold text-white">{{ $editingShiftIndex !== null ? 'Edit Shift' : 'Add Shift' }}</h3>
            </div>
            <div class="p-6 space-y-4">
                <input type="text" wire:model="shiftName" placeholder="Shift name" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded text-white" />
                <input type="time" wire:model="shiftStartTime" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded text-white" />
                <input type="time" wire:model="shiftEndTime" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded text-white" />
                <div class="grid grid-cols-2 gap-2">
                    @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $i => $day)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="shiftDays" value="{{ $day }}" class="rounded" />
                            <span class="text-sm text-gray-300">{{ $day }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="p-6 border-t border-gray-700 flex gap-3">
                <button wire:click="closeShiftModal" class="flex-1 px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">Cancel</button>
                <button wire:click="saveShift" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save</button>
            </div>
        </div>
    </div>
    @endif

    <!-- MATERIAL MODAL -->
    @if($showMaterialModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click="closeMaterialModal">
        <div class="bg-gray-800 rounded-lg w-full max-w-md border border-gray-700" @click.stop>
            <div class="p-6 border-b border-gray-700">
                <h3 class="text-xl font-bold text-white">Add Material Type</h3>
            </div>
            <div class="p-6">
                <input type="text" wire:model="newMaterialType" placeholder="e.g., Coal, Iron Ore" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded text-white" />
            </div>
            <div class="p-6 border-t border-gray-700 flex gap-3">
                <button wire:click="closeMaterialModal" class="flex-1 px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">Cancel</button>
                <button wire:click="addMaterialType" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">Add</button>
            </div>
        </div>
    </div>
    @endif

    <!-- TARGETS MODAL -->
    @if($showTargetsModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click="closeTargetsModal">
        <div class="bg-gray-800 rounded-lg w-full max-w-md border border-gray-700" @click.stop>
            <div class="p-6 border-b border-gray-700">
                <h3 class="text-xl font-bold text-white">Mining Targets</h3>
            </div>
            <div class="p-6 space-y-4">
                <select wire:model="mining_targets.unit" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded text-white">
                    <option value="tonnes">Tonnes</option>
                    <option value="cubic_meters">Cubic Meters</option>
                </select>
                <input type="number" step="0.01" wire:model="mining_targets.daily" placeholder="Daily" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded text-white" />
                <input type="number" step="0.01" wire:model="mining_targets.weekly" placeholder="Weekly" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded text-white" />
                <input type="number" step="0.01" wire:model="mining_targets.monthly" placeholder="Monthly" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded text-white" />
                <input type="number" step="0.01" wire:model="mining_targets.yearly" placeholder="Yearly" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded text-white" />
            </div>
            <div class="p-6 border-t border-gray-700 flex gap-3">
                <button wire:click="closeTargetsModal" class="flex-1 px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">Cancel</button>
                <button wire:click="saveTargets" class="flex-1 px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">Save</button>
            </div>
        </div>
    </div>
    @endif

    <!-- Leaflet JS Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-providers/1.13.0/leaflet-providers.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- Inline Map Initialization -->
    <script>
    (function() {
        'use strict';
        
        let map = null;
        let markers = [];
        let polygon = null;
        
        window.MineAreaMapManager = {
            map: null,
            isDrawing: false,
            initMap: initMap
        };
        
        function updateStatus(message) {
            const el = document.getElementById('map-status');
            if (el) {
                el.textContent = message;
                if (message.includes('✅')) {
                    el.style.backgroundColor = '#065f46';
                    el.style.color = '#d1fae5';
                } else if (message.includes('❌')) {
                    el.style.backgroundColor = '#7f1d1d';
                    el.style.color = '#fecaca';
                } else {
                    el.style.backgroundColor = '#374151';
                    el.style.color = '#e5e7eb';
                }
            }
        }
        
        function initMap() {
            console.log('🗺️  Map init called');
            
            if (typeof L === 'undefined') {
                console.log('⏳ Leaflet not ready');
                setTimeout(initMap, 100);
                return;
            }
            
            const container = document.getElementById('map');
            if (!container) {
                console.log('⏳ Container not found');
                setTimeout(initMap, 100);
                return;
            }
            
            if (map) {
                console.log('✅ Map already initialized');
                map.invalidateSize();
                return;
            }
            
            try {
                console.log('🚀 Creating map...');
                map = L.map('map').setView([-25.7479, 28.1872], 6);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap',
                    maxZoom: 19
                }).addTo(map);
                
                window.MineAreaMapManager.map = map;
                updateStatus('✅ Map ready!');
                console.log('✅ Map initialized');
                
                setTimeout(() => {
                    if (map) map.invalidateSize();
                }, 200);
                
                // Map click handler
                map.on('click', function(e) {
                    if (window.MineAreaMapManager.isDrawing) {
                        const lat = parseFloat(e.latlng.lat.toFixed(6));
                        const lon = parseFloat(e.latlng.lng.toFixed(6));
                        console.log('📍 Add:', lat, lon);
                        
                        if (window.Livewire) {
                            const el = document.querySelector('[wire\\:id]');
                            if (el) {
                                const id = el.getAttribute('wire:id');
                                const component = window.Livewire.find(id);
                                if (component) {
                                    component.call('addCoordinateFromMap', lat, lon);
                                }
                            }
                        }
                    }
                });
                
            } catch (error) {
                console.error('❌ Map error:', error);
                updateStatus('❌ Error: ' + error.message);
            }
        }
        
        // Initialize
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMap);
        } else {
            setTimeout(initMap, 50);
        }
        
        window.addEventListener('load', initMap);
        
        // Livewire events
        document.addEventListener('livewire:updated', function() {
            console.log('🔄 Livewire updated');
            if (!window.MineAreaMapManager.map) {
                setTimeout(initMap, 100);
            }
        });
        
        // Coordinate update function
        window.updateMineAreaCoordinates = function(coords) {
            if (!map) return;
            
            markers.forEach(m => map.removeLayer(m));
            markers = [];
            if (polygon) {
                map.removeLayer(polygon);
                polygon = null;
            }
            
            if (!Array.isArray(coords) || coords.length === 0) return;
            
            coords.forEach(c => {
                const m = L.circleMarker([c.lat, c.lon], {
                    radius: 12,
                    fillColor: '#2563eb',
                    color: '#fff',
                    weight: 3,
                    opacity: 1,
                    fillOpacity: 0.9
                }).addTo(map);
                markers.push(m);
            });
            
            if (coords.length >= 3) {
                const latlngs = coords.map(c => [c.lat, c.lon]);
                polygon = L.polygon(latlngs, {
                    color: coords.length === 4 ? '#10b981' : '#f59e0b',
                    fillColor: coords.length === 4 ? '#34d399' : '#fbbf24',
                    fillOpacity: 0.2,
                    weight: 3
                }).addTo(map);
            }
            
            if (markers.length > 0) {
                const group = L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.2));
            }
        };
        
        // Update drawing mode
        window.MineAreaMapManager.setDrawing = function(isDrawing) {
            window.MineAreaMapManager.isDrawing = isDrawing;
            if (map) {
                map.getContainer().style.cursor = isDrawing ? 'crosshair' : 'default';
            }
        };
        
    })();
    </script>

    <style>
    #map {
        z-index: 10;
    }
    
    .leaflet-container {
        background: #1f2937 !important;
        font-family: inherit;
    }
    
    .leaflet-popup-content-wrapper {
        background-color: #374151;
        color: #f3f4f6;
        border-radius: 4px;
    }
    
    .leaflet-popup-tip {
        background-color: #374151;
    }
    
    .leaflet-control-zoom {
        background-color: rgba(31, 41, 55, 0.9) !important;
        border-radius: 8px !important;
    }
    
    .leaflet-control-zoom a {
        color: white !important;
        background-color: rgba(55, 65, 81, 0.8) !important;
    }
    
    .leaflet-control-zoom a:hover {
        background-color: rgba(75, 85, 99, 0.9) !important;
    }
    </style>

</div>
