<!-- Mine Areas Analytics Dashboard -->
<div class="min-h-screen bg-slate-900 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-4xl font-bold text-white">Analytics Dashboard</h1>
                    <p class="mt-2 text-slate-400">Mine Areas Performance & Operations</p>
                </div>
                <a href="{{ route('mine-areas') }}" class="px-6 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition font-medium">
                    ← Back to Areas
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6 mb-8">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Period</label>
                    <select 
                        wire:model.live="selectedPeriod"
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent text-white"
                    >
                        <option value="7days">Last 7 Days</option>
                        <option value="30days">Last 30 Days</option>
                        <option value="90days">Last 90 Days</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Area Type</label>
                    <select 
                        wire:model.live="filterType"
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent text-white"
                    >
                        <option value="">All Types</option>
                        <option value="open_pit">Open Pit</option>
                        <option value="underground">Underground</option>
                        <option value="surface">Surface</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                    <select 
                        wire:model.live="filterStatus"
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent text-white"
                    >
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Geofence Route Selection -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6 mb-8">
            <div class="mb-4">
                <h2 class="text-xl font-semibold text-white flex items-center gap-2">
                    <span class="text-2xl">🗺️</span>
                    Route to Geofence
                </h2>
                <p class="text-sm text-gray-400 mt-1">Select a geofence to find the safest and most optimal route</p>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Geofence Selector -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Select Destination Geofence</label>
                    <select 
                        wire:model.live="selectedGeofenceId"
                        class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent text-white"
                    >
                        <option value="">-- Choose a geofence --</option>
                        @foreach($geofences as $geofence)
                            <option value="{{ $geofence->id }}">
                                {{ $geofence->name }} 
                                @if($geofence->mineArea)
                                    ({{ $geofence->mineArea->name }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    
                    @if($selectedGeofenceId && count($availableRoutes) === 0)
                        <div class="mt-3 p-3 bg-yellow-600/20 border border-yellow-600/50 rounded-lg">
                            <p class="text-yellow-400 text-sm">⚠️ No active routes found leading to this geofence.</p>
                        </div>
                    @endif
                </div>

                <!-- Recommended Route Display -->
                <div>
                    @if($recommendedRoute)
                        <div class="bg-gradient-to-br from-green-600/20 to-emerald-600/20 border border-green-600/50 rounded-lg p-4">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-green-400 text-lg">✓</span>
                                        <h3 class="text-white font-semibold">{{ $recommendedRoute['name'] }}</h3>
                                    </div>
                                    <p class="text-xs text-gray-400">{{ $recommendedRoute['machine'] }}</p>
                                </div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-600 text-white">
                                    {{ ucfirst($recommendedRoute['type']) }}
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-3 gap-3 mt-4">
                                <div class="bg-gray-800/50 rounded-lg p-2">
                                    <p class="text-xs text-gray-400">Distance</p>
                                    <p class="text-sm font-bold text-white">{{ number_format($recommendedRoute['distance'], 2) }} km</p>
                                </div>
                                <div class="bg-gray-800/50 rounded-lg p-2">
                                    <p class="text-xs text-gray-400">Time</p>
                                    <p class="text-sm font-bold text-white">{{ $recommendedRoute['estimated_time'] }} min</p>
                                </div>
                                <div class="bg-gray-800/50 rounded-lg p-2">
                                    <p class="text-xs text-gray-400">Fuel</p>
                                    <p class="text-sm font-bold text-white">{{ number_format($recommendedRoute['estimated_fuel'], 1) }} L</p>
                                </div>
                            </div>
                            
                            <div class="mt-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                    <span class="text-xs text-green-400 font-medium">Optimal Route Selected</span>
                                </div>
                                <span class="text-xs text-gray-400">Score: {{ $recommendedRoute['score'] }}/100</span>
                            </div>
                        </div>
                        
                        @if(count($availableRoutes) > 1)
                            <div class="mt-3">
                                <details class="group">
                                    <summary class="cursor-pointer text-sm text-amber-400 hover:text-amber-300 flex items-center gap-1">
                                        <span class="group-open:rotate-90 transition-transform">▶</span>
                                        View {{ count($availableRoutes) - 1 }} alternative route(s)
                                    </summary>
                                    <div class="mt-2 space-y-2">
                                        @foreach(array_slice($availableRoutes, 1) as $route)
                                            <div class="bg-gray-700/50 border border-gray-600 rounded p-2">
                                                <div class="flex items-center justify-between">
                                                    <div class="text-xs">
                                                        <p class="text-white font-medium">{{ $route['name'] }}</p>
                                                        <p class="text-gray-400">{{ $route['distance'] }} km · {{ $route['estimated_time'] }} min · {{ $route['estimated_fuel'] }} L</p>
                                                    </div>
                                                    <span class="text-xs px-2 py-1 bg-gray-600 text-gray-300 rounded">{{ ucfirst($route['type']) }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            </div>
                        @endif
                    @elseif($selectedGeofenceId)
                        <div class="flex items-center justify-center h-full bg-gray-700/30 border border-gray-600 border-dashed rounded-lg p-6">
                            <div class="text-center">
                                <div class="text-4xl mb-2">🔍</div>
                                <p class="text-gray-400 text-sm">Searching for routes...</p>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center justify-center h-full bg-gray-700/30 border border-gray-600 border-dashed rounded-lg p-6">
                            <div class="text-center">
                                <div class="text-4xl mb-2">🎯</div>
                                <p class="text-gray-400 text-sm">Select a geofence to see recommended routes</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="mt-4 p-3 bg-blue-600/10 border border-blue-600/30 rounded-lg">
                <p class="text-xs text-blue-300">
                    <strong>💡 Smart Route Selection:</strong> The system automatically analyzes all available routes and selects the most optimal and safest path based on route type, fuel efficiency, and travel time.
                </p>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400 uppercase">Total Areas</p>
                        <p class="text-3xl font-bold text-white mt-2">{{ $statistics['total_areas'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center text-2xl">📍</div>
                </div>
            </div>

            <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400 uppercase">Active Areas</p>
                        <p class="text-3xl font-bold text-green-400 mt-2">{{ $statistics['active_areas'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center text-2xl">✓</div>
                </div>
            </div>

            <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400 uppercase">Total Area</p>
                        <p class="text-3xl font-bold text-blue-400 mt-2">{{ number_format($statistics['total_area_sqm'], 0) }}</p>
                        <p class="text-xs text-gray-500 mt-1">m²</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center text-2xl">📏</div>
                </div>
            </div>

            <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400 uppercase">Total Machines</p>
                        <p class="text-3xl font-bold text-purple-400 mt-2">{{ $statistics['total_machines'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center text-2xl">⚙️</div>
                </div>
            </div>
        </div>

        <!-- Production & Machines -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-white mb-4">Machine Status</h2>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between mb-2">
                            <p class="text-gray-400">Online</p>
                            <p class="font-bold text-green-400">{{ $statistics['active_machines'] }}</p>
                        </div>
                        <div class="w-full bg-gray-700 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full transition-all" style="width: {{ $statistics['total_machines'] > 0 ? ($statistics['active_machines'] / $statistics['total_machines']) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-2">
                            <p class="text-gray-400">Offline</p>
                            <p class="font-bold text-gray-400">{{ $statistics['total_machines'] - $statistics['active_machines'] }}</p>
                        </div>
                        <div class="w-full bg-gray-700 rounded-full h-2">
                            <div class="bg-gray-500 h-2 rounded-full transition-all" style="width: {{ $statistics['total_machines'] > 0 ? (($statistics['total_machines'] - $statistics['active_machines']) / $statistics['total_machines']) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-white mb-4">Production Metrics</h2>
                <div class="space-y-4">
                    <div class="p-3 bg-gray-700/50 rounded-lg">
                        <p class="text-sm text-gray-400">Total Production</p>
                        <p class="text-2xl font-bold text-white">{{ number_format($statistics['total_production'], 2) }} T</p>
                    </div>
                    <div class="p-3 bg-gray-700/50 rounded-lg">
                        <p class="text-sm text-gray-400">Average per Area</p>
                        <p class="text-2xl font-bold text-white">{{ number_format($statistics['average_production'], 2) }} T</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Production Trend Chart -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-white mb-4">Production Trend</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-700">
                        <tr>
                            <th class="text-left py-2 px-4 text-gray-400">Date</th>
                            <th class="text-right py-2 px-4 text-gray-400">Production (T)</th>
                            <th class="text-right py-2 px-4 text-gray-400">Trend</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach($productionTrend as $item)
                            <tr class="hover:bg-gray-700/50">
                                <td class="py-2 px-4 text-gray-300">{{ \Carbon\Carbon::parse($item['date'])->format('M d, Y') }}</td>
                                <td class="py-2 px-4 text-right font-medium text-white">{{ number_format($item['total'], 2) }}</td>
                                <td class="py-2 px-4 text-right">
                                    <div class="w-24 h-6 bg-blue-500/20 rounded inline-block relative">
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <div class="w-full h-1 bg-blue-500 rounded" style="width: {{ min($item['total'] / ($statistics['total_production'] / count($productionTrend) * 2) * 100, 100) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Upload Mine Plan Section -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-white mb-4 flex items-center gap-2">
                <span class="text-2xl">📤</span>
                Upload Mine Plan
            </h2>
            <form wire:submit.prevent="uploadMinePlan" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Mine Plan File (PDF, PNG, JPG, DWG)</label>
                    <input type="file" wire:model="minePlanFile" accept=".pdf,.png,.jpg,.jpeg,.dwg" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500">
                    @error('minePlanFile') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Associate with Mine Area (optional)</label>
                    <select wire:model="minePlanAreaId" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500">
                        <option value="">-- Select Mine Area --</option>
                        @foreach($team->mineAreas as $area)
                            <option value="{{ $area->id }}">{{ $area->name }}</option>
                        @endforeach
                    </select>
                    @error('minePlanAreaId') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Description (optional)</label>
                    <textarea wire:model="minePlanDescription" rows="2" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500" placeholder="Describe the mine plan or any notes..."></textarea>
                </div>
                <button type="submit" class="px-6 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition font-medium">Upload Plan</button>
                @if (session()->has('minePlanSuccess'))
                    <div class="mt-4 bg-green-600 text-white px-4 py-3 rounded-lg">{{ session('minePlanSuccess') }}</div>
                @endif
                @if (session()->has('minePlanError'))
                    <div class="mt-4 bg-red-600 text-white px-4 py-3 rounded-lg">{{ session('minePlanError') }}</div>
                @endif
            </form>
            <div class="mt-4 text-xs text-gray-400">
                Accepted formats: PDF, PNG, JPG, DWG. Max size: 10MB.<br>
                Uploaded mine plans will be available for download and review in the area details.
            </div>

            <!-- Uploaded Mine Plans List -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="text-2xl">📁</span>
                    Uploaded Mine Plans
                </h3>
                @if(count($uploadedMinePlans) === 0)
                    <div class="text-gray-400 text-sm">No mine plans uploaded yet.</div>
                @else
                    <ul class="space-y-4">
                        @foreach($uploadedMinePlans as $plan)
                            <li class="bg-gray-700/50 rounded-lg p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                                <div>
                                    <div class="font-medium text-white">{{ $plan->mineArea ? $plan->mineArea->name : 'Unassigned' }}</div>
                                    <div class="text-xs text-gray-400 mb-1">{{ $plan->description }}</div>
                                    <div class="text-xs text-gray-500">Uploaded by: {{ $plan->uploader->name ?? 'Unknown' }} on {{ $plan->created_at->format('M d, Y H:i') }}</div>
                                </div>
                                <div class="flex gap-2 items-center">
                                    <a href="{{ Storage::url($plan->file_path) }}" target="_blank" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-medium">Download</a>
                                    @if(auth()->user()->is_admin || $plan->uploaded_by === auth()->id())
                                        <button wire:click="deleteMinePlan({{ $plan->id }})" class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-medium">Delete</button>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-white mb-4">Top Areas by Size</h2>
                <div class="space-y-4">
                    @foreach($topAreas as $area)
                        <div class="p-4 bg-gray-700/50 rounded-lg">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="font-medium text-white">{{ $area['name'] }}</p>
                                    <p class="text-xs text-gray-400">{{ number_format($area['area_sqm'], 0) }} m²</p>
                                </div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                    {{ $area['machines_count'] }} machines
                                </span>
                            </div>
                            <div class="text-sm text-gray-400">
                                Production: <span class="font-medium text-white">{{ number_format($area['production'], 2) }} T</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-white mb-4">Machine Distribution</h2>
                <div class="space-y-4">
                    @foreach($machineDistribution as $item)
                        <div class="p-3">
                            <div class="flex justify-between mb-2">
                                <p class="text-sm text-gray-300">{{ $item['name'] }}</p>
                                <p class="text-sm font-bold text-white">{{ $item['count'] }}</p>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-2">
                                <div class="bg-gradient-to-r from-amber-400 to-amber-600 h-2 rounded-full transition-all" style="width: {{ ($item['count'] / max(1, collect($machineDistribution)->max('count'))) * 100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
