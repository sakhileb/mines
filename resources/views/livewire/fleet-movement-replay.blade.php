<div>
<div class="h-screen flex flex-col bg-slate-900" 
     data-path-coords="{{ json_encode($pathCoordinates ?? []) }}"
     data-geofences="{{ json_encode($geofences ?? []) }}"
     data-routes="{{ json_encode($routes ?? []) }}"
     data-machine-type="{{ $selectedMachineDetails->machine_type ?? '' }}">
    <!-- Leaflet CSS - loaded directly in component -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    
    <style>
        /* Map specific styles */
        #replay-map {
            background: #1f2937;
        }
        
        #replay-map .leaflet-container {
            background: #1f2937;
            height: 100%;
            width: 100%;
        }
    </style>
    
    <!-- Header -->
    <div class="bg-gray-800 border-b border-gray-700 p-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-white">Fleet Movement Replay</h1>
                    <p class="text-gray-400 mt-1">Review historical vehicle movements and routes</p>
                </div>
                <a href="{{ route('fleet') }}" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors">
                    Back to Fleet
                </a>
            </div>
        </div>
    </div>

    <div class="flex-1 flex flex-col md:flex-row overflow-hidden">
        <!-- Left Sidebar - Controls -->
        <div class="w-full md:w-96 bg-gray-800 border-b md:border-b-0 md:border-r border-gray-700 overflow-y-auto p-4 md:p-6">
            <!-- Loading Spinner -->
            @if ($isLoading)
                <div class="flex justify-center items-center h-96">
                    <svg class="animate-spin h-12 w-12 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                </div>
                <script>window.scrollTo(0,0);</script>
            @else
            <!-- Machine Selection -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">Select Machine</label>
                <select id="machine-select" wire:model.live="selectedMachine" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500">
                    <option value="">-- Choose a Machine --</option>
                    @foreach($machines as $machineType => $machineGroup)
                        <optgroup label="{{ strtoupper(str_replace('_', ' ', $machineType)) }}">
                            @foreach($machineGroup as $machine)
                                <option value="{{ $machine->id }}">{{ $machine->name }} ({{ $machine->manufacturer }})</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            <!-- Date Range -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">Start Date</label>
                <input type="date" wire:model="startDate" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">End Date</label>
                <input type="date" wire:model="endDate" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500">
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-2 mb-6">
                <button wire:click="loadReplay" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition-colors font-medium flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Load Replay
                </button>
                <button wire:click="showRecentActivities" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-medium flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Recent
                </button>
                <button wire:click="exportReplayData" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors font-medium flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export
                </button>
                <button wire:click="showRoutes" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-all font-medium flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    Routes
                </button>
            </div>

            <!-- Recent Activities (for selected machine / date range) -->
            @if($showActivities)
                <div class="bg-gray-800 border border-gray-700 rounded-lg p-4 mb-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-semibold text-white">Recent Activities</h4>
                        <button wire:click="hideRecentActivities" class="text-xs text-gray-400 hover:text-gray-300">Close</button>
                    </div>
                    @if(count($machineActivities) > 0)
                        <ul class="space-y-2 text-sm text-gray-300 max-h-64 overflow-y-auto">
                            @foreach($machineActivities as $act)
                                <li>
                                    <div class="text-xs text-gray-400">{{ $act['created_at'] }} — {{ $act['user'] }}</div>
                                    <div class="font-medium">{{ $act['action'] }}</div>
                                    <div class="text-gray-400 text-sm">{{ $act['description'] }}</div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-400">No activities found for the selected machine/date range.</p>
                    @endif
                </div>
            @endif

            <!-- Enhanced Playback Player -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-6 shadow-2xl border border-gray-700 mb-6">
                @if($selectedMachine && $totalPositions > 0)
                    <!-- Player Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-amber-600 rounded-lg flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v6h16V7a2 2 0 00-2-2H4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">Movement Replay</h3>
                                <p class="text-sm text-gray-400">{{ $totalPositions }} recorded positions</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-gray-400 mb-1">Current Time</div>
                            <div class="text-sm font-mono text-amber-400" id="current-timestamp">--:--:--</div>
                        </div>
                    </div>

                    <!-- Progress Bar with Timeline -->
                    <div class="mb-6">
                        <div class="relative h-3 bg-gray-700 rounded-full overflow-hidden mb-2">
                            <!-- Background progress -->
                            <div class="absolute inset-0 bg-gradient-to-r from-amber-500/20 to-amber-600/20"></div>
                            <!-- Active progress -->
                            <div class="absolute inset-y-0 left-0 bg-gradient-to-r from-amber-500 to-amber-600 transition-all duration-300" 
                                 style="width: {{ $totalPositions > 0 ? (($currentPosition + 1) / $totalPositions * 100) : 0 }}%">
                            </div>
                            <!-- Glow effect -->
                            <div class="absolute inset-y-0 bg-gradient-to-r from-transparent via-white/30 to-transparent animate-shimmer" 
                                 style="width: {{ $totalPositions > 0 ? (($currentPosition + 1) / $totalPositions * 100) : 0 }}%; left: 0;">
                            </div>
                        </div>
                        
                        <!-- Seekbar -->
                        <input type="range" 
                               id="replay-slider"
                               min="0" 
                               max="{{ max(0, $totalPositions - 1) }}" 
                               value="{{ $currentPosition }}" 
                               class="w-full h-2 bg-transparent rounded-lg appearance-none cursor-pointer slider-thumb"
                               style="margin-top: -10px;">
                        
                        <!-- Timeline markers -->
                        <div class="flex justify-between text-xs text-gray-500 mt-1 px-1">
                            <span id="start-time">{{ $startDate }}</span>
                            <span class="text-amber-400 font-mono">{{ $currentPosition + 1 }} / {{ $totalPositions }}</span>
                            <span id="end-time">{{ $endDate }}</span>
                        </div>
                    </div>

                    <!-- Main Controls -->
                    <div class="flex items-center justify-center gap-3 mb-6">
                        <!-- Previous Frame -->
                        <button wire:click="previousFrame" 
                                class="p-3 bg-gray-700 hover:bg-gray-600 rounded-lg transition-all transform hover:scale-105 group"
                                title="Previous Frame">
                            <svg class="w-5 h-5 text-gray-300 group-hover:text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.445 14.832A1 1 0 0010 14v-2.798l5.445 3.63A1 1 0 0017 14V6a1 1 0 00-1.555-.832L10 8.798V6a1 1 0 00-1.555-.832l-6 4a1 1 0 000 1.664l6 4z"/>
                            </svg>
                        </button>

                        <!-- Play/Pause Button -->
                        @if($isPlaying)
                            <button wire:click="pause"
                                    class="p-3 bg-gradient-to-br from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 rounded-lg transition-all transform hover:scale-105 group"
                                    title="Pause">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 4a2 2 0 012-2h2a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V4zm8 0a2 2 0 012-2h2a2 2 0 012 2v12a2 2 0 01-2 2h-2a2 2 0 01-2-2V4z"/>
                                </svg>
                            </button>
                        @else
                            <button wire:click="play" data-speed="{{ $playbackSpeed }}"
                                    class="p-3 bg-gradient-to-br from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 rounded-lg transition-all transform hover:scale-105 group"
                                    title="Play">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6.3 2.84A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.27l9.34-5.89a1.5 1.5 0 000-2.54L6.3 2.84z"/>
                                </svg>
                            </button>
                        @endif

                        <!-- Stop Button -->
                        <button wire:click="stop"
                                class="p-3 bg-red-600 hover:bg-red-700 rounded-lg transition-all transform hover:scale-105 group"
                                title="Stop & Reset">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <!-- Next Frame -->
                        <button wire:click="nextFrame" 
                                class="p-3 bg-gray-700 hover:bg-gray-600 rounded-lg transition-all transform hover:scale-105 group"
                                title="Next Frame">
                            <svg class="w-5 h-5 text-gray-300 group-hover:text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4.555 5.168A1 1 0 003 6v8a1 1 0 001.555.832L10 11.202V14a1 1 0 001.555.832l6-4a1 1 0 000-1.664l-6-4A1 1 0 0010 6v2.798l-5.445-3.63z"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Speed Control & Additional Options -->
                    <div class="space-y-4">
                        <!-- Playback Speed -->
                        <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700">
                            <div class="flex items-center justify-between mb-3">
                                <label class="text-sm font-medium text-gray-300 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    Playback Speed
                                </label>
                                <span class="text-amber-400 font-bold text-lg">{{ $playbackSpeed }}x</span>
                            </div>
                            <div class="flex gap-2">
                                @foreach([0.25, 0.5, 1, 2, 4, 8] as $speed)
                                    <button wire:click="setSpeed({{ $speed }})" 
                                            class="flex-1 px-2 py-2 rounded-lg text-sm font-medium transition-all {{ $playbackSpeed == $speed ? 'bg-amber-600 text-white shadow-lg' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                                        {{ $speed }}x
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Playback Options -->
                        <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700">
                            <label class="text-sm font-medium text-gray-300 mb-3 block flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                                </svg>
                                Options
                            </label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="checkbox" wire:model="autoReplay" class="w-4 h-4 rounded bg-gray-700 border-gray-600 text-amber-600 focus:ring-amber-500">
                                    <span class="text-sm text-gray-400 group-hover:text-gray-300">Loop replay</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="checkbox" wire:model="showTrail" checked class="w-4 h-4 rounded bg-gray-700 border-gray-600 text-amber-600 focus:ring-amber-500">
                                    <span class="text-sm text-gray-400 group-hover:text-gray-300">Show trail</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="checkbox" wire:model="smoothPan" checked class="w-4 h-4 rounded bg-gray-700 border-gray-600 text-amber-600 focus:ring-amber-500">
                                    <span class="text-sm text-gray-400 group-hover:text-gray-300">Smooth camera</span>
                                </label>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Empty State - Waiting for Data -->
                    <div class="text-center py-12">
                        <div class="w-20 h-20 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v6h16V7a2 2 0 00-2-2H4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">Ready to Replay</h3>
                        <p class="text-gray-400 text-sm">Select a machine, date range, and click "Load Replay" to view historical movements</p>
                    </div>
                @endif
            </div>

            @endif
        </div>

        <!-- Map Container -->
        <div class="flex-1 relative bg-gray-800" style="min-height: 400px;" wire:ignore>
            <!-- Map always visible -->
            <div id="replay-map" class="w-full h-full absolute inset-0" style="min-height: 400px;"></div>
            
            <!-- Hover overlay when no data loaded -->
            @if(!$selectedMachine || $totalPositions == 0)
            <div class="absolute inset-0 bg-gray-900/90 backdrop-blur-sm flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity duration-300 pointer-events-none z-[400]" id="map-overlay">
                <div class="text-center p-8 pointer-events-auto">
                    @if(!$selectedMachine)
                        <div class="text-6xl mb-4">🚜</div>
                        <h3 class="text-xl font-semibold text-white mb-2">Select a Machine</h3>
                        <p class="text-gray-400 mb-2">Choose a machine and date range to replay its movement history.</p>
                    @else
                        <div class="text-6xl mb-4">📉</div>
                        <h3 class="text-xl font-semibold text-white mb-2">No Data Available</h3>
                        <p class="text-gray-400 mb-2">No movement data found for the selected time range.</p>
                    @endif
                </div>
            </div>
            @endif
            
            <script>
                // Hide overlay when data is loaded
                function hideMapOverlay() {
                    const overlay = document.getElementById('map-overlay');
                    if (overlay) {
                        overlay.style.display = 'none';
                    }
                }
                
                // Show overlay when no data
                function showMapOverlay() {
                    const overlay = document.getElementById('map-overlay');
                    if (overlay) {
                        overlay.style.display = 'flex';
                    }
                }
            </script>
        </div>
    </div>


    <!-- Leaflet JS - loaded directly in component -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-providers/1.13.0/leaflet-providers.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        // Initialize with safe defaults - all at window scope
        window.replayState = {
            centerLat: {{ $centerLat ?? -26.2041 }},
            centerLng: {{ $centerLng ?? 28.0473 }},
            zoomLevel: {{ $zoomLevel ?? 10 }}
        };

        window.replayMap = null;
        window.pathCoordinates = [];
        window.geofences = [];
        window.routes = [];
        window.initRetryCount = 0;
        const MAX_INIT_RETRIES = 50;
        
        // Map layers
        window.currentMarker = null;
        window.pathPolyline = null;
        window.geofencePolygons = [];
        window.routePolylines = [];
        window.trailPolyline = null;
        window.machineType = '';

        // Helper: normalize various coordinate formats to {lat, lng}
        function normalizeCoord(coord) {
            if (!coord) return null;
            // If already object with lat/lng
            if (typeof coord === 'object' && coord !== null) {
                if (typeof coord.lat === 'number' && typeof coord.lng === 'number') return { lat: coord.lat, lng: coord.lng };
                if (typeof coord.latitude === 'number' && typeof coord.longitude === 'number') return { lat: coord.latitude, lng: coord.longitude };
                // If array-like [lat, lng] or [lng, lat]
                if (Array.isArray(coord) && coord.length >= 2) {
                    const a = Number(coord[0]);
                    const b = Number(coord[1]);
                    if (!Number.isNaN(a) && !Number.isNaN(b)) return { lat: a, lng: b };
                }
                // If object with nested coordinates (GeoJSON style)
                if (coord.coordinates) return normalizeCoord(coord.coordinates);
            }
            // If string, try parse
            if (typeof coord === 'string') {
                try {
                    const parsed = JSON.parse(coord);
                    return normalizeCoord(parsed);
                } catch (e) {
                    return null;
                }
            }
            return null;
        }
        
        // Get machine emoji image based on machine type
        function getMachineEmojiImage(machineType) {
            const emojiMap = {
                'excavator': '/machine-emojis/excavator.svg',
                'articulated_hauler': '/machine-emojis/dump-truck.svg',
                'dozer': '/machine-emojis/bulldozer.svg',
                'grader': '/machine-emojis/grader.svg',
                'support_vehicle': '/machine-emojis/service-truck.svg'
            };
            return emojiMap[machineType] || '/machine-emojis/excavator.svg';
        }
        
        // Load initial data from data attributes
        function loadDataFromAttributes() {
            const componentDiv = document.querySelector('[data-path-coords]');
            if (componentDiv) {
                try {
                    const pathCoordsStr = componentDiv.getAttribute('data-path-coords');
                    const geofencesStr = componentDiv.getAttribute('data-geofences');
                    const routesStr = componentDiv.getAttribute('data-routes');
                    const machineTypeStr = componentDiv.getAttribute('data-machine-type');
                    window.machineType = machineTypeStr || '';
                    // Parse raw strings and normalize shapes
                    const rawPath = pathCoordsStr ? JSON.parse(pathCoordsStr) : [];
                    const rawGeofences = geofencesStr ? JSON.parse(geofencesStr) : [];
                    const rawRoutes = routesStr ? JSON.parse(routesStr) : [];

                    // Normalize pathCoordinates to objects with numeric lat/lng
                    window.pathCoordinates = rawPath.map(p => {
                        const n = normalizeCoord(p);
                        return n ? Object.assign({}, p, { lat: Number(n.lat), lng: Number(n.lng) }) : null;
                    }).filter(Boolean);

                    // Normalize geofences: ensure coordinates is an array of [lat,lng] pairs
                    window.geofences = rawGeofences.map(g => {
                        try {
                            let coords = g.coordinates;
                            if (typeof coords === 'string') coords = JSON.parse(coords);
                            // GeoJSON "coordinates" might be [ [lng,lat], ... ] or [{lat,lng}, ...]
                            const latlngs = [];
                            if (Array.isArray(coords)) {
                                coords.forEach(c => {
                                    const nn = normalizeCoord(c);
                                    if (nn) latlngs.push([nn.lat, nn.lng]);
                                });
                            }
                            return Object.assign({}, g, { coordinates: latlngs });
                        } catch (e) {
                            return Object.assign({}, g, { coordinates: [] });
                        }
                    });

                    // Normalize routes: ensure waypoints become arrays of [lat,lng]
                    window.routes = rawRoutes.map(r => {
                        try {
                            const waypoints = (r.waypoints || []).map(wp => {
                                const nn = normalizeCoord(wp);
                                return nn ? [Number(nn.lat), Number(nn.lng)] : null;
                            }).filter(Boolean);
                            return Object.assign({}, r, { waypoints });
                        } catch (e) {
                            return Object.assign({}, r, { waypoints: [] });
                        }
                    });
                    
                    // Clear the snapped coordinate cache when new data is loaded
                    window.snappedCoordinateCache = {};
                    
                    console.log('Initial data loaded from attributes:', {
                        pathCoords: window.pathCoordinates?.length || 0,
                        geofences: window.geofences?.length || 0,
                        routes: window.routes?.length || 0
                    });
                    
                    // Log route details for debugging
                    if (window.routes?.length > 0) {
                        window.routes.forEach((route, idx) => {
                            console.log(`Route ${idx}: ${route.name} with ${route.waypoints?.length || 0} waypoints`);
                        });
                    }
                } catch (err) {
                    console.error('Error loading initial data:', err);
                }
            }
        }
        
        // Load data immediately
        loadDataFromAttributes();
        
        // Timer update function
        function updateTimerDisplay() {
            try {
                const timerElement = document.getElementById('current-timestamp');
                if (!timerElement) return;
                
                if (Array.isArray(window.pathCoordinates) && window.pathCoordinates.length > 0) {
                    const timestamp = window.pathCoordinates[0]?.timestamp;
                    if (timestamp) {
                        timerElement.textContent = timestamp;
                    }
                }
            } catch (err) {
                console.error('Error updating timer display:', err);
            }
        }

        function initReplayMap() {
            // Debug: Check what's available
            console.log('Checking for Leaflet... window.L:', typeof window.L, 'L:', typeof L);
            console.log('Path coordinates:', window.pathCoordinates?.length);
            
            // Check if Leaflet is loaded (check both window.L and global L)
            if (typeof window.L === 'undefined' && typeof L === 'undefined') {
                window.initRetryCount++;
                if (window.initRetryCount > MAX_INIT_RETRIES) {
                    console.error('Leaflet failed to load after maximum retries');
                    return;
                }
                console.log('Leaflet not loaded yet, retry', window.initRetryCount);
                setTimeout(initReplayMap, 200);
                return;
            }
            
            // Check if map container exists
            const mapContainer = document.getElementById('replay-map');
            if (!mapContainer) {
                console.log('Map container not found, retrying...');
                setTimeout(initReplayMap, 100);
                return;
            }
            
            // Check if map is already initialized
            if (window.replayMap) {
                console.log('Map already initialized');
                return;
            }
            
            console.log('Initializing replay map...');
            
            try {
                // Initialize map
                window.replayMap = L.map('replay-map').setView([window.replayState.centerLat, window.replayState.centerLng], window.replayState.zoomLevel);

                // Add tile layers
                const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap contributors'
                });

                const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    maxZoom: 19,
                    attribution: 'Esri, Maxar, Earthstar Geographics'
                });

                osmLayer.addTo(window.replayMap);

                // Layer control
                L.control.layers({
                    'Standard': osmLayer,
                    'Satellite': satelliteLayer
                }).addTo(window.replayMap);
                
                console.log('Map initialized successfully');
                
                // Invalidate size after short delay
                setTimeout(() => {
                    if (window.replayMap) {
                        window.replayMap.invalidateSize();
                    }
                }, 100);
                
            } catch (error) {
                console.error('Error initializing map:', error);
            }
        }
        
        function renderPathOnMap() {
            try {
                if (!window.replayMap || !Array.isArray(window.pathCoordinates) || window.pathCoordinates.length === 0) {
                    console.log('Cannot render path - map or coordinates missing');
                    return;
                }
                
                // Clear existing path
                if (window.pathPolyline) {
                    window.replayMap.removeLayer(window.pathPolyline);
                }
                
                // Create path polyline with snapped coordinates to stay on routes
                // Cache the snapped path to avoid recalculating on every render
                const pathLatLngs = window.pathCoordinates.map((coord, index) => {
                    const snapped = snapCoordinateToRoute(coord);
                    return [snapped.lat, snapped.lng];
                });
                
                window.pathPolyline = L.polyline(pathLatLngs, {
                    color: '#fbbf24',
                    weight: 3,
                    opacity: 0.7,
                    dashArray: '5, 5',
                    className: 'replay-path',
                    lineCap: 'round',
                    lineJoin: 'round'
                }).addTo(window.replayMap);
                
                console.log('Path rendered with', pathLatLngs.length, 'points (snapped to routes)');
                
                // Log first and last points to verify snapping
                if (pathLatLngs.length > 0) {
                    console.log('Path start:', pathLatLngs[0]);
                    console.log('Path end:', pathLatLngs[pathLatLngs.length - 1]);
                }
            } catch (err) {
                console.error('Error rendering path on map:', err);
            }
        }
        
        function renderGeofencesOnMap() {
            try {
                if (!window.replayMap || !Array.isArray(window.geofences) || window.geofences.length === 0) {
                    console.log('No geofences to render');
                    return;
                }
                
                // Clear existing geofences
                window.geofencePolygons.forEach(polygon => {
                    if (polygon instanceof L.Polygon) {
                        window.replayMap.removeLayer(polygon);
                    }
                });
                window.geofencePolygons = [];
                
                window.geofences.forEach(geofence => {
                    try {
                        const coords = geofence.coordinates || [];
                        const latlngs = [];
                        if (Array.isArray(coords)) {
                            coords.forEach(c => {
                                const nn = normalizeCoord(c);
                                if (nn) latlngs.push([Number(nn.lat), Number(nn.lng)]);
                            });
                        }

                        if (latlngs.length >= 2) {
                            const polygon = L.polygon(latlngs, {
                                color: geofence.color || '#3b82f6',
                                weight: 2,
                                opacity: 0.5,
                                fillOpacity: 0.1,
                                className: 'geofence-poly'
                            }).bindPopup(`<strong>${geofence.name}</strong><br>Type: ${geofence.type}`);

                            polygon.addTo(window.replayMap);
                            window.geofencePolygons.push(polygon);
                        }
                    } catch (e) {
                        console.warn('Skipping invalid geofence during render:', geofence, e);
                    }
                });
                
                console.log('Rendered', window.geofencePolygons.length, 'geofences');
            } catch (err) {
                console.error('Error rendering geofences on map:', err);
            }
        }
        
        function renderRoutesOnMap() {
            try {
                if (!window.replayMap || !Array.isArray(window.routes) || window.routes.length === 0) {
                    console.log('No routes to render');
                    return;
                }
                
                // Clear existing routes
                window.routePolylines.forEach(polyline => {
                    if (polyline instanceof L.Polyline) {
                        window.replayMap.removeLayer(polyline);
                    }
                });
                window.routePolylines = [];
                
                window.routes.forEach((route, routeIndex) => {
                    if (route.waypoints && route.waypoints.length > 0) {
                        // Handle multiple waypoint formats: {latitude, longitude}, {lat, lng}, or [lat, lng]
                        const latlngs = route.waypoints.map(wp => {
                            if (Array.isArray(wp) && wp.length >= 2) {
                                // Array format: [lat, lng]
                                return [wp[0], wp[1]];
                            } else if (wp && typeof wp === 'object') {
                                // Object format: {latitude, longitude} or {lat, lng}
                                const lat = wp.latitude ?? wp.lat;
                                const lng = wp.longitude ?? wp.lng;
                                if (lat !== undefined && lng !== undefined) {
                                    return [lat, lng];
                                }
                            }
                            return null;
                        }).filter(coord => coord !== null); // Remove invalid coordinates
                        
                        if (latlngs.length >= 2) {
                            // Enhanced route polyline with better styling
                            const polyline = L.polyline(latlngs, {
                                color: route.color || '#f59e0b',
                                weight: 3,
                                opacity: 0.9,
                                lineCap: 'round',
                                lineJoin: 'round',
                                className: 'replay-route',
                                dashArray: routeIndex === 0 && route.name === 'Auto-calculated Route' ? '8, 4' : 'none' // Dashed for auto-calculated routes
                            }).bindPopup(`
                                <div class="bg-white p-2 rounded">
                                    <strong>${route.name}</strong><br>
                                    ${route.waypoints?.length || 0} waypoints<br>
                                    From: ${route.start_location}<br>
                                    To: ${route.end_location}
                                </div>
                            `);
                            
                            polyline.addTo(window.replayMap);
                            window.routePolylines.push(polyline);
                        }
                    }
                });
                
                console.log('Rendered', window.routePolylines.length, 'routes on map');
            } catch (err) {
                console.error('Error rendering routes on map:', err);
            }
        }
        
        // Calculate distance between two coordinates (in meters)
        function calculateDistance(lat1, lng1, lat2, lng2) {
            const R = 6371000; // Earth's radius in meters
            const φ1 = lat1 * Math.PI / 180;
            const φ2 = lat2 * Math.PI / 180;
            const Δφ = (lat2 - lat1) * Math.PI / 180;
            const Δλ = (lng2 - lng1) * Math.PI / 180;
            
            const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                      Math.cos(φ1) * Math.cos(φ2) *
                      Math.sin(Δλ/2) * Math.sin(Δλ/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            const distance = R * c;
            
            return distance;
        }
        
        // Get the closest point on a line segment to a point
        function getClosestPointOnLineSegment(lat, lng, lat1, lng1, lat2, lng2) {
            // Handle degenerate segment
            if ((lat1 === lat2) && (lng1 === lng2)) {
                return { lat: lat1, lng: lng1 };
            }

            const dx = lng2 - lng1;
            const dy = lat2 - lat1;

            // Project point onto the line, computing parameter t in [0,1]
            let t = ((lng - lng1) * dx + (lat - lat1) * dy) / (dx * dx + dy * dy);
            t = Math.max(0, Math.min(1, t));

            return {
                lat: lat1 + t * (lat2 - lat1),
                lng: lng1 + t * (lng2 - lng1)
            };
        }

        // Linear interpolation between two positions
        function interpolatePos(from, to, progress) {
            if (!from || !to) return from || to || null;
            const p = Math.max(0, Math.min(1, progress || 0));
            return {
                lat: from.lat + (to.lat - from.lat) * p,
                lng: from.lng + (to.lng - from.lng) * p,
                heading: (from.heading || 0) + ((to.heading || 0) - (from.heading || 0)) * p
            };
        }

        // Given a fractional index into the pathCoordinates (e.g. 3.4), return an interpolated snapped position
        function getInterpolatedPosition(fractionalIndex) {
            if (!Array.isArray(window.pathCoordinates) || window.pathCoordinates.length === 0) return null;
            const lowerIndex = Math.floor(fractionalIndex);
            const upperIndex = Math.ceil(fractionalIndex);
            const progress = fractionalIndex - lowerIndex;

            if (lowerIndex < 0) return snapCoordinateToRoute(window.pathCoordinates[0]);
            if (upperIndex >= window.pathCoordinates.length) return snapCoordinateToRoute(window.pathCoordinates[window.pathCoordinates.length - 1]);

            if (lowerIndex === upperIndex) {
                return snapCoordinateToRoute(window.pathCoordinates[lowerIndex]);
            }

            const from = snapCoordinateToRoute(window.pathCoordinates[lowerIndex]);
            const to = snapCoordinateToRoute(window.pathCoordinates[upperIndex]);
            return interpolatePos(from, to, progress);
        }
        
        // Cache for snapped coordinates to improve performance
        window.snappedCoordinateCache = {};
        
        // Generate cache key for a coordinate
        function generateCoordCacheKey(coord) {
            if (!coord || typeof coord.lat === 'undefined' || typeof coord.lng === 'undefined') {
                return null;
            }
            // Round to 5 decimal places for cache key (approximately 1 meter precision)
            const lat = Math.round(coord.lat * 100000) / 100000;
            const lng = Math.round(coord.lng * 100000) / 100000;
            return `${lat},${lng}`;
        }
        
        // Find the closest point on routes to the given coordinate
        function snapCoordinateToRoute(coord, forceRecalculate = false) {
            if (!coord || typeof coord.lat === 'undefined' || typeof coord.lng === 'undefined') {
                return coord;
            }
            
            // Check cache first
            if (!forceRecalculate) {
                const cacheKey = generateCoordCacheKey(coord);
                if (cacheKey && window.snappedCoordinateCache[cacheKey]) {
                    return window.snappedCoordinateCache[cacheKey];
                }
            }
            
            // If no routes defined, return original coordinate
            if (!Array.isArray(window.routes) || window.routes.length === 0) {
                return coord;
            }
            
            let closestPoint = null;
            let minDistance = Infinity;
            let snapSegment = null;
            const snapRadius = 1000; // Extended snap radius to 1 km for better coverage
            
            // Check each route
            window.routes.forEach(route => {
                if (!route.waypoints || route.waypoints.length < 2) {
                    return;
                }
                
                // Check each segment of the route
                for (let i = 0; i < route.waypoints.length - 1; i++) {
                    const wp1 = route.waypoints[i];
                    const wp2 = route.waypoints[i + 1];
                    
                    // Handle multiple waypoint formats: {latitude, longitude}, {lat, lng}, or [lat, lng]
                    const lat1 = wp1.latitude ?? wp1.lat ?? (Array.isArray(wp1) ? wp1[0] : undefined);
                    const lng1 = wp1.longitude ?? wp1.lng ?? (Array.isArray(wp1) ? wp1[1] : undefined);
                    const lat2 = wp2.latitude ?? wp2.lat ?? (Array.isArray(wp2) ? wp2[0] : undefined);
                    const lng2 = wp2.longitude ?? wp2.lng ?? (Array.isArray(wp2) ? wp2[1] : undefined);
                    
                    // Validate waypoints
                    if (typeof lat1 === 'undefined' || typeof lng1 === 'undefined' ||
                        typeof lat2 === 'undefined' || typeof lng2 === 'undefined') {
                        continue;
                    }
                    
                    // Find closest point on this line segment
                    const closestOnSegment = getClosestPointOnLineSegment(
                        coord.lat, coord.lng,
                        lat1, lng1,
                        lat2, lng2
                    );
                    
                    const distance = calculateDistance(
                        coord.lat, coord.lng,
                        closestOnSegment.lat, closestOnSegment.lng
                    );
                    
                    if (distance < minDistance) {
                        minDistance = distance;
                        snapSegment = { lat1, lng1, lat2, lng2 };
                        closestPoint = {
                            ...coord,
                            lat: closestOnSegment.lat,
                            lng: closestOnSegment.lng
                        };
                    }
                }
            });
            
            // Snap if we found a point (use extended radius)
            if (closestPoint) {
                // Cache the result
                const cacheKey = generateCoordCacheKey(coord);
                if (cacheKey) {
                    window.snappedCoordinateCache[cacheKey] = closestPoint;
                }
                
                if (minDistance <= snapRadius || minDistance <= 5000) { // Allow up to 5km if necessary
                    return closestPoint;
                }
            }
            
            // Fallback: return original if no route segments found
            return coord;
        }
        
        function zoomToRouteArea() {
            if (!window.replayMap || !Array.isArray(window.pathCoordinates) || window.pathCoordinates.length === 0) return;
            
            // Create bounds from path coordinates
            const bounds = L.latLngBounds(window.pathCoordinates.map(coord => [coord.lat, coord.lng]));
            
            // Also add geofence bounds
            if (Array.isArray(window.geofences)) {
                window.geofences.forEach(geofence => {
                    if (geofence.coordinates && geofence.coordinates.length > 0) {
                        geofence.coordinates.forEach(coord => {
                            bounds.extend([coord[0] || coord.lat, coord[1] || coord.lng]);
                        });
                    }
                });
            }
            
            // Fit map to bounds with padding
            window.replayMap.fitBounds(bounds, { padding: [50, 50] });
            console.log('Map zoomed to route area');
        }

        // Center map on the first available path coordinate or on the first route waypoint
        function centerOnSelectedMachine() {
            try {
                if (!window.replayMap) return;

                // Prefer first path coordinate
                if (Array.isArray(window.pathCoordinates) && window.pathCoordinates.length > 0) {
                    // Find first valid coordinate
                    const firstValid = window.pathCoordinates.map(c => normalizeCoord(c)).find(n => n && typeof n.lat !== 'undefined' && typeof n.lng !== 'undefined');
                    if (firstValid) {
                        const lat = Number(firstValid.lat);
                        const lng = Number(firstValid.lng);
                        if (window.smoothPan) {
                            window.replayMap.panTo([lat, lng], { animate: true, duration: 0.6 });
                        } else {
                            window.replayMap.setView([lat, lng], 14);
                        }
                        return;
                    }
                }

                // Fallback: use first route waypoint
                if (Array.isArray(window.routes) && window.routes.length > 0) {
                    const firstRoute = window.routes[0];
                    if (firstRoute && firstRoute.waypoints && firstRoute.waypoints.length > 0) {
                        const wp = firstRoute.waypoints[0];
                        let lat = wp.latitude ?? wp.lat ?? (Array.isArray(wp) ? wp[0] : undefined);
                        let lng = wp.longitude ?? wp.lng ?? (Array.isArray(wp) ? wp[1] : undefined);
                        if (typeof lat !== 'undefined' && typeof lng !== 'undefined') {
                            if (window.smoothPan) {
                                window.replayMap.panTo([lat, lng], { animate: true, duration: 0.6 });
                            } else {
                                window.replayMap.setView([lat, lng], 14);
                            }
                        }
                    }
                }
            } catch (err) {
                console.error('Error centering on selected machine:', err);
            }
        }
        
        function updateMachineMarker(position) {
            try {
                if (!window.replayMap || position < 0 || !Array.isArray(window.pathCoordinates) || position >= window.pathCoordinates.length) return;
                
                const coord = window.pathCoordinates[position];
                if (!coord || typeof coord.lat === 'undefined' || typeof coord.lng === 'undefined') return;
                
                // Snap the coordinate to the nearest route for realistic movement on roads
                const snappedCoord = snapCoordinateToRoute(coord);
                const latlng = [snappedCoord.lat, snappedCoord.lng];
            
                // Remove existing marker
                if (window.currentMarker) {
                    window.replayMap.removeLayer(window.currentMarker);
                }
                
                // Calculate direction based on movement between last snapped and current snapped positions
                let heading = coord.heading || 0;
                if (position > 0) {
                    const prevCoord = window.pathCoordinates[position - 1];
                    if (prevCoord && position > 0) {
                        const prevSnapped = snapCoordinateToRoute(prevCoord);
                        // Calculate bearing from previous to current position
                        const dy = snappedCoord.lat - prevSnapped.lat;
                        const dx = snappedCoord.lng - prevSnapped.lng;
                        heading = Math.atan2(dx, dy) * 180 / Math.PI;
                    }
                }
                
                const speed = coord.speed || 0;
                const emojiImageUrl = getMachineEmojiImage(window.machineType);
                
                const markerHtml = `
                    <div style="
                        background-color: #ef4444;
                        width: 40px;
                        height: 40px;
                        border-radius: 50%;
                        border: 3px solid white;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.5);
                        transform: rotate(${heading}deg);
                        padding: 4px;
                    ">
                        <img src="${emojiImageUrl}" 
                             style="width: 28px; height: 28px; object-fit: contain;" 
                             onerror="this.style.display='none'; this.parentElement.innerHTML='🚜';" 
                             alt="Machine" />
                    </div>
                `;
                
                window.currentMarker = L.marker(latlng, {
                    icon: L.divIcon({
                        html: markerHtml,
                        className: '',
                        iconSize: [40, 40],
                        iconAnchor: [20, 20]
                    })
                }).bindPopup(`
                    <strong>Machine Position</strong><br>
                    Lat: ${snappedCoord.lat.toFixed(6)}<br>
                    Lng: ${snappedCoord.lng.toFixed(6)}<br>
                    Speed: ${speed} km/h<br>
                    Heading: ${heading.toFixed(0)}°<br>
                    Time: ${coord.timestamp}
                `);
                
                window.currentMarker.addTo(window.replayMap);
                
                // Update trail with snapped coordinates combined with smart interpolation
                if (position > 0) {
                    // Build trail from cached snapped coordinates
                    const trailCoordinates = [];
                    for (let i = 0; i <= position; i++) {
                        const pathCoord = window.pathCoordinates[i];
                        const snapped = snapCoordinateToRoute(pathCoord);
                        
                        // Add interpolation waypoints if this is not the first point and there's significant movement
                        if (i > 0) {
                            const prevSnapped = snapCoordinateToRoute(window.pathCoordinates[i - 1]);
                            const distBetween = calculateDistance(prevSnapped.lat, prevSnapped.lng, snapped.lat, snapped.lng);
                            
                            // If there's significant movement between points, add intermediate marker
                            if (distBetween > 50) { // More than 50 meters
                                // Interpolate intermediate point (for smoother trails)
                                const midLat = (prevSnapped.lat + snapped.lat) / 2;
                                const midLng = (prevSnapped.lng + snapped.lng) / 2;
                                trailCoordinates.push([midLat, midLng]);
                            }
                        }
                        
                        trailCoordinates.push([snapped.lat, snapped.lng]);
                    }
                    
                    if (window.trailPolyline) {
                        window.replayMap.removeLayer(window.trailPolyline);
                    }
                    window.trailPolyline = L.polyline(trailCoordinates, {
                        color: '#10b981',
                        weight: 2,
                        opacity: 0.6,
                        className: 'replay-trail',
                        lineCap: 'round',
                        lineJoin: 'round'
                    }).addTo(window.replayMap);
                }
            } catch (err) {
                console.error('Error updating machine marker:', err);
            }
        }

        // Render map elements when data is loaded
        function renderMapElements() {
            console.log('=== Rendering map elements ===');
            console.log('Path coordinates:', window.pathCoordinates?.length || 0);
            console.log('Routes available:', window.routes?.length || 0);
            console.log('Geofences:', window.geofences?.length || 0);
            
            renderPathOnMap();
            renderGeofencesOnMap();
            renderRoutesOnMap();
            zoomToRouteArea();
            
            if (Array.isArray(window.pathCoordinates) && window.pathCoordinates.length > 0) {
                updateMachineMarker(0);
            }
            
            console.log('=== Map rendering complete ===');
        }
        
        // Listen for Livewire component updates
        document.addEventListener('livewire:updated', (e) => {
            console.log('Livewire updated, refreshing data from DOM...');
            
            // Get the main component div
            const componentDiv = document.querySelector('[data-path-coords]');
            if (!componentDiv) {
                console.log('Component div not found');
                return;
            }
            
            // Extract data from data attributes
            try {
                const pathCoordsStr = componentDiv.getAttribute('data-path-coords');
                const geofencesStr = componentDiv.getAttribute('data-geofences');
                const routesStr = componentDiv.getAttribute('data-routes');
                
                window.pathCoordinates = pathCoordsStr ? JSON.parse(pathCoordsStr) : [];
                window.geofences = geofencesStr ? JSON.parse(geofencesStr) : [];
                window.routes = routesStr ? JSON.parse(routesStr) : [];
                
                console.log('Updated path coordinates:', window.pathCoordinates?.length || 0);
                console.log('Updated geofences:', window.geofences?.length || 0);
                console.log('Updated routes:', window.routes?.length || 0);
                
                if (Array.isArray(window.pathCoordinates) && window.pathCoordinates.length > 0) {
                    console.log('Data available, rendering map...');
                    renderMapElements();
                    hideMapOverlay();
                    updateTimerDisplay();
                } else {
                    console.log('No data available, showing overlay');
                    showMapOverlay();
                }
            } catch (err) {
                console.error('Error parsing data from attributes:', err);
            }
        });
        
        // Livewire event listeners
        window.addEventListener('livewire:navigated', () => {
            window.pathCoordinates = @json($pathCoordinates ?? []);
            window.geofences = @json($geofences ?? []);
            window.routes = @json($routes ?? []);
            
            if (Array.isArray(window.pathCoordinates) && window.pathCoordinates.length > 0) {
                renderMapElements();
                hideMapOverlay();
            } else {
                showMapOverlay();
            }
        });

        // Defer Livewire component event listeners until component is ready
        function initializeLivewireListeners() {
            if (typeof @this === 'undefined') {
                setTimeout(initializeLivewireListeners, 100);
                return;
            }

            @this.on('machine-selected', () => {
                console.log('Machine selected event');
                // Wait a moment for Livewire to re-render, then load new data
                setTimeout(() => {
                    loadDataFromAttributes();
                    if (Array.isArray(window.pathCoordinates) && window.pathCoordinates.length > 0) {
                            renderMapElements();
                            // Center map on selected machine's first position
                            centerOnSelectedMachine();
                        hideMapOverlay();
                        updateTimerDisplay();
                    } else {
                        showMapOverlay();
                    }
                }, 100);
            });

            // Listen for Livewire updates
            @this.on('replay-loaded', () => {
                console.log('Replay loaded event');
                // Wait for Livewire to fully re-render the data attributes
                setTimeout(() => {
                    try {
                        loadDataFromAttributes();
                        console.log('After loading attributes:', {
                            pathCoords: window.pathCoordinates?.length || 0,
                            geofences: window.geofences?.length || 0,
                            routes: window.routes?.length || 0
                        });

                        if (Array.isArray(window.pathCoordinates) && window.pathCoordinates.length > 0) {
                            console.log('Rendering map elements with path data...');
                            renderMapElements();
                            // Center on the selected machine for immediate context
                            centerOnSelectedMachine();
                            hideMapOverlay();
                            updateTimerDisplay();
                            console.log('Map rendering completed');
                        } else {
                            console.log('No path coordinates available yet');
                            showMapOverlay();
                        }
                    } catch (err) {
                        console.error('Error in replay-loaded handler:', err);
                    }
                }, 150); // Increased timeout to ensure data is available
            });

            @this.on('show-routes', () => {
                console.log('Show routes event received');
                setTimeout(() => {
                    try {
                        loadDataFromAttributes();
                        if (Array.isArray(window.routes) && window.routes.length > 0) {
                            renderRoutesOnMap();
                            zoomToRouteArea();
                            hideMapOverlay();
                        } else {
                            console.log('No routes available to show');
                        }
                    } catch (err) {
                        console.error('Error showing routes:', err);
                    }
                }, 120);
            });

            @this.on('replay-seek', (data) => {
                try {
                    const position = typeof data?.position === 'number' ? data.position : 0;
                    if (position >= 0 && Array.isArray(window.pathCoordinates) && position < window.pathCoordinates.length) {
                        console.log('Seeking to position', position);
                        updateMachineMarker(position);
                        updateTimerDisplay();
                    }
                } catch (err) {
                    console.error('Error during seek:', err);
                }
            });

            // Handle play/pause for timer update and marker position
            let playbackInterval = null;
            
            @this.on('replay-play', () => {
                console.log('Replay playing');
                if (playbackInterval) clearInterval(playbackInterval);
                
                playbackInterval = setInterval(() => {
                    try {
                        const currentPos = @this?.currentPosition;
                        const totalPositions = @this?.totalPositions || 0;
                        
                        if (typeof currentPos === 'number' && currentPos >= 0) {
                            updateTimerDisplay();
                            updateMachineMarker(currentPos);
                            
                            // Auto-increment position if not at end
                            if (currentPos < totalPositions - 1) {
                                @this.set('currentPosition', currentPos + 1);
                            } else {
                                // Reached end, pause playback
                                @this.call('pause');
                            }
                        }
                    } catch (err) {
                        console.error('Error during playback:', err);
                    }
                }, 100);
            });

            @this.on('replay-pause', () => {
                console.log('Replay paused');
                if (playbackInterval) {
                    clearInterval(playbackInterval);
                    playbackInterval = null;
                }
            });

            @this.on('replay-stop', () => {
                console.log('Replay stopped');
                if (playbackInterval) {
                    clearInterval(playbackInterval);
                    playbackInterval = null;
                }
                try {
                    updateMachineMarker(0);
                    updateTimerDisplay();
                } catch (err) {
                    console.error('Error during stop:', err);
                }
            });
        }
        
        // Call initialization when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeLivewireListeners);
        } else {
            initializeLivewireListeners();
        }

        // When a machine is selected in the sidebar, automatically center the map
        // and hide the overlay prompt. Use event delegation so listeners survive
        // Livewire DOM updates.
        document.addEventListener('change', (e) => {
            try {
                const target = e.target;
                if (!target) return;
            // Prefer checking the element id (stable) and fall back to attribute check
            const isMachineSelect = (target.id === 'machine-select') || (target.getAttribute && target.getAttribute('wire:model.live') === 'selectedMachine');
                if (!isMachineSelect) return;

                // If a machine was chosen (non-empty value), hide the overlay and
                // attempt to center the map if we have coordinates already.
                const val = target.value;
                if (val && val !== '') {
                    // Allow Livewire a moment to update any data attributes
                    setTimeout(() => {
                        try {
                            loadDataFromAttributes();
                            if (Array.isArray(window.pathCoordinates) && window.pathCoordinates.length > 0) {
                                renderMapElements();
                                centerOnSelectedMachine();
                                hideMapOverlay();
                                updateTimerDisplay();
                            } else {
                                // No path data yet — still remove the instruction overlay so user can click Load Replay
                                hideMapOverlay();
                            }
                        } catch (err) {
                            console.error('Error handling machine select change:', err);
                        }
                    }, 120);
                } else {
                    // If user cleared selection, show overlay again
                    showMapOverlay();
                }
            } catch (err) {
                console.error('Error in machine select change listener:', err);
            }
        });
        
        // Initialize map
        initReplayMap();
        
        // Render on initial load if data exists
        window.addEventListener('load', () => {
            if (Array.isArray(window.pathCoordinates) && window.pathCoordinates.length > 0) {
                setTimeout(renderMapElements, 500);
            }
            
            // Setup slider event listener
            const slider = document.getElementById('replay-slider');
            if (slider) {
                slider.addEventListener('input', (e) => {
                    try {
                        const position = parseInt(e.target.value, 10);
                        if (typeof position === 'number' && position >= 0) {
                            // Make sure we have the latest data
                            loadDataFromAttributes();
                            if (typeof Livewire !== 'undefined') {
                                Livewire.dispatch('replay-seek', { position: position });
                            }
                        }
                    } catch (err) {
                        console.error('Error in slider event:', err);
                    }
                });
            }
        });
    </script>
    
    <style>
        @keyframes pulse-marker {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 4px 12px rgba(59, 130, 246, 0.6);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 6px 16px rgba(59, 130, 246, 0.8);
            }
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }
            100% {
                transform: translateX(100%);
            }
        }

        .animate-shimmer {
            animation: shimmer 3s infinite;
        }

        .replay-marker {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }

        /* Custom Range Slider Styling */
        input[type="range"].slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            background: transparent;
            cursor: pointer;
        }

        input[type="range"].slider-thumb::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            cursor: grab;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.6);
            border: 3px solid white;
            transition: all 0.2s ease;
        }

        input[type="range"].slider-thumb::-webkit-slider-thumb:hover {
            transform: scale(1.2);
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.8);
        }

        input[type="range"].slider-thumb::-webkit-slider-thumb:active {
            cursor: grabbing;
            transform: scale(1.1);
        }

        input[type="range"].slider-thumb::-moz-range-thumb {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            cursor: grab;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.6);
            border: 3px solid white;
            transition: all 0.2s ease;
        }

        input[type="range"].slider-thumb::-moz-range-thumb:hover {
            transform: scale(1.2);
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.8);
        }

        input[type="range"].slider-thumb::-moz-range-thumb:active {
            cursor: grabbing;
            transform: scale(1.1);
        }
    </style>
</div>
</div>
