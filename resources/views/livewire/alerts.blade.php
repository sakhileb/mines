<div class="min-h-screen bg-slate-900 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">Alerts &amp; Incidents</h1>
            <p class="text-slate-400">Monitor machine alerts, safety incidents, and breakdown reports</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            @php
                $allAlerts = $alerts;
                $criticalCount = $allAlerts->where('priority', 'critical')->count() ?? 0;
                $highCount = $allAlerts->where('priority', 'high')->count() ?? 0;
                $openCount = $allAlerts->whereIn('status', ['new', 'acknowledged'])->count() ?? 0;
            @endphp
            
            <div class="bg-red-900/30 border border-red-700 rounded-lg p-4">
                <div class="text-red-400 text-sm font-medium">Critical Alerts</div>
                <div class="text-3xl font-bold text-red-300 mt-2">{{ $criticalCount }}</div>
            </div>
            
            <div class="bg-orange-900/30 border border-orange-700 rounded-lg p-4">
                <div class="text-orange-400 text-sm font-medium">High Priority</div>
                <div class="text-3xl font-bold text-orange-300 mt-2">{{ $highCount }}</div>
            </div>
            
            <div class="bg-blue-900/30 border border-blue-700 rounded-lg p-4">
                <div class="text-blue-400 text-sm font-medium">Open Alerts</div>
                <div class="text-3xl font-bold text-blue-300 mt-2">{{ $openCount }}</div>
            </div>
            
            <div class="bg-slate-700/50 border border-slate-600 rounded-lg p-4">
                <div class="text-slate-400 text-sm font-medium">Total Alerts</div>
                <div class="text-3xl font-bold text-slate-300 mt-2">{{ $allAlerts->total() ?? 0 }}</div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="flex gap-1 mb-6 bg-slate-800/60 p-1 rounded-xl border border-slate-700">
            <button wire:click="$set('activeTab', 'alerts')"
                class="flex-1 px-6 py-2.5 rounded-lg text-sm font-semibold transition-all
                       {{ $activeTab === 'alerts' ? 'bg-blue-600 text-white shadow' : 'text-slate-400 hover:text-white' }}">
                <svg class="inline w-4 h-4 mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                Machine Alerts
                <span class="ml-1.5 bg-slate-700/80 text-slate-300 text-xs px-1.5 py-0.5 rounded-full">{{ $alerts->total() }}</span>
            </button>
            <button wire:click="$set('activeTab', 'incidents')"
                class="flex-1 px-6 py-2.5 rounded-lg text-sm font-semibold transition-all
                       {{ $activeTab === 'incidents' ? 'bg-rose-600 text-white shadow' : 'text-slate-400 hover:text-white' }}">
                <svg class="inline w-4 h-4 mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Incident Reports
                <span class="ml-1.5 bg-slate-700/80 text-slate-300 text-xs px-1.5 py-0.5 rounded-full">{{ $incidentReports->total() }}</span>
            </button>
        </div>

        @if($activeTab === 'alerts')
        <!-- Filters Bar -->
        <div class="bg-slate-800 rounded-lg p-6 mb-6 border border-slate-700">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Search Alerts</label>
                    <input 
                        type="text" 
                        wire:model.live="search" 
                        placeholder="Search by title or description..."
                        class="w-full bg-slate-700 text-white px-4 py-2 rounded-lg border border-slate-600 focus:border-blue-500 focus:outline-none"
                    >
                </div>

                <!-- Priority Filter -->
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Priority</label>
                    <select 
                        wire:model.live="selectedPriority"
                        class="w-full bg-slate-700 text-white px-4 py-2 rounded-lg border border-slate-600 focus:border-blue-500 focus:outline-none"
                    >

                        <option value="all">All Priorities</option>
                        @foreach ($alertPriorities as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Status</label>
                    <select 
                        wire:model.live="selectedStatus"
                        class="w-full bg-slate-700 text-white px-4 py-2 rounded-lg border border-slate-600 focus:border-blue-500 focus:outline-none"
                    >
                        <option value="all">All Statuses</option>
                        <option value="new">New</option>
                        <option value="acknowledged">Acknowledged</option>
                        <option value="resolved">Resolved</option>
                        <option value="dismissed">Dismissed</option>
                    </select>
                </div>

                <!-- Type Filter -->
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Type</label>
                    <select 
                        wire:model.live="selectedType"
                        class="w-full bg-slate-700 text-white px-4 py-2 rounded-lg border border-slate-600 focus:border-blue-500 focus:outline-none"
                    >
                        <option value="all">All Types</option>
                        @foreach ($alertTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Sort Controls -->
            <div class="flex items-center gap-2">
                <span class="text-sm text-slate-400">Sort by:</span>
                <button 
                    wire:click="setSortBy('created_at')"
                    class="px-3 py-1 rounded-lg text-sm {{ $sortBy === 'created_at' ? 'bg-blue-600 text-white' : 'bg-slate-700 text-slate-300 hover:bg-slate-600' }}"
                >
                    Date
                </button>
                <button 
                    wire:click="setSortBy('priority')"
                    class="px-3 py-1 rounded-lg text-sm {{ $sortBy === 'priority' ? 'bg-blue-600 text-white' : 'bg-slate-700 text-slate-300 hover:bg-slate-600' }}"
                >
                    Priority
                </button>
                <button 
                    wire:click="setSortBy('status')"
                    class="px-3 py-1 rounded-lg text-sm {{ $sortBy === 'status' ? 'bg-blue-600 text-white' : 'bg-slate-700 text-slate-300 hover:bg-slate-600' }}"
                >
                    Status
                </button>
            </div>
        </div>

        <!-- Alerts List -->
        @if($alerts->count() > 0)
            <div class="space-y-3">
                @foreach ($alerts as $alert)
                    @php
                        $priorityClasses = $alert->priority === 'critical' ? 'border-red-700 bg-red-900/10' : ($alert->priority === 'high' ? 'border-orange-700 bg-orange-900/10' : ($alert->priority === 'medium' ? 'border-yellow-700 bg-yellow-900/10' : 'border-slate-700'));
                        $attentionClass = $alert->status === 'attention' ? 'border-red-700 bg-red-900/10' : '';
                    @endphp

                    <div class="bg-slate-800 rounded-lg border {{ $priorityClasses }} {{ $attentionClass }} p-4 hover:border-blue-600 transition">
                        <div class="flex items-start justify-between gap-4">
                            <!-- Alert Info -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <!-- Priority Badge -->
                                    <span class="px-2 py-1 text-xs font-semibold rounded {{ 
                                        $alert->priority === 'critical' ? 'bg-red-900 text-red-300' : 
                                        ($alert->priority === 'high' ? 'bg-orange-900 text-orange-300' : 
                                        ($alert->priority === 'medium' ? 'bg-yellow-900 text-yellow-300' : 'bg-blue-900 text-blue-300')) 
                                    }}">
                                        {{ ucfirst($alert->priority) }}
                                    </span>

                                    <!-- Status Badge -->
                                                    @php
                                                        $statusLabel = match($alert->status) {
                                                            'new' => 'New',
                                                            'acknowledged' => 'Acknowledged',
                                                            'resolved' => 'Resolved',
                                                            'attention' => 'Attention',
                                                            'dismissed_unresolved' => 'Dismissed - Unresolved',
                                                            'dismissed' => 'Dismissed',
                                                            default => ucfirst($alert->status),
                                                        };

                                                        $statusClass = match($alert->status) {
                                                            'new' => 'bg-green-900 text-green-300',
                                                            'acknowledged' => 'bg-blue-900 text-blue-300',
                                                            'resolved' => 'bg-slate-700 text-slate-300',
                                                            'attention' => 'bg-red-900 text-red-300',
                                                            'dismissed_unresolved' => 'bg-red-900 text-red-300',
                                                            'dismissed' => 'bg-slate-600 text-slate-400',
                                                            default => 'bg-slate-600 text-slate-400',
                                                        };
                                                    @endphp
                                                    <span class="px-2 py-1 text-xs font-semibold rounded {{ $statusClass }}">{{ $statusLabel }}</span>

                                    <!-- Type Badge -->
                                    <span class="px-2 py-1 text-xs font-medium rounded bg-slate-700 text-slate-300">
                                        {{ $alertTypes[$alert->type] ?? $alert->type }}
                                    </span>
                                </div>

                                <h3 class="text-lg font-semibold text-white mb-1">{{ $alert->title }}</h3>
                                <p class="text-slate-400 text-sm">{{ $alert->description }}</p>

                                @if($alert->machine)
                                    <div class="mt-2 text-sm text-slate-500">
                                        <a href="{{ route('fleet.show', $alert->machine->id) }}" class="text-blue-400 hover:text-blue-300">
                                            Machine: {{ $alert->machine->name }}
                                        </a>
                                    </div>
                                @endif

                                <div class="mt-2 text-xs text-slate-500">
                                    {{ $alert->created_at->diffForHumans() }}
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2">
                                <button 
                                    wire:click="showDetails({{ $alert->id }})"
                                    class="px-3 py-1 text-sm bg-slate-700 text-slate-300 hover:bg-slate-600 rounded transition"
                                >
                                    Details
                                </button>

                                @if($alert->status === 'new')
                                    <button 
                                        wire:click="acknowledgeAlert({{ $alert->id }})"
                                        class="px-3 py-1 text-sm bg-blue-600 text-white hover:bg-blue-700 rounded transition flex items-center gap-1"
                                        wire:loading.attr="disabled"
                                        wire:target="acknowledgeAlert({{ $alert->id }})"
                                    >
                                        <span wire:loading.remove wire:target="acknowledgeAlert({{ $alert->id }})">Acknowledge</span>
                                        <span wire:loading wire:target="acknowledgeAlert({{ $alert->id }})" class="flex items-center gap-1">
                                            <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        </span>
                                    </button>
                                @endif

                                @if($alert->status !== 'resolved')
                                    <button 
                                        wire:click="resolveAlert({{ $alert->id }})"
                                        class="px-3 py-1 text-sm bg-green-600 text-white hover:bg-green-700 rounded transition flex items-center gap-1"
                                        wire:loading.attr="disabled"
                                        wire:target="resolveAlert({{ $alert->id }})"
                                    >
                                        <span wire:loading.remove wire:target="resolveAlert({{ $alert->id }})">Resolve</span>
                                        <span wire:loading wire:target="resolveAlert({{ $alert->id }})" class="flex items-center gap-1">
                                            <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        </span>
                                    </button>
                                @endif

                                @if($alert->status !== 'dismissed')
                                    <button 
                                        wire:click="dismissAlert({{ $alert->id }})"
                                        class="px-3 py-1 text-sm bg-slate-600 text-white hover:bg-slate-500 rounded transition"
                                    >
                                        Dismiss
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $alerts->links(data: ['scrollTo' => false]) }}
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-700 mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">No Alerts Found</h3>
                <p class="text-slate-400">No alerts match your criteria. Great job!</p>
            </div>
        @endif
    </div>

    <!-- Alert Details Modal -->
    @if($showDetailsModal && $selectedAlert)
        @php
            $isSpeedAlert = in_array(strtolower($selectedAlert->type ?? ''), ['speed', 'speed_limit', 'overspeed']);
            $modalSizeClass = $isSpeedAlert ? 'w-full max-w-3xl max-h-[80vh]' : 'w-96 max-h-96';
        @endphp
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6 {{ $modalSizeClass }} overflow-y-auto">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-semibold text-white">{{ $selectedAlert->title }}</h3>
                    <button 
                        wire:click="closeDetails"
                        class="text-slate-400 hover:text-slate-300"
                    >
                        ✕
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Description</div>
                        <div class="text-slate-300">{{ $selectedAlert->description }}</div>
                    </div>

                    @if($selectedAlert->details)
                        <div>
                            <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Details</div>
                            <div class="text-slate-300 text-sm">{{ $selectedAlert->details }}</div>
                        </div>
                    @endif

                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Priority</div>
                        <div class="text-slate-300">{{ ucfirst($selectedAlert->priority) }}</div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Status</div>
                        <div class="text-slate-300">{{ ucfirst($selectedAlert->status) }}</div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Created</div>
                        <div class="text-slate-300">{{ $selectedAlert->created_at->format('M d, Y H:i') }}</div>
                    </div>

                    @if($selectedAlert->acknowledged_at)
                        <div>
                            <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Acknowledged</div>
                            <div class="text-slate-300">{{ $selectedAlert->acknowledged_at->format('M d, Y H:i') }}</div>
                        </div>
                    @endif

                    @if($selectedAlert->resolved_at)
                        <div>
                            <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Resolved</div>
                            <div class="text-slate-300">{{ $selectedAlert->resolved_at->format('M d, Y H:i') }}</div>
                        </div>
                    @endif

                    {{-- Location / Context --}}
                    @if($isSpeedAlert)
                        <!-- Expanded speed alert context -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="text-xs font-semibold text-slate-400 uppercase mb-1">GPS Coordinates</div>
                                <div class="text-slate-300 text-sm">
                                    @if(is_array($selectedAlert->metadata ?? []) && isset($selectedAlert->metadata['latitude']) && isset($selectedAlert->metadata['longitude']))
                                        {{ $selectedAlert->metadata['latitude'] }}, {{ $selectedAlert->metadata['longitude'] }}
                                        <a href="https://maps.google.com/?q={{ $selectedAlert->metadata['latitude'] }},{{ $selectedAlert->metadata['longitude'] }}" target="_blank" class="text-amber-400 hover:text-amber-300 ml-2 text-xs">View on map →</a>
                                    @elseif($selectedAlert->machine && $selectedAlert->machine->last_location_latitude)
                                        {{ $selectedAlert->machine->last_location_latitude }}, {{ $selectedAlert->machine->last_location_longitude }}
                                        <a href="https://maps.google.com/?q={{ $selectedAlert->machine->last_location_latitude }},{{ $selectedAlert->machine->last_location_longitude }}" target="_blank" class="text-amber-400 hover:text-amber-300 ml-2 text-xs">View on map →</a>
                                    @else
                                        <span class="text-slate-500">No coordinates available</span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Associated Geofence</div>
                                <div class="text-slate-300">
                                    @if(isset($selectedAlert->geofence) && $selectedAlert->geofence)
                                        {{ $selectedAlert->geofence->name }}
                                    @elseif(is_array($selectedAlert->metadata ?? []) && isset($selectedAlert->metadata['geofence_name']))
                                        {{ $selectedAlert->metadata['geofence_name'] }}
                                    @else
                                        <span class="text-slate-500">N/A</span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Mining Area</div>
                                <div class="text-slate-300">
                                    @if($selectedAlert->mineArea)
                                        <a href="{{ route('mine-areas.show', $selectedAlert->mineArea->id) }}" class="text-amber-400 hover:text-amber-300">{{ $selectedAlert->mineArea->name }}</a>
                                    @else
                                        <span class="text-slate-500">N/A</span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Asset</div>
                                <div class="text-slate-300">
                                    @if($selectedAlert->machine)
                                        <a href="{{ route('fleet.show', $selectedAlert->machine->id) }}" class="text-amber-400 hover:text-amber-300">{{ $selectedAlert->machine->name }}</a>
                                    @else
                                        <span class="text-slate-500">Unlinked asset</span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Timestamp</div>
                                <div class="text-slate-300">{{ $selectedAlert->created_at->format('M d, Y H:i:s') }}</div>
                            </div>

                            <div>
                                <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Management Chain</div>
                                <div class="text-slate-300 text-sm">
                                    @php
                                        // Try to resolve management chain from alert data, else fall back to mineAreaManagers
                                        $chain = $selectedAlert->management_chain ?? null;
                                        $resolved = [];
                                        if (!$chain || !is_array($chain)) {
                                            $chain = [];
                                            if (!empty($mineAreaManagers) && is_array($mineAreaManagers)) {
                                                foreach ($mineAreaManagers as $m) {
                                                    $roleKey = strtolower(trim($m['role'] ?? ''));
                                                    $chain[$roleKey] = $m;
                                                }
                                            }
                                        }

                                        // Helper to find by role keywords
                                        $findRole = function($keywords) use ($chain) {
                                            foreach ($keywords as $k) {
                                                foreach ($chain as $key => $val) {
                                                    if (stripos($key, $k) !== false || (is_string($val['role'] ?? '') && stripos($val['role'], $k) !== false)) {
                                                        return $val;
                                                    }
                                                }
                                                // direct key check
                                                if (isset($chain[$k])) return $chain[$k];
                                            }
                                            return null;
                                        };

                                        $supervisor = $findRole(['supervisor','mine supervisor','mine_supervisor']);
                                        $opsManager = $findRole(['operations manager','operations_manager','operations','ops manager','ops']);
                                        $safetyOfficer = $findRole(['safety officer','safety_officer','safety']);
                                    @endphp

                                    <ul class="space-y-2">
                                        <li>
                                            <strong>Mine Supervisor:</strong>
                                            @if($supervisor)
                                                {{ $supervisor['name'] }} <span class="text-slate-500">({{ $supervisor['role'] }})</span> — <a href="mailto:{{ $supervisor['email'] }}" class="text-amber-400">{{ $supervisor['email'] }}</a>
                                            @else
                                                <span class="text-slate-500">Unassigned</span>
                                            @endif
                                        </li>
                                        <li>
                                            <strong>Operations Manager:</strong>
                                            @if($opsManager)
                                                {{ $opsManager['name'] }} <span class="text-slate-500">({{ $opsManager['role'] }})</span> — <a href="mailto:{{ $opsManager['email'] }}" class="text-amber-400">{{ $opsManager['email'] }}</a>
                                            @else
                                                <span class="text-slate-500">Unassigned</span>
                                            @endif
                                        </li>
                                        <li>
                                            <strong>Safety Officer:</strong>
                                            @if($safetyOfficer)
                                                {{ $safetyOfficer['name'] }} <span class="text-slate-500">({{ $safetyOfficer['role'] }})</span> — <a href="mailto:{{ $safetyOfficer['email'] }}" class="text-amber-400">{{ $safetyOfficer['email'] }}</a>
                                            @else
                                                <span class="text-slate-500">Unassigned</span>
                                            @endif
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @else
                        <div>
                            <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Location</div>
                            <div class="text-slate-300 text-sm">
                                @if(is_array($selectedAlert->metadata ?? []) && isset($selectedAlert->metadata['latitude']) && isset($selectedAlert->metadata['longitude']))
                                    {{ $selectedAlert->metadata['latitude'] }}, {{ $selectedAlert->metadata['longitude'] }}
                                    <a href="https://maps.google.com/?q={{ $selectedAlert->metadata['latitude'] }},{{ $selectedAlert->metadata['longitude'] }}" target="_blank" class="text-amber-400 hover:text-amber-300 ml-2 text-xs">View on map →</a>
                                @elseif($selectedAlert->machine && $selectedAlert->machine->last_location_latitude)
                                    {{ $selectedAlert->machine->last_location_latitude }}, {{ $selectedAlert->machine->last_location_longitude }}
                                    <a href="https://maps.google.com/?q={{ $selectedAlert->machine->last_location_latitude }},{{ $selectedAlert->machine->last_location_longitude }}" target="_blank" class="text-amber-400 hover:text-amber-300 ml-2 text-xs">View on map →</a>
                                @else
                                    <span class="text-slate-500">No coordinates available</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Geofence</div>
                            <div class="text-slate-300">
                                @if(isset($selectedAlert->geofence) && $selectedAlert->geofence)
                                    {{ $selectedAlert->geofence->name }}
                                @elseif(is_array($selectedAlert->metadata ?? []) && isset($selectedAlert->metadata['geofence_name']))
                                    {{ $selectedAlert->metadata['geofence_name'] }}
                                @else
                                    <span class="text-slate-500">N/A</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Mine Area</div>
                            <div class="text-slate-300">
                                @if($selectedAlert->mineArea)
                                    <a href="{{ route('mine-areas.show', $selectedAlert->mineArea->id) }}" class="text-amber-400 hover:text-amber-300">{{ $selectedAlert->mineArea->name }}</a>
                                @else
                                    <span class="text-slate-500">N/A</span>
                                @endif
                            </div>
                        </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Mine Area Managers</div>
                        <div class="text-slate-300 text-sm">
                            @if(!empty($mineAreaManagers) && count($mineAreaManagers) > 0)
                                <ul class="list-disc pl-5">
                                    @foreach($mineAreaManagers as $mgr)
                                        <li class="text-slate-300">{{ $mgr['name'] }} <span class="text-slate-500">({{ $mgr['role'] }})</span> — <a href="mailto:{{ $mgr['email'] }}" class="text-amber-400">{{ $mgr['email'] }}</a></li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-slate-500">No managers assigned</span>
                            @endif
                        </div>
                    </div>
                </div>

                    @endif

                <div class="flex gap-2 justify-end mt-6 pt-4 border-t border-slate-700">
                    <button 
                        wire:click="closeDetails"
                        class="px-4 py-2 bg-slate-700 text-white rounded hover:bg-slate-600 transition"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Dismiss Confirmation Modal -->
    @if($showDismissConfirm && isset($pendingDismissAlertId))
        @php $pendingAlert = \App\Models\Alert::find($pendingDismissAlertId); @endphp
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6 w-96 max-h-80 overflow-y-auto">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-white">Confirm Dismissal</h3>
                    <p class="text-sm text-slate-400 mt-2">This issue remains unresolved. Dismissing may affect operational safety. Continue?</p>
                </div>

                <div class="space-y-3">
                    <div class="text-sm text-slate-300">
                        <strong>{{ $pendingAlert?->title }}</strong>
                        <div class="text-slate-400 text-xs mt-1">{{ $pendingAlert?->description }}</div>
                    </div>
                </div>

                <div class="flex gap-2 justify-end mt-6 pt-4 border-t border-slate-700">
                    <button wire:click="cancelDismiss" class="px-4 py-2 bg-slate-700 text-white rounded hover:bg-slate-600">Cancel</button>
                    <button wire:click="confirmDismiss('confirm')" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-500">Confirm Dismiss</button>
                </div>
            </div>
        </div>
    @endif
        @elseif($activeTab === 'incidents')
        <!-- ── Incident Reports Tab ───────────────────────────────────── -->
        @php
            $totalInc    = $incidentReports->total();
            $openInc     = $incidentReports->getCollection()->where('status', 'open')->count();
            $criticalInc = $incidentReports->getCollection()->where('severity', 'critical')->count();
            $resolvedInc = $incidentReports->getCollection()->whereIn('status', ['resolved', 'closed'])->count();
        @endphp

        <div class="space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <h2 class="text-2xl font-bold text-white">Incident Reports</h2>
                    <p class="text-slate-400 text-sm mt-0.5">Log and track safety, mechanical, environmental and operational incidents</p>
                </div>
                <button wire:click="openLogIncidentModal()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 text-white text-sm font-medium rounded-lg hover:bg-rose-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Log Incident
                </button>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-rose-900/30 border border-rose-700 rounded-lg p-4">
                    <p class="text-rose-400 text-sm font-medium">Total</p>
                    <p class="text-3xl font-bold text-rose-300 mt-1">{{ $totalInc }}</p>
                </div>
                <div class="bg-amber-900/30 border border-amber-700 rounded-lg p-4">
                    <p class="text-amber-400 text-sm font-medium">Open</p>
                    <p class="text-3xl font-bold text-amber-300 mt-1">{{ $openInc }}</p>
                </div>
                <div class="bg-red-900/30 border border-red-700 rounded-lg p-4">
                    <p class="text-red-400 text-sm font-medium">Critical</p>
                    <p class="text-3xl font-bold text-red-300 mt-1">{{ $criticalInc }}</p>
                </div>
                <div class="bg-green-900/30 border border-green-700 rounded-lg p-4">
                    <p class="text-green-400 text-sm font-medium">Resolved</p>
                    <p class="text-3xl font-bold text-green-300 mt-1">{{ $resolvedInc }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-slate-800 rounded-lg p-4 border border-slate-700">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5">Search</label>
                        <input type="text" wire:model.live="incidentSearch" placeholder="Title or description…"
                            class="w-full bg-slate-700 text-white px-3 py-2 rounded-lg border border-slate-600 text-sm focus:border-rose-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5">Category</label>
                        <select wire:model.live="incidentCategoryFilter"
                            class="w-full bg-slate-700 text-white px-3 py-2 rounded-lg border border-slate-600 text-sm focus:border-rose-500 focus:outline-none">
                            <option value="all">All Categories</option>
                            @foreach($incidentCategories as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5">Severity</label>
                        <select wire:model.live="incidentSeverityFilter"
                            class="w-full bg-slate-700 text-white px-3 py-2 rounded-lg border border-slate-600 text-sm focus:border-rose-500 focus:outline-none">
                            <option value="all">All Severities</option>
                            @foreach($incidentSeverities as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5">Status</label>
                        <select wire:model.live="incidentStatusFilter"
                            class="w-full bg-slate-700 text-white px-3 py-2 rounded-lg border border-slate-600 text-sm focus:border-rose-500 focus:outline-none">
                            <option value="all">All Statuses</option>
                            @foreach($incidentStatuses as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Incident List -->
            @if($incidentReports->count() > 0)
                <div class="space-y-3">
                    @foreach($incidentReports as $incident)
                        @php
                            $sevBorder = match($incident->severity) {
                                'critical' => 'border-red-700 bg-red-900/10',
                                'high'     => 'border-orange-700 bg-orange-900/10',
                                'medium'   => 'border-amber-700 bg-amber-900/10',
                                default    => 'border-slate-700',
                            };
                            $sevBadge = match($incident->severity) {
                                'critical' => 'bg-red-900 text-red-300',
                                'high'     => 'bg-orange-900 text-orange-300',
                                'medium'   => 'bg-amber-900 text-amber-300',
                                default    => 'bg-slate-700 text-slate-300',
                            };
                            $catBadgeColor = match($incident->category) {
                                'safety'           => 'bg-yellow-900 text-yellow-300',
                                'mechanical'       => 'bg-blue-900 text-blue-300',
                                'delay'            => 'bg-purple-900 text-purple-300',
                                'environmental'    => 'bg-teal-900 text-teal-300',
                                'equipment_damage' => 'bg-orange-900 text-orange-300',
                                'near_miss'        => 'bg-pink-900 text-pink-300',
                                default            => 'bg-slate-700 text-slate-400',
                            };
                            $statusBadge = match($incident->status) {
                                'open'          => 'bg-rose-900 text-rose-300',
                                'investigating' => 'bg-blue-900 text-blue-300',
                                'resolved'      => 'bg-green-900 text-green-300',
                                'closed'        => 'bg-slate-700 text-slate-400',
                                default         => 'bg-slate-700 text-slate-400',
                            };
                        @endphp
                        <div class="bg-slate-800 rounded-xl border {{ $sevBorder }} p-5 hover:border-rose-600 transition">
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <!-- Badges row -->
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded {{ $catBadgeColor }}">
                                            {{ $incidentCategories[$incident->category] ?? ucfirst($incident->category) }}
                                        </span>
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded {{ $sevBadge }}">
                                            {{ ucfirst($incident->severity) }}
                                        </span>
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded {{ $statusBadge }}">
                                            {{ $incidentStatuses[$incident->status] ?? ucfirst($incident->status) }}
                                        </span>
                                    </div>

                                    <h3 class="text-base font-semibold text-white mb-1.5">{{ $incident->title }}</h3>
                                    <p class="text-slate-400 text-sm leading-relaxed line-clamp-2 mb-3">{{ $incident->description }}</p>

                                    <!-- Meta -->
                                    <div class="flex flex-wrap gap-x-5 gap-y-1 text-xs text-slate-400">
                                        <!-- Timestamp: when it happened -->
                                        <span class="flex items-center gap-1" title="When the incident occurred">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $incident->occurred_at->format('d M Y, H:i') }}
                                        </span>
                                        <!-- Reported by -->
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            {{ $incident->reportedBy?->name ?? 'Unknown' }}
                                        </span>
                                        @if($incident->machine)
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2v-4M9 21H5a2 2 0 01-2-2v-4m0 0h18"/>
                                                </svg>
                                                <a href="{{ route('fleet.show', $incident->machine->id) }}" class="text-blue-400 hover:text-blue-300">
                                                    {{ $incident->machine->name }}
                                                </a>
                                            </span>
                                        @endif
                                        @if($incident->mineArea)
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6 3m-6-3v-13m6 3l5.553-2.776A1 1 0 0121 5.618v10.764a1 1 0 01-1.447.894L15 20m0-13v13"/>
                                                </svg>
                                                {{ $incident->mineArea->name }}
                                            </span>
                                        @endif
                                        @if($incident->resolved_at)
                                            <span class="flex items-center gap-1 text-green-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Resolved {{ $incident->resolved_at->diffForHumans() }}
                                            </span>
                                        @endif
                                    </div>
                                    @if($incident->resolution_notes)
                                        <div class="mt-2 px-3 py-2 bg-green-900/20 border border-green-800 rounded text-xs text-green-300">
                                            <span class="font-medium">Resolution: </span>{{ $incident->resolution_notes }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Actions -->
                                <div class="flex sm:flex-col gap-2 shrink-0">
                                    <button wire:click="openLogIncidentModal({{ $incident->id }})"
                                        class="px-3 py-1.5 text-xs bg-slate-700 text-slate-300 hover:bg-slate-600 rounded transition">
                                        Edit
                                    </button>
                                    @if($incident->status === 'open')
                                        <button wire:click="updateIncidentStatus({{ $incident->id }}, 'investigating')"
                                            class="px-3 py-1.5 text-xs bg-blue-700 text-blue-100 hover:bg-blue-600 rounded transition">
                                            Investigate
                                        </button>
                                    @endif
                                    @if(in_array($incident->status, ['open', 'investigating']))
                                        <button wire:click="openResolveModal({{ $incident->id }})"
                                            class="px-3 py-1.5 text-xs bg-green-700 text-green-100 hover:bg-green-600 rounded transition">
                                            Resolve
                                        </button>
                                    @endif
                                    @if($incident->status === 'resolved')
                                        <button wire:click="updateIncidentStatus({{ $incident->id }}, 'closed')"
                                            class="px-3 py-1.5 text-xs bg-slate-600 text-slate-300 hover:bg-slate-500 rounded transition">
                                            Close
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">{{ $incidentReports->links() }}</div>
            @else
                <div class="flex flex-col items-center justify-center py-20 text-slate-500">
                    <svg class="w-16 h-16 mb-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-base font-medium mb-1">No incidents found</p>
                    <p class="text-sm text-slate-600 mb-4">Click <strong class="text-slate-400">Log Incident</strong> to record a new one</p>
                    <button wire:click="openLogIncidentModal()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 text-white text-sm rounded-lg hover:bg-rose-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Log Incident
                    </button>
                </div>
            @endif

        </div>{{-- end space-y-6 --}}
        @endif{{-- end activeTab --}}

    </div>{{-- end max-7xl --}}

    <!-- ── Log / Edit Incident Modal ─────────────────────────────────── -->
    @if($showIncidentModal)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-[10000] p-4" wire:click.self="closeIncidentModal">
            <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-slate-700 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-white">
                        {{ $editingIncidentId ? 'Edit Incident' : 'Log New Incident' }}
                    </h2>
                    <button wire:click="closeIncidentModal" class="text-slate-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form wire:submit="saveIncident" class="p-6 space-y-5">

                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Title <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="incidentTitle" placeholder="Brief incident title"
                            class="w-full bg-slate-700 text-white px-3 py-2 rounded-lg border border-slate-600 focus:border-rose-500 focus:outline-none text-sm">
                        @error('incidentTitle') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Category + Severity -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Category <span class="text-rose-500">*</span></label>
                            <select wire:model="incidentCategory"
                                class="w-full bg-slate-700 text-white px-3 py-2 rounded-lg border border-slate-600 focus:border-rose-500 focus:outline-none text-sm">
                                @foreach($incidentCategories as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('incidentCategory') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Severity <span class="text-rose-500">*</span></label>
                            <select wire:model="incidentSeverity"
                                class="w-full bg-slate-700 text-white px-3 py-2 rounded-lg border border-slate-600 focus:border-rose-500 focus:outline-none text-sm">
                                @foreach($incidentSeverities as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Occurred At -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Date &amp; Time of Incident <span class="text-rose-500">*</span></label>
                        <input type="datetime-local" wire:model="incidentOccurredAt"
                            class="w-full bg-slate-700 text-white px-3 py-2 rounded-lg border border-slate-600 focus:border-rose-500 focus:outline-none text-sm">
                        @error('incidentOccurredAt') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Machine + Mine Area -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Machine</label>
                            <select wire:model="incidentMachineId"
                                class="w-full bg-slate-700 text-white px-3 py-2 rounded-lg border border-slate-600 focus:border-rose-500 focus:outline-none text-sm">
                                <option value="">— None —</option>
                                @foreach($formMachines as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->machine_type }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Mine Area</label>
                            <select wire:model="incidentMineAreaId"
                                class="w-full bg-slate-700 text-white px-3 py-2 rounded-lg border border-slate-600 focus:border-rose-500 focus:outline-none text-sm">
                                <option value="">— None —</option>
                                @foreach($formMineAreas as $area)
                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Description <span class="text-rose-500">*</span></label>
                        <textarea wire:model="incidentDescription" rows="4"
                            placeholder="Describe what happened, contributing factors, any immediate actions taken…"
                            class="w-full bg-slate-700 text-white px-3 py-2 rounded-lg border border-slate-600 focus:border-rose-500 focus:outline-none text-sm resize-none"></textarea>
                        @error('incidentDescription') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex gap-3 pt-2 border-t border-slate-700">
                        <button type="button" wire:click="closeIncidentModal"
                            class="flex-1 px-4 py-2 bg-slate-700 text-white text-sm rounded-lg hover:bg-slate-600 transition">Cancel</button>
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-rose-600 text-white text-sm font-medium rounded-lg hover:bg-rose-700 transition"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove>{{ $editingIncidentId ? 'Update Incident' : 'Log Incident' }}</span>
                            <span wire:loading class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                Saving…
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- ── Resolve Incident Modal ─────────────────────────────────────── -->
    @if($showResolveModal)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-[10000] p-4" wire:click.self="closeResolveModal">
            <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-2xl w-full max-w-lg">
                <div class="p-6 border-b border-slate-700 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-white">Resolve Incident</h2>
                    <button wire:click="closeResolveModal" class="text-slate-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Resolution Notes <span class="text-slate-500 font-normal">(optional)</span></label>
                        <textarea wire:model="incidentResolutionNotes" rows="3"
                            placeholder="Describe corrective actions taken, root cause, follow-up required…"
                            class="w-full bg-slate-700 text-white px-3 py-2 rounded-lg border border-slate-600 focus:border-green-500 focus:outline-none text-sm resize-none"></textarea>
                    </div>
                    <div class="flex gap-3 pt-2 border-t border-slate-700">
                        <button wire:click="closeResolveModal"
                            class="flex-1 px-4 py-2 bg-slate-700 text-white text-sm rounded-lg hover:bg-slate-600 transition">Cancel</button>
                        <button wire:click="resolveIncident"
                            class="flex-1 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
                            Mark Resolved
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>{{-- end min-h-screen --}}

