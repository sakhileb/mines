<div class="min-h-screen bg-gray-900">
    <!-- Header -->
    <div class="bg-gray-800 border-b border-gray-700 p-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('mine-areas') }}" class="text-gray-400 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold text-white">{{ $mineArea->name }}</h1>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($mineArea->status === 'active') bg-green-900 text-green-200
                                @elseif($mineArea->status === 'inactive') bg-red-900 text-red-200
                                @else bg-yellow-900 text-yellow-200 @endif">
                                {{ ucfirst($mineArea->status) }}
                            </span>
                        </div>
                        <p class="text-gray-400 mt-1">{{ $mineArea->location ?? $mineArea->description ?? 'No description' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 text-sm text-gray-400">
                    @if($mineArea->manager_name)
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            {{ $mineArea->manager_name }}
                        </span>
                    @endif
                    @if($mineArea->area_size_hectares)
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                            {{ number_format($mineArea->area_size_hectares, 1) }} ha
                        </span>
                    @endif
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mt-6">
                <div class="bg-gray-700/50 rounded-lg p-3 text-center">
                    <p class="text-2xl font-bold text-blue-400">{{ $assignedMachines->count() }}</p>
                    <p class="text-xs text-gray-400">Machines</p>
                </div>
                <div class="bg-gray-700/50 rounded-lg p-3 text-center">
                    <p class="text-2xl font-bold text-green-400">{{ number_format($productionSummary['today'], 1) }}</p>
                    <p class="text-xs text-gray-400">Today ({{ $productionSummary['target_unit'] }})</p>
                </div>
                <div class="bg-gray-700/50 rounded-lg p-3 text-center">
                    <p class="text-2xl font-bold text-purple-400">{{ $linkedGeofences->count() }}</p>
                    <p class="text-xs text-gray-400">Geofences</p>
                </div>
                <div class="bg-gray-700/50 rounded-lg p-3 text-center">
                    <p class="text-2xl font-bold {{ $activeAlertCount > 0 ? 'text-red-400' : 'text-gray-400' }}">{{ $activeAlertCount }}</p>
                    <p class="text-xs text-gray-400">Active Alerts</p>
                </div>
                <div class="bg-gray-700/50 rounded-lg p-3 text-center">
                    <p class="text-2xl font-bold text-amber-400">{{ $minePlans->where('status', 'active')->count() }}</p>
                    <p class="text-xs text-gray-400">Active Plans</p>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="flex gap-1 mt-6 overflow-x-auto">
                @foreach(['overview' => 'Overview', 'machines' => 'Machines', 'production' => 'Production', 'plans' => 'Mine Plans', 'alerts' => 'Alerts', 'geofences' => 'Geofences'] as $tab => $label)
                    <button 
                        wire:click="setTab('{{ $tab }}')"
                        class="px-4 py-2 rounded-t-lg text-sm font-medium whitespace-nowrap transition-colors
                            {{ $activeTab === $tab ? 'bg-gray-900 text-white border-t-2 border-amber-500' : 'bg-gray-700 text-gray-400 hover:text-white hover:bg-gray-600' }}"
                    >
                        {{ $label }}
                        @if($tab === 'alerts' && $activeAlertCount > 0)
                            <span class="ml-1 px-1.5 py-0.5 text-xs bg-red-600 text-white rounded-full">{{ $activeAlertCount }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    </div>


    <!-- Map Section -->
    <div class="max-w-7xl mx-auto p-6">
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-white mb-2">Mine Area Map</h2>
            <div id="mine-area-detail-map" wire:ignore style="height: 400px; width: 100%; background: #1f2937; border-radius: 0.5rem; border: 1px solid #374151;"></div>
        </div>
    <!-- Leaflet Map Scripts -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
            (function () {
                const lat = {{ $mineArea->latitude ?? 'null' }};
                const lng = {{ $mineArea->longitude ?? 'null' }};
                const boundary = @json($mineArea->metadata['boundary_coordinates'] ?? null);

                function createMap() {
                    const mapContainer = document.getElementById('mine-area-detail-map');
                    if (!mapContainer || typeof L === 'undefined') return null;

                    // If map already exists globally, do not recreate
                    if (window._mineAreaDetailMap) return window._mineAreaDetailMap;

                    let center = [-26.2041, 28.0473];
                    let zoom = 13;
                    if (lat && lng) {
                        center = [lat, lng];
                    }

                    const map = L.map(mapContainer).setView(center, zoom);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(map);

                    if (lat && lng) {
                        L.marker([lat, lng]).addTo(map).bindPopup('Center: ' + lat.toFixed(6) + ', ' + lng.toFixed(6));
                    }

                    if (boundary && Array.isArray(boundary) && boundary.length > 2) {
                        const polygonLatLngs = boundary.map(pt => [pt.lat, pt.lng]);
                        const polygon = L.polygon(polygonLatLngs, {color: '#f59e42', fillOpacity: 0.2}).addTo(map);
                        map.fitBounds(polygon.getBounds(), {padding: [30, 30]});
                    }

                    // store instance globally so Livewire won't lose reference when DOM updates
                    window._mineAreaDetailMap = map;
                    return map;
                }

                function ensureMapVisible() {
                    const map = window._mineAreaDetailMap || createMap();
                    if (map) {
                        setTimeout(() => map.invalidateSize(), 50);
                    }
                }

                document.addEventListener('livewire:load', function () {
                    createMap();
                });

                // After any Livewire DOM update, ensure map resizes and is visible
                document.addEventListener('livewire:update', function () {
                    ensureMapVisible();
                });

                // Fallback for initial non-Livewire page load
                document.addEventListener('DOMContentLoaded', function () {
                    createMap();
                });
            })();
    </script>

        {{-- OVERVIEW TAB --}}
        @if($activeTab === 'overview')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Area Details -->
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Area Details</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between"><dt class="text-gray-400">Name</dt><dd class="text-white">{{ $mineArea->name }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-400">Location</dt><dd class="text-white">{{ $mineArea->location ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-400">Size</dt><dd class="text-white">{{ $mineArea->area_size_hectares ? number_format($mineArea->area_size_hectares, 1) . ' ha' : '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-400">Coordinates</dt><dd class="text-white">{{ $mineArea->latitude && $mineArea->longitude ? round($mineArea->latitude, 6) . ', ' . round($mineArea->longitude, 6) : '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-400">Manager</dt><dd class="text-white">{{ $mineArea->manager_name ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-400">Contact</dt><dd class="text-white">{{ $mineArea->manager_contact ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-400">Created</dt><dd class="text-white">{{ $mineArea->created_at->format('d M Y') }}</dd></div>
                    </dl>
                </div>

                <!-- Production Progress -->
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Production Progress</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-400">Monthly Target Progress</span>
                                <span class="text-white font-medium">{{ $productionSummary['target_progress'] }}%</span>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-3">
                                <div class="h-3 rounded-full transition-all duration-500 {{ $productionSummary['target_progress'] >= 100 ? 'bg-green-500' : ($productionSummary['target_progress'] >= 75 ? 'bg-blue-500' : ($productionSummary['target_progress'] >= 50 ? 'bg-yellow-500' : 'bg-red-500')) }}"
                                     style="width: {{ min($productionSummary['target_progress'], 100) }}%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>{{ number_format($productionSummary['month'], 1) }} {{ $productionSummary['target_unit'] }}</span>
                                <span>Target: {{ number_format($productionSummary['target'], 1) }} {{ $productionSummary['target_unit'] }}</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div class="bg-gray-700/50 rounded p-3 text-center">
                                <p class="text-lg font-bold text-white">{{ number_format($productionSummary['today'], 1) }}</p>
                                <p class="text-xs text-gray-400">Today</p>
                            </div>
                            <div class="bg-gray-700/50 rounded p-3 text-center">
                                <p class="text-lg font-bold text-white">{{ number_format($productionSummary['week'], 1) }}</p>
                                <p class="text-xs text-gray-400">This Week</p>
                            </div>
                            <div class="bg-gray-700/50 rounded p-3 text-center">
                                <p class="text-lg font-bold text-white">{{ number_format($productionSummary['month'], 1) }}</p>
                                <p class="text-xs text-gray-400">This Month</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assigned Machines (compact) -->
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-white">Assigned Machines</h3>
                        <button wire:click="setTab('machines')" class="text-sm text-amber-400 hover:text-amber-300">View All</button>
                    </div>
                    @if($assignedMachines->count() > 0)
                        <div class="space-y-2">
                            @foreach($assignedMachines->take(5) as $machine)
                                <div class="flex items-center justify-between bg-gray-700/50 rounded p-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-2 h-2 rounded-full {{ $machine->status === 'active' ? 'bg-green-400' : ($machine->status === 'idle' ? 'bg-yellow-400' : 'bg-red-400') }}"></div>
                                        <div>
                                            <p class="text-sm font-medium text-white">{{ $machine->name }}</p>
                                            <p class="text-xs text-gray-400">{{ ucfirst($machine->machine_type) }} &middot; {{ ucfirst($machine->status) }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            @if($assignedMachines->count() > 5)
                                <p class="text-xs text-gray-500 text-center">+{{ $assignedMachines->count() - 5 }} more</p>
                            @endif
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">No machines assigned yet</p>
                    @endif
                </div>

                <!-- Recent Alerts (compact) -->
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-white">Recent Alerts</h3>
                        <button wire:click="setTab('alerts')" class="text-sm text-amber-400 hover:text-amber-300">View All</button>
                    </div>
                    @if($areaAlerts->count() > 0)
                        <div class="space-y-2">
                            @foreach($areaAlerts->take(5) as $alert)
                                <div class="flex items-center justify-between bg-gray-700/50 rounded p-3">
                                    <div class="flex items-center gap-3">
                                        <span class="w-2 h-2 rounded-full {{ $alert->priority === 'critical' ? 'bg-red-500' : ($alert->priority === 'high' ? 'bg-orange-500' : ($alert->priority === 'medium' ? 'bg-yellow-500' : 'bg-blue-500')) }}"></span>
                                        <div>
                                            <p class="text-sm text-white">{{ $alert->title }}</p>
                                            <p class="text-xs text-gray-400">{{ $alert->triggered_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    <span class="text-xs px-2 py-0.5 rounded {{ $alert->status === 'active' ? 'bg-red-900 text-red-200' : ($alert->status === 'acknowledged' ? 'bg-yellow-900 text-yellow-200' : 'bg-green-900 text-green-200') }}">
                                        {{ ucfirst($alert->status) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">No alerts for this area</p>
                    @endif
                </div>
            </div>

        {{-- MACHINES TAB --}}
        @elseif($activeTab === 'machines')
            <div class="space-y-6">
                <!-- Actions -->
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-white">Machine Assignments</h2>
                    <button wire:click="openAssignModal" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Assign Machine
                    </button>
                </div>

                <!-- Currently Assigned -->
                <div class="bg-gray-800 rounded-lg border border-gray-700">
                    <div class="p-4 border-b border-gray-700">
                        <h3 class="font-semibold text-white">Currently Assigned ({{ $assignedMachines->count() }})</h3>
                    </div>
                    <div class="divide-y divide-gray-700">
                        @forelse($assignedMachines as $machine)
                            <div class="p-4 flex items-center justify-between hover:bg-gray-700/50 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-lg bg-blue-900 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-white">{{ $machine->name }}</p>
                                        <p class="text-sm text-gray-400">{{ ucfirst($machine->machine_type) }} &middot; {{ $machine->model ?? 'N/A' }} &middot; SN: {{ $machine->serial_number ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                        {{ $machine->status === 'active' ? 'bg-green-900 text-green-200' : ($machine->status === 'idle' ? 'bg-yellow-900 text-yellow-200' : ($machine->status === 'maintenance' ? 'bg-orange-900 text-orange-200' : 'bg-red-900 text-red-200')) }}">
                                        {{ ucfirst($machine->status) }}
                                    </span>
                                    <button 
                                        wire:click="unassignMachine({{ $machine->id }})"
                                        wire:confirm="Remove {{ $machine->name }} from this area?"
                                        class="text-red-400 hover:text-red-300 text-sm"
                                    >
                                        Unassign
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-gray-500">
                                <p>No machines assigned to this area yet.</p>
                                <button wire:click="openAssignModal" class="mt-2 text-blue-400 hover:text-blue-300 text-sm">Assign a machine</button>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Assignment History -->
                <div class="bg-gray-800 rounded-lg border border-gray-700">
                    <div class="p-4 border-b border-gray-700">
                        <h3 class="font-semibold text-white">Assignment History</h3>
                    </div>
                    <div class="divide-y divide-gray-700">
                        @forelse($assignmentHistory as $assignment)
                            <div class="p-4 flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-white">
                                        <span class="font-medium">{{ $assignment->machine->name ?? 'Unknown' }}</span>
                                        {{ $assignment->unassigned_at ? 'was assigned' : 'assigned' }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ $assignment->assigned_at->format('d M Y H:i') }}
                                        @if($assignment->unassigned_at) &mdash; {{ $assignment->unassigned_at->format('d M Y H:i') }} @endif
                                        @if($assignment->assignedByUser) &middot; by {{ $assignment->assignedByUser->name }} @endif
                                    </p>
                                    @if($assignment->reason)
                                        <p class="text-xs text-gray-500 mt-1">Reason: {{ $assignment->reason }}</p>
                                    @endif
                                </div>
                                <span class="text-xs px-2 py-0.5 rounded {{ $assignment->unassigned_at ? 'bg-gray-700 text-gray-400' : 'bg-green-900 text-green-200' }}">
                                    {{ $assignment->unassigned_at ? 'Completed' : 'Active' }}
                                </span>
                            </div>
                        @empty
                            <div class="p-6 text-center text-gray-500 text-sm">No assignment history</div>
                        @endforelse
                    </div>
                </div>
            </div>

        {{-- PRODUCTION TAB --}}
        @elseif($activeTab === 'production')
            <div class="space-y-6">
                <!-- Actions -->
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-white">Production Tracking</h2>
                    <div class="flex gap-2">
                        <button wire:click="openTargetModal" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            Set Target
                        </button>
                        <button wire:click="openProductionModal" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Add Record
                        </button>
                    </div>
                </div>

                <!-- Production Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-gray-800 rounded-lg border border-gray-700 p-4">
                        <p class="text-gray-400 text-sm">Today</p>
                        <p class="text-2xl font-bold text-white">{{ number_format($productionSummary['today'], 1) }}</p>
                        <p class="text-xs text-gray-500">{{ $productionSummary['target_unit'] }}</p>
                    </div>
                    <div class="bg-gray-800 rounded-lg border border-gray-700 p-4">
                        <p class="text-gray-400 text-sm">This Week</p>
                        <p class="text-2xl font-bold text-white">{{ number_format($productionSummary['week'], 1) }}</p>
                        <p class="text-xs text-gray-500">{{ $productionSummary['target_unit'] }}</p>
                    </div>
                    <div class="bg-gray-800 rounded-lg border border-gray-700 p-4">
                        <p class="text-gray-400 text-sm">This Month</p>
                        <p class="text-2xl font-bold text-white">{{ number_format($productionSummary['month'], 1) }}</p>
                        <p class="text-xs text-gray-500">{{ $productionSummary['target_unit'] }}</p>
                    </div>
                    <div class="bg-gray-800 rounded-lg border border-gray-700 p-4">
                        <p class="text-gray-400 text-sm">Monthly Target</p>
                        <p class="text-2xl font-bold {{ $productionSummary['target_progress'] >= 100 ? 'text-green-400' : 'text-amber-400' }}">{{ $productionSummary['target_progress'] }}%</p>
                        <div class="w-full bg-gray-700 rounded-full h-2 mt-2">
                            <div class="h-2 rounded-full {{ $productionSummary['target_progress'] >= 100 ? 'bg-green-500' : 'bg-amber-500' }}" style="width: {{ min($productionSummary['target_progress'], 100) }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Active Targets -->
                @if($activeTargets->count() > 0)
                    <div class="bg-gray-800 rounded-lg border border-gray-700">
                        <div class="p-4 border-b border-gray-700">
                            <h3 class="font-semibold text-white">Active Targets</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-sm text-gray-300">Period</th>
                                        <th class="px-4 py-2 text-left text-sm text-gray-300">Date Range</th>
                                        <th class="px-4 py-2 text-right text-sm text-gray-300">Target</th>
                                        <th class="px-4 py-2 text-left text-sm text-gray-300">Description</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700">
                                    @foreach($activeTargets as $target)
                                        <tr class="hover:bg-gray-700/50">
                                            <td class="px-4 py-3 text-white text-sm">{{ ucfirst($target->period_type) }}</td>
                                            <td class="px-4 py-3 text-gray-300 text-sm">{{ $target->start_date->format('d M') }} - {{ $target->end_date->format('d M Y') }}</td>
                                            <td class="px-4 py-3 text-white text-sm text-right font-medium">{{ number_format($target->target_quantity, 1) }} {{ $target->unit }}</td>
                                            <td class="px-4 py-3 text-gray-400 text-sm">{{ $target->description ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Production Records Table -->
                <div class="bg-gray-800 rounded-lg border border-gray-700">
                    <div class="p-4 border-b border-gray-700">
                        <h3 class="font-semibold text-white">Production Records</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left text-sm text-gray-300">Date</th>
                                    <th class="px-4 py-2 text-left text-sm text-gray-300">Shift</th>
                                    <th class="px-4 py-2 text-left text-sm text-gray-300">Machine</th>
                                    <th class="px-4 py-2 text-right text-sm text-gray-300">Produced</th>
                                    <th class="px-4 py-2 text-right text-sm text-gray-300">Target</th>
                                    <th class="px-4 py-2 text-right text-sm text-gray-300">Variance</th>
                                    <th class="px-4 py-2 text-left text-sm text-gray-300">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                @forelse($productionRecords as $record)
                                    <tr class="hover:bg-gray-700/50">
                                        <td class="px-4 py-3 text-white text-sm">{{ $record->record_date->format('d M Y') }}</td>
                                        <td class="px-4 py-3 text-gray-300 text-sm">{{ ucfirst($record->shift) }}</td>
                                        <td class="px-4 py-3 text-gray-300 text-sm">{{ $record->machine->name ?? '-' }}</td>
                                        <td class="px-4 py-3 text-white text-sm text-right font-medium">{{ number_format($record->quantity_produced, 1) }} {{ $record->unit }}</td>
                                        <td class="px-4 py-3 text-gray-400 text-sm text-right">{{ $record->target_quantity ? number_format($record->target_quantity, 1) : '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-right">
                                            @if($record->target_quantity)
                                                <span class="{{ $record->is_above_target ? 'text-green-400' : 'text-red-400' }}">
                                                    {{ $record->is_above_target ? '+' : '' }}{{ number_format($record->variance_percentage, 1) }}%
                                                </span>
                                            @else
                                                <span class="text-gray-500">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="px-2 py-0.5 rounded text-xs {{ $record->status === 'completed' ? 'bg-green-900 text-green-200' : 'bg-yellow-900 text-yellow-200' }}">
                                                {{ ucfirst($record->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">No production records yet</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-gray-700">
                        {{ $productionRecords->links() }}
                    </div>
                </div>
            </div>

        {{-- MINE PLANS TAB --}}
        @elseif($activeTab === 'plans')
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-white">Mine Plan Uploads</h2>
                    <button wire:click="openUploadModal" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        Upload Plan
                    </button>
                </div>

                <!-- Plans Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($minePlans as $plan)
                        <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden hover:border-gray-600 transition-colors">
                            <!-- File Type Icon -->
                            <div class="p-4 bg-gray-700/50 flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg flex items-center justify-center
                                    {{ $plan->file_type === 'pdf' ? 'bg-red-900' : ($plan->is_image ? 'bg-blue-900' : ($plan->file_type === 'kml' || $plan->file_type === 'kmz' ? 'bg-green-900' : 'bg-purple-900')) }}">
                                    @if($plan->file_type === 'pdf')
                                        <svg class="w-6 h-6 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    @elseif($plan->is_image)
                                        <svg class="w-6 h-6 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    @else
                                        <svg class="w-6 h-6 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-white font-medium truncate">{{ $plan->title }}</p>
                                    <p class="text-xs text-gray-400">{{ strtoupper($plan->file_type) }} &middot; {{ $plan->formatted_file_size }}</p>
                                </div>
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    {{ $plan->status === 'active' ? 'bg-green-900 text-green-200' : ($plan->status === 'draft' ? 'bg-yellow-900 text-yellow-200' : ($plan->status === 'superseded' ? 'bg-gray-600 text-gray-300' : 'bg-gray-700 text-gray-400')) }}">
                                    {{ ucfirst($plan->status) }}
                                </span>
                            </div>

                            <!-- Details -->
                            <div class="p-4 space-y-2">
                                @if($plan->description)
                                    <p class="text-sm text-gray-400">{{ Str::limit($plan->description, 100) }}</p>
                                @endif
                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <span>v{{ $plan->version }}</span>
                                    @if($plan->effective_date)
                                        <span>Effective: {{ $plan->effective_date->format('d M Y') }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">
                                    Uploaded by {{ $plan->uploader->name ?? 'Unknown' }} &middot; {{ $plan->created_at->diffForHumans() }}
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-2 pt-2 border-t border-gray-700">
                                    <a href="{{ Storage::url($plan->file_path) }}" target="_blank" class="text-xs text-blue-400 hover:text-blue-300">Download</a>
                                    @if($plan->status === 'draft')
                                        <button wire:click="activateMinePlan({{ $plan->id }})" class="text-xs text-green-400 hover:text-green-300">Activate</button>
                                    @endif
                                    @if($plan->status !== 'archived')
                                        <button wire:click="archiveMinePlan({{ $plan->id }})" class="text-xs text-yellow-400 hover:text-yellow-300">Archive</button>
                                    @endif
                                    <button wire:click="deleteMinePlan({{ $plan->id }})" wire:confirm="Delete this mine plan?" class="text-xs text-red-400 hover:text-red-300 ml-auto">Delete</button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-3 bg-gray-800 rounded-lg border border-gray-700 p-8 text-center">
                            <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            <p class="text-gray-500">No mine plans uploaded yet</p>
                            <button wire:click="openUploadModal" class="mt-2 text-amber-400 hover:text-amber-300 text-sm">Upload your first plan</button>
                        </div>
                    @endforelse
                </div>
            </div>

        {{-- ALERTS TAB --}}
        @elseif($activeTab === 'alerts')
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-white">Area Alerts</h2>
                    <button wire:click="openAlertModal" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Create Alert
                    </button>
                </div>

                <!-- Alert Stats -->
                <div class="grid grid-cols-4 gap-4">
                    <div class="bg-gray-800 rounded-lg border border-red-900 p-4 text-center">
                        <p class="text-2xl font-bold text-red-400">{{ $areaAlerts->where('priority', 'critical')->where('status', 'active')->count() }}</p>
                        <p class="text-xs text-gray-400">Critical</p>
                    </div>
                    <div class="bg-gray-800 rounded-lg border border-orange-900 p-4 text-center">
                        <p class="text-2xl font-bold text-orange-400">{{ $areaAlerts->where('priority', 'high')->where('status', 'active')->count() }}</p>
                        <p class="text-xs text-gray-400">High</p>
                    </div>
                    <div class="bg-gray-800 rounded-lg border border-yellow-900 p-4 text-center">
                        <p class="text-2xl font-bold text-yellow-400">{{ $areaAlerts->where('priority', 'medium')->where('status', 'active')->count() }}</p>
                        <p class="text-xs text-gray-400">Medium</p>
                    </div>
                    <div class="bg-gray-800 rounded-lg border border-blue-900 p-4 text-center">
                        <p class="text-2xl font-bold text-blue-400">{{ $areaAlerts->where('priority', 'low')->where('status', 'active')->count() }}</p>
                        <p class="text-xs text-gray-400">Low</p>
                    </div>
                </div>

                <!-- Alerts List -->
                <div class="bg-gray-800 rounded-lg border border-gray-700">
                    <div class="divide-y divide-gray-700">
                        @forelse($areaAlerts as $alert)
                            <div class="p-4 flex items-center justify-between hover:bg-gray-700/50 transition-colors">
                                <div class="flex items-center gap-4">
                                    <span class="w-3 h-3 rounded-full flex-shrink-0
                                        {{ $alert->priority === 'critical' ? 'bg-red-500 animate-pulse' : ($alert->priority === 'high' ? 'bg-orange-500' : ($alert->priority === 'medium' ? 'bg-yellow-500' : 'bg-blue-500')) }}"></span>
                                    <div>
                                        <p class="text-white font-medium">{{ $alert->title }}</p>
                                        <p class="text-sm text-gray-400">{{ $alert->description ?? '' }}</p>
                                        <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                                            <span>{{ ucfirst($alert->type) }}</span>
                                            <span>&middot;</span>
                                            <span>{{ ucfirst($alert->priority) }}</span>
                                            <span>&middot;</span>
                                            <span>{{ $alert->triggered_at->diffForHumans() }}</span>
                                            @if($alert->machine)
                                                <span>&middot;</span>
                                                <span>Machine: {{ $alert->machine->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="px-2 py-1 text-xs rounded {{ $alert->status === 'active' ? 'bg-red-900 text-red-200' : ($alert->status === 'acknowledged' ? 'bg-yellow-900 text-yellow-200' : 'bg-green-900 text-green-200') }}">
                                        {{ ucfirst($alert->status) }}
                                    </span>
                                    @if($alert->status === 'active')
                                        <button wire:click="acknowledgeAlert({{ $alert->id }})" class="text-xs text-yellow-400 hover:text-yellow-300 px-2 py-1 bg-gray-700 rounded">
                                            Acknowledge
                                        </button>
                                    @endif
                                    @if($alert->status !== 'resolved')
                                        <button wire:click="resolveAlert({{ $alert->id }})" class="text-xs text-green-400 hover:text-green-300 px-2 py-1 bg-gray-700 rounded">
                                            Resolve
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-gray-500">
                                <p>No alerts for this area</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        {{-- GEOFENCES TAB --}}
        @elseif($activeTab === 'geofences')
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-white">Geofence Integration</h2>
                    <button wire:click="openGeofenceModal" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                        Link Geofence
                    </button>
                </div>

                <!-- Linked Geofences -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($linkedGeofences as $geofence)
                        <div class="bg-gray-800 rounded-lg border border-gray-700 p-4 hover:border-purple-700 transition-colors">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-purple-900 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-white font-medium">{{ $geofence->name }}</p>
                                        <p class="text-xs text-gray-400">{{ ucfirst($geofence->type ?? 'zone') }}</p>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 text-xs rounded {{ $geofence->status === 'active' ? 'bg-green-900 text-green-200' : 'bg-gray-700 text-gray-400' }}">
                                    {{ ucfirst($geofence->status) }}
                                </span>
                            </div>

                            <div class="space-y-2 text-sm">
                                @if($geofence->description)
                                    <p class="text-gray-400">{{ Str::limit($geofence->description, 80) }}</p>
                                @endif
                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <span>{{ $geofence->entries_count ?? 0 }} total entries</span>
                                    @if($geofence->area_sqm)
                                        <span>{{ number_format($geofence->area_sqm / 10000, 2) }} ha</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-700">
                                <a href="{{ route('geofences.show', $geofence->id) }}" class="text-xs text-blue-400 hover:text-blue-300">View Details</a>
                                <button 
                                    wire:click="unlinkGeofence({{ $geofence->id }})"
                                    wire:confirm="Unlink {{ $geofence->name }} from this area?"
                                    class="text-xs text-red-400 hover:text-red-300"
                                >
                                    Unlink
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-3 bg-gray-800 rounded-lg border border-gray-700 p-8 text-center">
                            <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                            <p class="text-gray-500">No geofences linked to this area</p>
                            <button wire:click="openGeofenceModal" class="mt-2 text-purple-400 hover:text-purple-300 text-sm">Link a geofence</button>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>

    {{-- ============ MODALS ============ --}}

    <!-- Assign Machine Modal -->
    @if($showAssignModal)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
            <div class="bg-gray-800 rounded-lg shadow-xl max-w-lg w-full border border-gray-700 text-gray-100">
                <div class="p-6 border-b border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-100">Assign Machine to {{ $mineArea->name }}</h2>
                    <button wire:click="closeAssignModal" class="text-gray-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form wire:submit="assignMachine" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Select Machine <span class="text-red-500">*</span></label>
                        <select wire:model="selectedMachineId" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="">-- Choose a machine --</option>
                            @foreach($availableMachines as $machine)
                                <option value="{{ $machine->id }}">
                                    {{ $machine->name }} ({{ ucfirst($machine->machine_type) }} - {{ ucfirst($machine->status) }})
                                    @if($machine->mine_area_id) [Currently: {{ $machine->mineArea->name ?? 'Other' }}] @endif
                                </option>
                            @endforeach
                        </select>
                        @error('selectedMachineId') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Reason for Assignment</label>
                        <input type="text" wire:model="assignmentReason" placeholder="e.g., Scheduled rotation, increased production demand..." class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div class="flex gap-3 pt-3 border-t border-gray-700">
                        <button type="button" wire:click="closeAssignModal" class="flex-1 px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600">Cancel</button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">Assign Machine</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Production Record Modal -->
    @if($showProductionModal)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
            <div class="bg-gray-800 rounded-lg shadow-xl max-w-lg w-full border border-gray-700">
                <div class="p-6 border-b border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-white">Add Production Record</h2>
                    <button wire:click="closeProductionModal" class="text-gray-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form wire:submit="saveProductionRecord" class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Date <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="productionDate" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-green-500 outline-none">
                            @error('productionDate') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Shift</label>
                            <select wire:model="productionShift" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-green-500 outline-none">
                                <option value="day">Day Shift</option>
                                <option value="night">Night Shift</option>
                                <option value="continuous">Continuous</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Machine (optional)</label>
                        <select wire:model="productionMachineId" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-green-500 outline-none">
                            <option value="">-- Area total --</option>
                            @foreach($assignedMachines as $machine)
                                <option value="{{ $machine->id }}">{{ $machine->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Produced <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" wire:model="quantityProduced" placeholder="0.00" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-green-500 outline-none">
                            @error('quantityProduced') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Target</label>
                            <input type="number" step="0.01" wire:model="targetQuantity" placeholder="0.00" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-green-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Unit</label>
                            <select wire:model="productionUnit" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-green-500 outline-none">
                                <option value="tonnes">Tonnes</option>
                                <option value="cubic_meters">m³</option>
                                <option value="loads">Loads</option>
                                <option value="trips">Trips</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Notes</label>
                        <textarea wire:model="productionNotes" rows="2" placeholder="Any additional notes..." class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-green-500 outline-none"></textarea>
                    </div>
                    <div class="flex gap-3 pt-3 border-t border-gray-700">
                        <button type="button" wire:click="closeProductionModal" class="flex-1 px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600">Cancel</button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">Save Record</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Production Target Modal -->
    @if($showTargetModal)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
            <div class="bg-gray-800 rounded-lg shadow-xl max-w-lg w-full border border-gray-700">
                <div class="p-6 border-b border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-white">Set Production Target</h2>
                    <button wire:click="closeTargetModal" class="text-gray-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form wire:submit="saveProductionTarget" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Period Type</label>
                        <select wire:model="targetPeriodType" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-purple-500 outline-none">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Start Date <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="targetStartDate" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-purple-500 outline-none">
                            @error('targetStartDate') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">End Date <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="targetEndDate" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-purple-500 outline-none">
                            @error('targetEndDate') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Target Quantity <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" wire:model="targetValue" placeholder="0.00" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-purple-500 outline-none">
                            @error('targetValue') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Unit</label>
                            <select wire:model="targetUnit" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-purple-500 outline-none">
                                <option value="tonnes">Tonnes</option>
                                <option value="cubic_meters">m³</option>
                                <option value="loads">Loads</option>
                                <option value="trips">Trips</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Description</label>
                        <input type="text" wire:model="targetDescription" placeholder="e.g., Q1 production target for gold ore..." class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-purple-500 outline-none">
                    </div>
                    <div class="flex gap-3 pt-3 border-t border-gray-700">
                        <button type="button" wire:click="closeTargetModal" class="flex-1 px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600">Cancel</button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium">Set Target</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Upload Mine Plan Modal -->
    @if($showUploadModal)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
            <div class="bg-gray-800 rounded-lg shadow-xl max-w-lg w-full border border-gray-700">
                <div class="p-6 border-b border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-white">Upload Mine Plan</h2>
                    <button wire:click="closeUploadModal" class="text-gray-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form wire:submit="uploadMinePlan" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Title <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="planTitle" placeholder="e.g., North Pit Phase 3 Extraction Plan" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-amber-500 outline-none">
                        @error('planTitle') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Description</label>
                        <textarea wire:model="planDescription" rows="2" placeholder="Describe this mine plan..." class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-amber-500 outline-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">File <span class="text-red-500">*</span></label>
                        <input type="file" wire:model="planFile" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-amber-900 file:text-amber-200 hover:file:bg-amber-800">
                        <p class="text-xs text-gray-500 mt-1">Supported: PDF, DWG, DXF, KML, KMZ, Shapefiles, Images (max 50MB)</p>
                        @error('planFile') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Version</label>
                            <input type="text" wire:model="planVersion" placeholder="1.0" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Status</label>
                            <select wire:model="planStatus" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-amber-500 outline-none">
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Effective Date</label>
                            <input type="date" wire:model="planEffectiveDate" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>
                    </div>
                    <div class="flex gap-3 pt-3 border-t border-gray-700">
                        <button type="button" wire:click="closeUploadModal" class="flex-1 px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600">Cancel</button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 font-medium">
                            <span wire:loading.remove wire:target="uploadMinePlan">Upload Plan</span>
                            <span wire:loading wire:target="planFile,uploadMinePlan">Uploading...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Create Area Alert Modal -->
    @if($showAlertModal)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
            <div class="bg-gray-800 rounded-lg shadow-xl max-w-lg w-full border border-gray-700">
                <div class="p-6 border-b border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-white">Create Area Alert</h2>
                    <button wire:click="closeAlertModal" class="text-gray-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form wire:submit="createAreaAlert" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Alert Title <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="alertTitle" placeholder="e.g., Slope instability detected in Zone B" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-red-500 outline-none">
                        @error('alertTitle') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Description</label>
                        <textarea wire:model="alertDescription" rows="3" placeholder="Provide details about the alert..." class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-red-500 outline-none"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Type</label>
                            <select wire:model="alertType" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-red-500 outline-none">
                                <option value="area">Area</option>
                                <option value="safety">Safety</option>
                                <option value="environmental">Environmental</option>
                                <option value="production">Production</option>
                                <option value="geofence">Geofence</option>
                                <option value="blasting">Blasting</option>
                                <option value="ground_stability">Ground Stability</option>
                                <option value="weather">Weather</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Priority</label>
                            <select wire:model="alertPriority" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-red-500 outline-none">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-3 pt-3 border-t border-gray-700">
                        <button type="button" wire:click="closeAlertModal" class="flex-1 px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600">Cancel</button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">Create Alert</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Link Geofence Modal -->
    @if($showGeofenceModal)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
            <div class="bg-gray-800 rounded-lg shadow-xl max-w-lg w-full border border-gray-700">
                <div class="p-6 border-b border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-white">Link Geofence to {{ $mineArea->name }}</h2>
                    <button wire:click="closeGeofenceModal" class="text-gray-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form wire:submit="linkGeofence" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Select Geofence <span class="text-red-500">*</span></label>
                        <select wire:model="selectedGeofenceId" class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-purple-500 outline-none">
                            <option value="">-- Choose a geofence --</option>
                            @foreach($availableGeofences as $geofence)
                                <option value="{{ $geofence->id }}">
                                    {{ $geofence->name }} ({{ ucfirst($geofence->type ?? 'zone') }} - {{ ucfirst($geofence->status) }})
                                </option>
                            @endforeach
                        </select>
                        @error('selectedGeofenceId') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    @if($availableGeofences->isEmpty())
                        <div class="bg-yellow-900/30 border border-yellow-700 rounded-lg p-3">
                            <p class="text-yellow-300 text-sm">No available geofences to link. Create geofences first from the Geofences page.</p>
                        </div>
                    @endif
                    <div class="flex gap-3 pt-3 border-t border-gray-700">
                        <button type="button" wire:click="closeGeofenceModal" class="flex-1 px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600">Cancel</button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium" {{ $availableGeofences->isEmpty() ? 'disabled' : '' }}>Link Geofence</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
