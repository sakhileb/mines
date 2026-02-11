<!-- Detail View -->
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

    @if($currentMineArea)
        <!-- Quick Actions Bar -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-4 mb-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <h2 class="text-lg font-semibold text-white">{{ $currentMineArea->name }}</h2>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                        @if($currentMineArea->type === 'pit') bg-amber-100 text-amber-800
                        @elseif($currentMineArea->type === 'stockpile') bg-orange-100 text-orange-800
                        @elseif($currentMineArea->type === 'dump') bg-red-100 text-red-800
                        @elseif($currentMineArea->type === 'processing') bg-blue-100 text-blue-800
                        @else bg-green-100 text-green-800
                        @endif
                    ">
                        {{ ucfirst($currentMineArea->type) }}
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a 
                        href="{{ route('mine-areas.plans', $currentMineArea) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition font-medium text-sm shadow-lg hover:shadow-xl"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Upload Mine Plan
                    </a>
                    <button 
                        wire:click="startEdit({{ $currentMineArea->id }})"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium text-sm"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Mine Plan Uploader section removed -->
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Header Card -->
                <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h1 class="text-3xl font-bold text-white">{{ $currentMineArea->name }}</h1>
                            <p class="text-gray-400 mt-2">{{ $currentMineArea->description }}</p>
                        </div>
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold
                            @if($currentMineArea->type === 'pit') bg-amber-100 text-amber-800
                            @elseif($currentMineArea->type === 'stockpile') bg-orange-100 text-orange-800
                            @elseif($currentMineArea->type === 'dump') bg-red-100 text-red-800
                            @elseif($currentMineArea->type === 'processing') bg-blue-100 text-blue-800
                            @else bg-green-100 text-green-800
                            @endif
                        ">
                            {{ ucfirst($currentMineArea->type) }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2 mt-4 pt-4 border-t border-slate-200">
                        <span class="w-3 h-3 rounded-full 
                            @if($currentMineArea->status === 'active') bg-green-600
                            @elseif($currentMineArea->status === 'inactive') bg-slate-600
                            @else bg-gray-400
                            @endif
                        "></span>
                        <span class="text-sm font-medium text-gray-300">
                            Status: <span class="text-white">{{ ucfirst($currentMineArea->status) }}</span>
                        </span>
                    </div>
                </div>

                <!-- Map View -->
                <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-white mb-4">Location Map</h2>
                    <div id="detailMap" class="w-full h-96 rounded-lg border border-gray-700 bg-gray-900"></div>
                </div>

                <!-- Statistics -->
                <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-white mb-4">Metrics</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="p-4 bg-blue-500 bg-opacity-20 border border-blue-500 border-opacity-30 rounded-lg">
                            <p class="text-xs font-medium text-blue-400 uppercase">Area</p>
                            <p class="text-2xl font-bold text-blue-200 mt-1">
                                {{ number_format($currentMineArea->area_sqm ?? 0, 0) }}
                            </p>
                            <p class="text-xs text-blue-300 mt-1">m²</p>
                        </div>
                        <div class="p-4 bg-green-500 bg-opacity-20 border border-green-500 border-opacity-30 rounded-lg">
                            <p class="text-xs font-medium text-green-400 uppercase">Perimeter</p>
                            <p class="text-2xl font-bold text-green-200 mt-1">
                                {{ number_format($currentMineArea->perimeter_m ?? 0, 0) }}
                            </p>
                            <p class="text-xs text-green-400 mt-1">m</p>
                        </div>
                        <div class="p-4 bg-purple-500 bg-opacity-20 border border-purple-500 border-opacity-30 rounded-lg">
                            <p class="text-xs font-medium text-purple-400 uppercase">Machines</p>
                            <p class="text-2xl font-bold text-purple-200 mt-1">
                                {{ $currentMineArea->machines->count() }}
                            </p>
                            <p class="text-xs text-purple-300 mt-1">assigned</p>
                        </div>
                        <div class="p-4 bg-orange-500 bg-opacity-20 border border-orange-500 border-opacity-30 rounded-lg">
                            <p class="text-xs font-medium text-orange-400 uppercase">Plans</p>
                            <p class="text-2xl font-bold text-orange-200 mt-1">
                                {{ $currentMineArea->plans->count() }}
                            </p>
                            <p class="text-xs text-orange-300 mt-1">uploaded</p>
                        </div>
                    </div>
                </div>

                <!-- Assigned Machines Section -->
                <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-white mb-4">Assigned Machines</h2>
                    @if($currentMineArea->machines->count() > 0)
                        <div class="space-y-4">
                            @foreach($currentMineArea->machines as $machine)
                                <div class="p-4 bg-gray-700 bg-opacity-50 rounded-lg border border-gray-600 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                    <div>
                                        <p class="font-medium text-white text-lg">{{ $machine->name }}</p>
                                        <p class="text-sm text-gray-400">Model: {{ $machine->model }}</p>
                                        <p class="text-sm text-gray-400">Serial: {{ $machine->serial_number ?? 'N/A' }}</p>
                                        <p class="text-sm text-gray-400">Status: <span class="font-semibold text-white">{{ ucfirst($machine->status) }}</span></p>
                                    </div>
                                    <div class="flex-1">
                                        @if($machine->production)
                                            <div class="mt-2 md:mt-0">
                                                <p class="text-sm text-blue-300 font-semibold mb-1">Production Details</p>
                                                <ul class="text-xs text-gray-200 space-y-1">
                                                    @if(isset($machine->production->today))
                                                        <li>Today: <span class="font-bold">{{ number_format($machine->production->today, 2) }}</span> {{ $machine->production->unit ?? 'tons' }}</li>
                                                    @endif
                                                    @if(isset($machine->production->week))
                                                        <li>This Week: <span class="font-bold">{{ number_format($machine->production->week, 2) }}</span> {{ $machine->production->unit ?? 'tons' }}</li>
                                                    @endif
                                                    @if(isset($machine->production->month))
                                                        <li>This Month: <span class="font-bold">{{ number_format($machine->production->month, 2) }}</span> {{ $machine->production->unit ?? 'tons' }}</li>
                                                    @endif
                                                    @if(isset($machine->production->year))
                                                        <li>This Year: <span class="font-bold">{{ number_format($machine->production->year, 2) }}</span> {{ $machine->production->unit ?? 'tons' }}</li>
                                                    @endif
                                                </ul>
                                            </div>
                                        @else
                                            <p class="text-xs text-gray-400">No production data available.</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-400">No machines assigned to this area.</div>
                    @endif
                </div>

                <!-- Plans Section -->
                <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-white">Mine Plans</h2>
                        <a 
                            href="{{ route('mine-areas.plans', $currentMineArea) }}"
                            class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition font-medium text-sm flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Upload Plan
                        </a>
                    </div>
                    
                    @if($currentMineArea->plans->count() > 0)
                        <div class="space-y-2">
                            @foreach($currentMineArea->plans->take(5) as $plan)
                                <div class="flex items-center justify-between p-3 bg-gray-700 bg-opacity-50 rounded-lg border border-gray-600">
                                    <div>
                                        <p class="font-medium text-white">{{ $plan->title ?? $plan->file_name }}</p>
                                        <p class="text-sm text-gray-400">
                                            {{ strtoupper($plan->file_type) }} • 
                                            {{ number_format($plan->file_size / 1024, 1) }} KB •
                                            {{ $plan->created_at->format('M d, Y') }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($plan->is_current)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-500 bg-opacity-20 text-blue-300 border border-blue-500">
                                                Current
                                            </span>
                                        @endif
                                        <a 
                                            href="{{ route('mine-plans.preview', $plan) }}"
                                            target="_blank"
                                            class="p-2 text-gray-400 hover:text-white transition"
                                            title="Preview"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                            
                            @if($currentMineArea->plans->count() > 5)
                                <a 
                                    href="{{ route('mine-areas.plans', $currentMineArea) }}"
                                    class="block text-center py-2 text-amber-400 hover:text-amber-300 text-sm font-medium"
                                >
                                    View all {{ $currentMineArea->plans->count() }} plans →
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-gray-400 mb-4">No mine plans uploaded yet</p>
                            <a 
                                href="{{ route('mine-areas.plans', $currentMineArea) }}"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition font-medium"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                Upload Your First Plan
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Notes -->
                @if($currentMineArea->notes)
                    <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                        <h2 class="text-xl font-semibold text-white mb-4">Notes</h2>
                        <p class="text-gray-300 whitespace-pre-wrap">{{ $currentMineArea->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Actions section removed -->

                <!-- Info Card -->
                <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                    <h3 class="font-semibold text-white mb-4">Information</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase">Created</p>
                            <p class="text-white">{{ $currentMineArea->created_at->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase">Last Updated</p>
                            <p class="text-white">{{ $currentMineArea->updated_at->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase">Center Point</p>
                            <p class="text-white font-mono text-xs">
                                {{ number_format($currentMineArea->center_latitude, 4) }}, 
                                {{ number_format($currentMineArea->center_longitude, 4) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Shifts Information -->
                @if($currentMineArea->shifts && count($currentMineArea->shifts) > 0)
                <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                    <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Active Shifts
                    </h3>
                    <div class="space-y-3">
                        @foreach($currentMineArea->shifts as $shift)
                            <div class="p-4 bg-gray-700 border border-gray-600 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-white">{{ $shift['name'] }}</p>
                                        <p class="text-sm text-gray-300 mt-1">
                                            {{ $shift['start_time'] }} - {{ $shift['end_time'] }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-1 mt-3">
                                    @foreach($shift['days'] as $day)
                                        <span class="px-2 py-1 bg-blue-500 bg-opacity-20 text-blue-300 rounded text-xs">
                                            {{ ucfirst($day) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Material Types -->
                @if($currentMineArea->material_types && count($currentMineArea->material_types) > 0)
                <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                    <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        Material Types
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($currentMineArea->material_types as $material)
                            <span class="px-3 py-2 bg-purple-500 bg-opacity-20 border border-purple-500 text-purple-300 rounded-lg font-medium">
                                {{ $material }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Mining Targets -->
                @if($currentMineArea->mining_targets)
                <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                    <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Mining Targets
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="p-4 bg-gray-700 border border-gray-600 rounded-lg text-center">
                            <p class="text-xs font-medium text-gray-400 uppercase">Daily</p>
                            <p class="text-2xl font-bold text-white mt-2">
                                {{ number_format($currentMineArea->mining_targets['daily'] ?? 0) }}
                            </p>
                        </div>
                        <div class="p-4 bg-gray-700 border border-gray-600 rounded-lg text-center">
                            <p class="text-xs font-medium text-gray-400 uppercase">Weekly</p>
                            <p class="text-2xl font-bold text-white mt-2">
                                {{ number_format($currentMineArea->mining_targets['weekly'] ?? 0) }}
                            </p>
                        </div>
                        <div class="p-4 bg-gray-700 border border-gray-600 rounded-lg text-center">
                            <p class="text-xs font-medium text-gray-400 uppercase">Monthly</p>
                            <p class="text-2xl font-bold text-white mt-2">
                                {{ number_format($currentMineArea->mining_targets['monthly'] ?? 0) }}
                            </p>
                        </div>
                        <div class="p-4 bg-gray-700 border border-gray-600 rounded-lg text-center">
                            <p class="text-xs font-medium text-gray-400 uppercase">Yearly</p>
                            <p class="text-2xl font-bold text-white mt-2">
                                {{ number_format($currentMineArea->mining_targets['yearly'] ?? 0) }}
                            </p>
                        </div>
                    </div>
                    <p class="text-center text-sm text-gray-400 mt-4">
                        Unit: {{ ucfirst(str_replace('_', ' ', $currentMineArea->mining_targets['unit'] ?? 'tonnes')) }}
                    </p>
                </div>
                @endif
            </div>
        </div>
    @else
        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-12 text-center">
            <p class="text-gray-400">Mine area not found</p>
        </div>
    @endif
</div>

<script>
console.log('🔵🔵🔵 [DETAIL MAP SCRIPT] Script tag is now executing! 🔵🔵🔵');
(function() {
    let detailMap = null;
    let initRetryCount = 0;
    const MAX_INIT_RETRIES = 100;

    function initializeDetailMap() {
        console.log('═════════════════════════════════════════════════');
        console.log('🗺️  [DETAIL MAP DEBUG] initializeDetailMap() called');
        console.log('═════════════════════════════════════════════════');
        
        // Check if Leaflet is loaded - more robust check
        console.log('[DETAIL MAP DEBUG] Checking Leaflet...');
        console.log('[DETAIL MAP DEBUG] typeof L:', typeof L);
        
        if (typeof L === 'undefined') {
            initRetryCount++;
            if (initRetryCount > MAX_INIT_RETRIES) {
                console.error('❌ [DETAIL MAP DEBUG] Leaflet failed to load after maximum retries');
                return;
            }
            console.log('⏳ [DETAIL MAP DEBUG] Leaflet not loaded yet, retry', initRetryCount);
            setTimeout(initializeDetailMap, 200);
            return;
        }
        
        console.log('✅ [DETAIL MAP DEBUG] Leaflet available, version:', L.version);

        const mineArea = @json($currentMineArea);
        const mapContainer = document.getElementById('detailMap');
        
        console.log('[DETAIL MAP DEBUG] Mine area data:', mineArea);
        console.log('[DETAIL MAP DEBUG] Map container found:', !!mapContainer);
        
        if (!mineArea || !mapContainer) {
            console.error('❌ [DETAIL MAP DEBUG] Mine area data or map container not found, retrying...');
            setTimeout(initializeDetailMap, 100);
            return;
        }
        
        console.log('✅ [DETAIL MAP DEBUG] Container dimensions:', mapContainer.offsetWidth, 'x', mapContainer.offsetHeight);

        // Clean up existing map instance if present
        if (detailMap) {
            console.log('[DETAIL MAP DEBUG] Removing existing map instance...');
            try {
                detailMap.remove();
            } catch (e) {
                console.log('[DETAIL MAP DEBUG] Error removing old map:', e);
            }
            detailMap = null;
        }

        // Clear any residual Leaflet state
        if (mapContainer._leaflet_id) {
            console.log('[DETAIL MAP DEBUG] Clearing _leaflet_id...');
            delete mapContainer._leaflet_id;
        }

        const coordinates = mineArea.coordinates || [];
        console.log('[DETAIL MAP DEBUG] Coordinates count:', coordinates.length);

        try {
            console.log('🚀 [DETAIL MAP DEBUG] Creating map instance...');
            
            // Initialize map with canvas renderer for better performance
            detailMap = L.map('detailMap', {
                preferCanvas: true,
                renderer: L.canvas()
            }).setView(
                [mineArea.center_latitude || -25.7479, mineArea.center_longitude || 28.2293], 
                13
            );
            
            // Add OpenStreetMap tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(detailMap);

            // Draw polygon boundary if we have coordinates
            if (coordinates.length > 2) {
                const latlngs = coordinates.map(c => [c[0], c[1]]);
                const polygon = L.polygon(latlngs, {
                    color: '#2563eb',
                    fillColor: '#3b82f6',
                    fillOpacity: 0.3,
                    weight: 3
                }).addTo(detailMap);

                // Add corner markers
                coordinates.forEach((coord, index) => {
                    L.circleMarker([coord[0], coord[1]], {
                        radius: 6,
                        fillColor: '#2563eb',
                        color: '#1e40af',
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 0.8
                    }).addTo(detailMap).bindPopup(`Corner ${index + 1}`);
                });

                // Fit map to show all boundaries
                detailMap.fitBounds(polygon.getBounds().pad(0.1));
            }

            // Show center point
            if (mineArea.center_latitude && mineArea.center_longitude) {
                L.circleMarker([mineArea.center_latitude, mineArea.center_longitude], {
                    radius: 8,
                    fillColor: '#ef4444',
                    color: '#dc2626',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.8
                }).addTo(detailMap).bindPopup('Center Point');
            }

            // Invalidate size to ensure proper rendering
            setTimeout(() => {
                if (detailMap) {
                    console.log('[DETAIL MAP DEBUG] Running invalidateSize...');
                    detailMap.invalidateSize();
                    console.log('✅ [DETAIL MAP DEBUG] Map size invalidated');
                }
            }, 100);

            console.log('✅✅✅ [DETAIL MAP DEBUG] Detail map initialized successfully!');
            console.log('[DETAIL MAP DEBUG] Map object:', detailMap);
        } catch (error) {
            console.error('❌❌❌ [DETAIL MAP DEBUG] CRITICAL ERROR initializing detail map!');
            console.error('[DETAIL MAP DEBUG] Error:', error);
            console.error('[DETAIL MAP DEBUG] Stack:', error.stack);
        }
        
        console.log('═════════════════════════════════════════════════');
        console.log('🏁 [DETAIL MAP DEBUG] initializeDetailMap() completed');
        console.log('═════════════════════════════════════════════════');
    }

    // Initialize on DOMContentLoaded
    console.log('[DETAIL MAP DEBUG] Setting up initialization triggers...');
    console.log('[DETAIL MAP DEBUG] document.readyState:', document.readyState);
    
    document.addEventListener('DOMContentLoaded', function() {
        console.log('[DETAIL MAP DEBUG] DOMContentLoaded event fired!');
        if (document.getElementById('detailMap') && @json($currentMineArea)) {
            console.log('[DETAIL MAP DEBUG] Container and data found, initializing...');
            initializeDetailMap();
        } else {
            console.log('[DETAIL MAP DEBUG] Container or data not found on DOMContentLoaded');
        }
    });

    // Re-initialize on Livewire navigation
    document.addEventListener('livewire:navigated', function() {
        console.log('[DETAIL MAP DEBUG] livewire:navigated event fired!');
        if (document.getElementById('detailMap') && @json($currentMineArea)) {
            console.log('[DETAIL MAP DEBUG] Re-initializing after navigation...');
            initRetryCount = 0;
            initializeDetailMap();
        }
    });

    // Also try on livewire:init
    document.addEventListener('livewire:init', function() {
        console.log('[DETAIL MAP DEBUG] livewire:init event fired!');
        if (document.getElementById('detailMap') && @json($currentMineArea)) {
            console.log('[DETAIL MAP DEBUG] Initializing after livewire:init...');
            setTimeout(initializeDetailMap, 100);
        }
    });
})();
console.log('🔵🔵🔵 [DETAIL MAP SCRIPT] Script tag finished executing! 🔵🔵🔵');
</script>
