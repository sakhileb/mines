<div>
    <style>
        #map {
            min-height: 384px;
            height: 384px !important;
            width: 100% !important;
            position: relative;
            z-index: 10 !important;
            display: block !important;
            background-color: #1f2937 !important;
            border-radius: 0.5rem;
        }
        
        #map * {
            box-sizing: border-box;
        }
        
        .leaflet-container {
            background: #1f2937 !important;
            font-family: inherit;
            height: 100% !important;
            width: 100% !important;
            position: relative !important;
            z-index: 10 !important;
        }
        
        .leaflet-popup-content-wrapper {
            background-color: #374151;
            color: #f3f4f6;
        }
        
        .leaflet-popup-tip {
            background-color: #374151;
        }
        
        /* Ensure map parent doesn't hide the map */
        #map-status {
            z-index: 5 !important;
        }
        
        .leaflet-pane {
            z-index: 11 !important;
        }
        
        .leaflet-tile-pane {
            z-index: 2 !important;
        }
        
        .leaflet-marker-pane {
            z-index: 6 !important;
        }
        
        .leaflet-popup-pane {
            z-index: 7 !important;
        }
    </style>

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
                <!-- Basic Information Card -->
                <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-white mb-4">Basic Information</h2>
                    
                    <div class="space-y-4">
                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Name *</label>
                            <input 
                                type="text" 
                                wire:model="name"
                                placeholder="e.g., North Pit A"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900
                                    @error('name') border-red-500 @else border-slate-300 @enderror"
                            />
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Type *</label>
                            <select 
                                wire:model="type"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900
                                    @error('type') border-red-500 @else border-slate-300 @enderror"
                            >
                                <option value="">Select a type</option>
                                <option value="pit">Pit</option>
                                <option value="stockpile">Stockpile</option>
                                <option value="dump">Dump</option>
                                <option value="processing">Processing Facility</option>
                                <option value="facility">Facility</option>
                            </select>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                            <textarea 
                                wire:model="description"
                                placeholder="Describe this mine area..."
                                rows="3"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900"
                            ></textarea>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Notes</label>
                            <textarea 
                                wire:model="notes"
                                placeholder="Additional notes..."
                                rows="2"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Coordinates Card -->
                <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-white">Location</h2>
                        @if(count($coordinates) < 4)
                            <button 
                                wire:click="toggleDrawing"
                                class="px-4 py-2 rounded-lg font-medium transition flex items-center gap-2
                                    @if($isDrawing) 
                                        bg-red-600 text-white hover:bg-red-700 shadow-lg ring-2 ring-red-400
                                    @else 
                                        bg-blue-600 text-white hover:bg-blue-700
                                    @endif
                                "
                            >
                                @if($isDrawing)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Stop Drawing
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path>
                                    </svg>
                                    {{ count($coordinates) === 0 ? 'Start Drawing on Map' : 'Add More Points' }}
                                @endif
                            </button>
                        @else
                            <span class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                All 4 Points Added
                            </span>
                        @endif
                    </div>

                    <!-- Drawing Instructions -->
                    @if($isDrawing)
                        <div class="mb-4 p-3 bg-blue-500 bg-opacity-20 border border-blue-500 border-opacity-50 rounded-lg animate-pulse">
                            <p class="text-sm text-blue-300 font-medium flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path>
                                </svg>
                                Click anywhere on the map to add a coordinate point ({{ 4 - count($coordinates) }} remaining)
                            </p>
                        </div>
                    @else
                        <p class="text-sm text-gray-400 mb-4">
                            📍 Enter exactly 4 GPS coordinates to define the mine area boundary. Click "Start Drawing" to add points by clicking on the map.
                        </p>
                    @endif

                    <!-- Map Container -->
                    <div id="map-status" class="mb-2 p-2 bg-gray-700 rounded text-xs text-gray-300 font-mono">
                        Initializing map...
                    </div>
                    
                    <div 
                        id="map" 
                        wire:ignore 
                        data-map-version="2.0"
                        class="w-full rounded-lg border mb-4 bg-gray-900 transition-all
                        @if($isDrawing) 
                            border-blue-500 shadow-lg shadow-blue-500/30 ring-2 ring-blue-400
                        @else 
                            border-gray-700
                        @endif
        "
        style="height: 384px;"
    ></div>

                    <!-- Coordinates List -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <h3 class="font-medium text-white">Coordinates ({{ count($coordinates) }} / 4 Required)</h3>
                            @if(count($coordinates) > 0)
                                <button 
                                    wire:click="clearCoordinates"
                                    wire:confirm="Clear all coordinates?"
                                    class="text-sm text-red-600 hover:text-red-800 font-medium transition"
                                >
                                    Clear All
                                </button>
                            @endif
                        </div>

                        @if(count($coordinates) > 0)
                            <div class="max-h-48 overflow-y-auto border border-gray-700 rounded-lg divide-y divide-gray-700">
                                @foreach($coordinates as $index => $coord)
                                    <div class="flex items-center justify-between p-3 hover:bg-gray-700">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-white">Point {{ $index + 1 }}</p>
                                            <p class="text-xs text-gray-400">{{ $coord['lat'] }}, {{ $coord['lon'] }}</p>
                                        </div>
                                        <button 
                                            wire:click="removeCoordinate({{ $index }})"
                                            class="text-red-600 hover:text-red-800 transition ml-2"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Area and Perimeter Info -->
                            @if(count($coordinates) === 4)
                                <div class="mt-4 p-3 bg-green-500 bg-opacity-20 border border-green-500 border-opacity-30 rounded-lg">
                                    <p class="text-sm text-green-300">
                                        <strong>✓ Complete:</strong> All 4 corner points defined. Area boundary is ready!
                                    </p>
                                </div>
                            @elseif(count($coordinates) === 3)
                                <div class="mt-4 p-3 bg-amber-500 bg-opacity-20 border border-amber-500 border-opacity-30 rounded-lg">
                                    <p class="text-sm text-amber-300">
                                        <strong>3 of 4 Points:</strong> Add 1 more coordinate to complete the boundary
                                    </p>
                                </div>
                            @elseif(count($coordinates) === 2)
                                <div class="mt-4 p-3 bg-amber-500 bg-opacity-20 border border-amber-500 border-opacity-30 rounded-lg">
                                    <p class="text-sm text-amber-300">
                                        <strong>2 of 4 Points:</strong> Add 2 more coordinates to complete the boundary
                                    </p>
                                </div>
                            @elseif(count($coordinates) === 1)
                                <div class="mt-4 p-3 bg-amber-500 bg-opacity-20 border border-amber-500 border-opacity-30 rounded-lg">
                                    <p class="text-sm text-amber-300">
                                        <strong>1 of 4 Points:</strong> Add 3 more coordinates to complete the boundary
                                    </p>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-6 text-slate-500">
                                <p class="text-sm">No coordinates yet. Add exactly 4 points to define the area boundary.</p>
                                <p class="text-xs text-slate-600 mt-1">Click on the map or enter coordinates manually below.</p>
                            </div>
                        @endif

                        @error('coordinates')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Manual Coordinate Input -->
                    <div class="mt-4 pt-4 border-t border-gray-700">
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Enter GPS Coordinates
                            <span class="text-xs text-gray-500">(e.g., -25.7479, 28.1872 for Pretoria)</span>
                        </label>
                        <div class="flex gap-2">
                            <input 
                                type="number" 
                                step="0.000001" 
                                wire:model.live="tempLat"
                                placeholder="Latitude (e.g., -25.7479)"
                                class="flex-1 px-4 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                            />
                            <input 
                                type="number" 
                                step="0.000001" 
                                wire:model.live="tempLon"
                                placeholder="Longitude (e.g., 28.1872)"
                                class="flex-1 px-4 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                            />
                            <button 
                                wire:click="addCoordinate"
                                @if(count($coordinates) >= 4) disabled @endif
                                class="px-6 py-2 text-white rounded-lg transition font-medium flex items-center gap-2
                                    @if(count($coordinates) >= 4) 
                                        bg-gray-600 cursor-not-allowed opacity-50
                                    @else 
                                        bg-blue-600 hover:bg-blue-700
                                    @endif
                                "
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                {{ count($coordinates) >= 4 ? 'Max Reached' : 'Add' }}
                            </button>
                        </div>
                        <p class="text-xs text-amber-400 mt-2">
                            💡 Tip: You need exactly 4 coordinates to define a complete mine area boundary. The map updates in real-time as you add points!
                        </p>
                    </div>
                </div>

                <!-- Shifts Configuration Card -->
                <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-white">Shifts Configuration</h2>
                        <button 
                            wire:click="openShiftModal"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium text-sm"
                        >
                            + Add Shift
                        </button>
                    </div>

                    @if(count($shifts) > 0)
                        <div class="space-y-3">
                            @foreach($shifts as $index => $shift)
                                <div class="border border-gray-600 rounded-lg p-4 bg-gray-700">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h3 class="font-medium text-white">{{ $shift['name'] }}</h3>
                                            <p class="text-sm text-gray-300 mt-1">
                                                {{ $shift['start_time'] }} - {{ $shift['end_time'] }}
                                            </p>
                                            <div class="flex flex-wrap gap-1 mt-2">
                                                @foreach($shift['days'] as $day)
                                                    <span class="px-2 py-1 bg-blue-500 bg-opacity-20 text-blue-300 rounded text-xs">
                                                        {{ ucfirst($day) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="flex gap-2 ml-4">
                                            <button 
                                                wire:click="editShift({{ $index }})"
                                                class="p-2 text-blue-400 hover:text-blue-300 transition"
                                                title="Edit"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button 
                                                wire:click="deleteShift({{ $index }})"
                                                wire:confirm="Are you sure you want to delete this shift?"
                                                class="p-2 text-red-400 hover:text-red-300 transition"
                                                title="Delete"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 text-gray-400 border border-gray-600 rounded-lg bg-gray-700">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm">No shifts configured yet</p>
                            <button 
                                wire:click="openShiftModal"
                                class="mt-2 text-blue-400 hover:text-blue-300 text-sm"
                            >
                                Add your first shift
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Material Types Card -->
                <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-white">Material Types</h2>
                        <button 
                            wire:click="openMaterialModal"
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-medium text-sm"
                        >
                            + Add Material
                        </button>
                    </div>

                    @if(count($material_types) > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($material_types as $index => $material)
                                <div class="flex items-center gap-2 px-3 py-2 bg-purple-500 bg-opacity-20 border border-purple-500 text-purple-300 rounded-lg">
                                    <span>{{ $material }}</span>
                                    <button 
                                        wire:click="removeMaterialType({{ $index }})"
                                        class="text-purple-400 hover:text-purple-200 transition"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 text-gray-400 border border-gray-600 rounded-lg bg-gray-700">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            <p class="text-sm">No material types allocated yet</p>
                            <button 
                                wire:click="openMaterialModal"
                                class="mt-2 text-purple-400 hover:text-purple-300 text-sm"
                            >
                                Add material types
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Mining Targets Card -->
                <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-white">Mining Targets</h2>
                        <button 
                            wire:click="openTargetsModal"
                            class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition font-medium text-sm"
                        >
                            Set Targets
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-700 border border-gray-600 rounded-lg p-3">
                            <p class="text-xs text-gray-400 uppercase font-medium">Daily</p>
                            <p class="text-lg font-bold text-white mt-1">
                                {{ number_format($mining_targets['daily']) }}
                            </p>
                        </div>
                        <div class="bg-gray-700 border border-gray-600 rounded-lg p-3">
                            <p class="text-xs text-gray-400 uppercase font-medium">Weekly</p>
                            <p class="text-lg font-bold text-white mt-1">
                                {{ number_format($mining_targets['weekly']) }}
                            </p>
                        </div>
                        <div class="bg-gray-700 border border-gray-600 rounded-lg p-3">
                            <p class="text-xs text-gray-400 uppercase font-medium">Monthly</p>
                            <p class="text-lg font-bold text-white mt-1">
                                {{ number_format($mining_targets['monthly']) }}
                            </p>
                        </div>
                        <div class="bg-gray-700 border border-gray-600 rounded-lg p-3">
                            <p class="text-xs text-gray-400 uppercase font-medium">Yearly</p>
                            <p class="text-lg font-bold text-white mt-1">
                                {{ number_format($mining_targets['yearly']) }}
                            </p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-3 text-center">
                        Unit: {{ ucfirst(str_replace('_', ' ', $mining_targets['unit'])) }}
                    </p>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="space-y-4">
                <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                    <h3 class="font-semibold text-white mb-4">Summary</h3>
                    
                    <div class="space-y-4">
                        <!-- Form Summary -->
                        <div class="space-y-2">
                            <div>
                                <p class="text-xs font-medium text-gray-400 uppercase">Name</p>
                                <p class="text-sm text-white font-medium">
                                    {{ $name ?: '—' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-400 uppercase">Type</p>
                                <p class="text-sm text-white font-medium">
                                    {{ $type ? ucfirst($type) : '—' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-400 uppercase">Coordinates</p>
                                <p class="text-sm text-white font-medium">
                                    {{ count($coordinates) }} points
                                </p>
                            </div>
                        </div>

                        <div class="border-t border-gray-700 pt-4">
                            <!-- Help Text -->
                            <div class="bg-blue-500 bg-opacity-20 border border-blue-500 border-opacity-30 rounded-lg p-3">
                                <p class="text-xs text-blue-300">
                                    <strong>Tip:</strong> At least 3 coordinates are required to create a valid polygon mine area.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                    <div class="space-y-2">
                        <button 
                            wire:click="saveMineArea"
                            wire:loading.attr="disabled"
                            class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span wire:loading.remove>Save Mine Area</span>
                            <span wire:loading>Saving...</span>
                        </button>
                        <button 
                            wire:click="backToList"
                            class="w-full px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition font-medium"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals - INSIDE component for Livewire variable access -->
    <!-- Shift Modal -->
    @if($showShiftModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" wire:click="closeShiftModal">
        <div class="bg-gray-800 rounded-lg w-full max-w-md border border-gray-700" @click.stop>
            <div class="p-6 border-b border-gray-700">
                <h3 class="text-xl font-bold text-white">{{ $editingShiftIndex !== null ? 'Edit Shift' : 'Add Shift' }}</h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Shift Name *</label>
                    <input 
                        type="text" 
                        wire:model="shiftName"
                        placeholder="e.g., Day Shift, Night Shift"
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Start Time *</label>
                    <input 
                        type="time" 
                        wire:model="shiftStartTime"
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">End Time *</label>
                    <input 
                        type="time" 
                        wire:model="shiftEndTime"
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Active Days *</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                            <label class="flex items-center gap-2 px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg cursor-pointer hover:bg-gray-600 transition">
                                <input 
                                    type="checkbox" 
                                    wire:model="shiftDays"
                                    value="{{ $day }}"
                                    class="rounded text-blue-600 focus:ring-blue-500"
                                />
                                <span class="text-sm text-gray-300">{{ ucfirst(substr($day, 0, 3)) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="p-6 border-t border-gray-700 flex gap-3">
                <button 
                    wire:click="closeShiftModal"
                    class="flex-1 px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition"
                >
                    Cancel
                </button>
                <button 
                    wire:click="saveShift"
                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                >
                    {{ $editingShiftIndex !== null ? 'Update' : 'Add' }} Shift
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Material Type Modal -->
    @if($showMaterialModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" wire:click="closeMaterialModal">
        <div class="bg-gray-800 rounded-lg w-full max-w-md border border-gray-700" @click.stop>
            <div class="p-6 border-b border-gray-700">
                <h3 class="text-xl font-bold text-white">Add Material Type</h3>
            </div>
            <div class="p-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Material Type *</label>
                <input 
                    type="text" 
                    wire:model="newMaterialType"
                    placeholder="e.g., Coal, Iron Ore, Gold"
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500"
                />
                
                <div class="mt-4 p-3 bg-purple-500 bg-opacity-10 border border-purple-500 border-opacity-30 rounded-lg">
                    <p class="text-xs text-gray-300">
                        <strong>Examples:</strong> Coal, Iron Ore, Gold, Copper, Bauxite, Limestone, Sand, Gravel, Overburden
                    </p>
                </div>
            </div>
            <div class="p-6 border-t border-gray-700 flex gap-3">
                <button 
                    wire:click="closeMaterialModal"
                    class="flex-1 px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition"
                >
                    Cancel
                </button>
                <button 
                    wire:click="addMaterialType"
                    class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition"
                >
                    Add Material
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Mining Targets Modal -->
    @if($showTargetsModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" wire:click="closeTargetsModal">
        <div class="bg-gray-800 rounded-lg w-full max-w-md border border-gray-700" @click.stop>
            <div class="p-6 border-b border-gray-700">
                <h3 class="text-xl font-bold text-white">Set Mining Targets</h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Unit *</label>
                    <select 
                        wire:model="mining_targets.unit"
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500"
                    >
                        <option value="tonnes">Tonnes</option>
                        <option value="cubic_meters">Cubic Meters</option>
                        <option value="units">Units</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Daily Target *</label>
                    <input 
                        type="number" 
                        step="0.01"
                        wire:model="mining_targets.daily"
                        placeholder="0"
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Weekly Target *</label>
                    <input 
                        type="number" 
                        step="0.01"
                        wire:model="mining_targets.weekly"
                        placeholder="0"
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Monthly Target *</label>
                    <input 
                        type="number" 
                        step="0.01"
                        wire:model="mining_targets.monthly"
                        placeholder="0"
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Yearly Target *</label>
                    <input 
                        type="number" 
                        step="0.01"
                        wire:model="mining_targets.yearly"
                        placeholder="0"
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500"
                    />
                </div>
            </div>
            <div class="p-6 border-t border-gray-700 flex gap-3">
                <button 
                    wire:click="closeTargetsModal"
                    class="flex-1 px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition"
                >
                    Cancel
                </button>
                <button 
                    wire:click="saveTargets"
                    class="flex-1 px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition"
                >
                    Save Targets
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Leaflet CSS - OUTSIDE component div -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
console.log('🟢 [MINE AREA MAP] Inline script executing in create-edit component');

(function() {
    'use strict';
    
    let mineAreaMap = null;
    let mapInitialized = false;
    let initRetryCount = 0;
    const MAX_INIT_RETRIES = 50;

    function initializeCreateEditMap() {
        console.log('═══════════════════════════════════════════════');
        console.log('🗺️ [MINE AREA MAP] initializeCreateEditMap() called');
        console.log('═══════════════════════════════════════════════');
        
        // Robust Leaflet check
        if (typeof L === 'undefined') {
            initRetryCount++;
            if (initRetryCount > MAX_INIT_RETRIES) {
                console.error('❌ [MINE AREA MAP] Leaflet failed after max retries');
                return;
            }
            console.log('⏳ [MINE AREA MAP] Waiting for Leaflet, retry:', initRetryCount);
            setTimeout(initializeCreateEditMap, 200);
            return;
        }

        console.log('✅ [MINE AREA MAP] Leaflet available, version:', L.version);

        const mapContainer = document.getElementById('map');
        const mapStatus = document.getElementById('map-status');
        
        console.log('[MINE AREA MAP] Map container found:', !!mapContainer);
        console.log('[MINE AREA MAP] Status div found:', !!mapStatus);
        
        if (!mapContainer) {
            console.error('❌ [MINE AREA MAP] Map container not found!');
            if (mapStatus) mapStatus.textContent = '❌ Map container not found';
            return;
        }

        // Clean up existing map if present
        if (mineAreaMap) {
            try {
                mineAreaMap.remove();
                console.log('[MINE AREA MAP] Cleaned up previous map instance');
            } catch (e) {
                console.log('[MINE AREA MAP] Error removing old map:', e);
            }
            mineAreaMap = null;
        }

        // Clear Leaflet state
        if (mapContainer._leaflet_id) {
            delete mapContainer._leaflet_id;
        }

        try {
            console.log('🚀 [MINE AREA MAP] Creating map...');
            if (mapStatus) mapStatus.innerHTML = '🔄 Initializing map...';

            // Create map instance
            mineAreaMap = L.map('map', {
                preferCanvas: true
            }).setView([-25.7479, 28.1872], 6);

            // Add OpenStreetMap tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(mineAreaMap);

            // Invalidate size after timeout
            setTimeout(() => {
                if (mineAreaMap) {
                    console.log('[MINE AREA MAP] Running invalidateSize...');
                    mineAreaMap.invalidateSize();
                    console.log('✅ [MINE AREA MAP] Map size invalidated');
                    if (mapStatus) mapStatus.innerHTML = '✅ Map ready!';
                }
            }, 250);

            mapInitialized = true;
            console.log('✅✅✅ [MINE AREA MAP] Map initialized successfully!');

            // Setup event listeners for drawing mode and coordinate updates
            setupMapEventListeners();

        } catch (error) {
            console.error('❌❌❌ [MINE AREA MAP] ERROR initializing map!');
            console.error('[MINE AREA MAP] Error:', error);
            if (mapStatus) mapStatus.innerHTML = '❌ Error: ' + error.message;
        }

        console.log('═══════════════════════════════════════════════');
        console.log('🏁 [MINE AREA MAP] initializeCreateEditMap() completed');
        console.log('═══════════════════════════════════════════════');
    }

    function setupMapEventListeners() {
        console.log('[MINE AREA MAP] Setting up event listeners...');
        
        const mapContainer = document.getElementById('map');
        if (!mapContainer || !mineAreaMap) {
            console.warn('[MINE AREA MAP] Cannot setup listeners - map or container missing');
            return;
        }

        // Listen for drawing mode changes from Livewire
        document.addEventListener('drawing-mode-changed', function(e) {
            console.log('[MINE AREA MAP] Drawing mode changed:', e.detail?.isDrawing);
            // Map cursor style can be updated here if needed
        });

        // Listen for coordinate updates from Livewire
        document.addEventListener('coordinates-updated', function(e) {
            console.log('[MINE AREA MAP] Coordinates updated from Livewire');
            redrawCoordinates(e.detail?.coordinates);
        });
    }

    function redrawCoordinates(coordinates) {
        if (!mineAreaMap) {
            console.warn('[MINE AREA MAP] Map not initialized for redrawing');
            return;
        }

        console.log('[MINE AREA MAP] Redrawing', coordinates?.length || 0, 'coordinates');

        // Clear existing markers
        // Note: In a full implementation, you'd track markers to remove them
        
        if (!coordinates || coordinates.length === 0) return;

        // Add markers for each coordinate
        coordinates.forEach((coord, index) => {
            L.circleMarker([coord.lat, coord.lon], {
                radius: 6,
                fillColor: '#3b82f6',
                color: '#1e40af',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.8
            }).addTo(mineAreaMap).bindPopup(`Point ${index + 1}`);
        });

        // Draw polygon if 3+ points
        if (coordinates.length >= 3) {
            const latlngs = coordinates.map(c => [c.lat, c.lon]);
            L.polygon(latlngs, {
                color: '#2563eb',
                fillColor: '#3b82f6',
                fillOpacity: 0.2,
                weight: 2
            }).addTo(mineAreaMap);
        }
    }

    // Initialize on DOMContentLoaded
    console.log('[MINE AREA MAP] Setting up initialization...');
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            console.log('[MINE AREA MAP] DOMContentLoaded fired');
            setTimeout(initializeCreateEditMap, 100);
        });
    } else {
        console.log('[MINE AREA MAP] DOM already loaded, initializing immediately');
        setTimeout(initializeCreateEditMap, 100);
    }

    // Re-initialize after Livewire component updates
    document.addEventListener('livewire:navigated', function() {
        console.log('[MINE AREA MAP] livewire:navigated - reinitializing map');
        initRetryCount = 0;
        mapInitialized = false;
        setTimeout(initializeCreateEditMap, 100);
    });

    // Also handle livewire:init event
    document.addEventListener('livewire:init', function() {
        console.log('[MINE AREA MAP] livewire:init - initializing map');
        setTimeout(initializeCreateEditMap, 150);
    });

    console.log('🟢 [MINE AREA MAP] Script setup complete');
})();
</script>
