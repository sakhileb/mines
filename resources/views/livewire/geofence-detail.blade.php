<div>
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('geofences') }}" class="text-amber-400 hover:text-amber-300 mb-4 inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Geofences
        </a>
        <h1 class="text-3xl font-bold text-white">{{ $geofence->name }}</h1>
        @if ($geofence->description)
            <p class="text-gray-400 mt-2">{{ $geofence->description }}</p>
        @endif
    </div>

    <!-- Information Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Location Card -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
            <p class="text-gray-400 text-sm">Location</p>
            <div class="mt-2">
                <p class="text-sm text-white font-mono">{{ $geofence->center_latitude }}, {{ $geofence->center_longitude }}</p>
                <a href="https://maps.google.com/?q={{ $geofence->center_latitude }},{{ $geofence->center_longitude }}" target="_blank" class="text-amber-400 hover:text-amber-300 text-xs mt-2 inline-block">
                    View on Google Maps →
                </a>
            </div>
        </div>

        <!-- Total Entries Card -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
            <p class="text-gray-400 text-sm">Total Entries</p>
            <p class="text-3xl font-bold text-blue-400 mt-2">{{ $totalEntries }}</p>
            <p class="text-xs text-gray-500 mt-1">Entry/exit events</p>
        </div>

        <!-- Machine Type Counts -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
            <p class="text-gray-400 text-sm">Machine Types (in this geofence)</p>
            <div class="mt-2">
                <p class="text-sm text-white">Excavators: {{ $machineTypeCounts['excavator'] ?? 0 }}</p>
                <p class="text-sm text-white">ADT / Haulers: {{ $machineTypeCounts['articulated_hauler'] ?? ($machineTypeCounts['adt'] ?? 0) }}</p>
                <p class="text-sm text-white">Dozers: {{ $machineTypeCounts['dozer'] ?? 0 }}</p>
            </div>
            <p class="text-xs text-gray-500 mt-1">Distinct machines by type</p>
        </div>

        <!-- Machines Tracked Card -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
            <p class="text-gray-400 text-sm">Machines Tracked</p>
            <p class="text-3xl font-bold text-green-400 mt-2">{{ $machineCount }}</p>
            <p class="text-xs text-gray-500 mt-1">Unique machines</p>
        </div>

        <!-- Created Card -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
            <p class="text-gray-400 text-sm">Created</p>
            <p class="text-xl font-semibold text-white mt-2">{{ $geofence->created_at->format('M d, Y') }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $geofence->created_at->diffForHumans() }}</p>
        </div>
    </div>

    <!-- Machines tracked/untracked -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
            <p class="text-gray-400 text-sm">Machines Tracked</p>
            <p class="text-2xl font-bold text-green-400 mt-2">{{ $machinesTracked }}</p>
            <p class="text-xs text-gray-500 mt-1">Unique machines recorded in this geofence</p>
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
            <p class="text-gray-400 text-sm">Machines Untracked</p>
            <p class="text-2xl font-bold text-red-400 mt-2">{{ $machinesUntracked }}</p>
            <p class="text-xs text-gray-500 mt-1">Machines on the team not recorded in this geofence</p>
        </div>
    </div>

    <!-- Recent Loads -->
    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Recent Loads & Authorizations</h3>
        @if(count($loads) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-700">
                        <tr>
                            <th class="text-left px-4 py-2 text-gray-400">Machine</th>
                            <th class="text-left px-4 py-2 text-gray-400">Tonnage</th>
                            <th class="text-left px-4 py-2 text-gray-400">Material</th>
                            <th class="text-left px-4 py-2 text-gray-400">Entry Time</th>
                            <th class="text-left px-4 py-2 text-gray-400">Authorized By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach($loads as $l)
                            <tr class="hover:bg-gray-700">
                                <td class="px-4 py-3">
                                    <a href="{{ route('fleet.show', $l['machine']->id) }}" class="text-amber-400 hover:text-amber-300 font-medium">{{ $l['machine']->name }}</a>
                                </td>
                                <td class="px-4 py-3 text-gray-300">{{ $l['tonnage_loaded'] ? number_format($l['tonnage_loaded']) . ' tons' : 'N/A' }}</td>
                                <td class="px-4 py-3 text-gray-300">{{ $l['material_type'] ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-gray-300">{{ $l['entry_time'] ? \Illuminate\Support\Carbon::parse($l['entry_time'])->format('M d, H:i') : 'N/A' }}</td>
                                <td class="px-4 py-3 text-gray-300">{{ $l['authorizer'] ?? 'Unknown' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-400">No recent loads recorded for this geofence.</p>
        @endif
    </div>

    <!-- Recent Entries -->
    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Recent Entry/Exit Events</h3>
        
        @if ($recentEntries->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-700">
                        <tr>
                            <th class="text-left px-4 py-2 text-gray-400">Machine</th>
                            <th class="text-left px-4 py-2 text-gray-400">Event Type</th>
                            <th class="text-left px-4 py-2 text-gray-400">Time</th>
                            <th class="text-left px-4 py-2 text-gray-400">Material</th>
                            <th class="text-left px-4 py-2 text-gray-400">Tonnage</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach ($recentEntries as $entry)
                            <tr class="hover:bg-gray-700">
                                <td class="px-4 py-3">
                                    <a href="{{ route('fleet.show', $entry->machine) }}" class="text-amber-400 hover:text-amber-300 font-medium">
                                        {{ $entry->machine->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($entry->entry_time && !$entry->exit_time)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500 bg-opacity-20 text-green-400">
                                            ENTRY
                                        </span>
                                    @elseif ($entry->exit_time)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-500 bg-opacity-20 text-blue-400">
                                            EXIT
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-300">
                                    {{ $entry->entry_time ? $entry->entry_time->format('M d, H:i') : 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-gray-300">{{ $entry->material_type ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-gray-300">{{ $entry->tonnage_loaded ? number_format($entry->tonnage_loaded) . ' tons' : 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <p class="text-gray-400 text-lg mt-4">No entry/exit events recorded</p>
            </div>
        @endif
    </div>
</div>
