<div class="min-h-screen bg-slate-900 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">Alerts</h1>
            <p class="text-slate-400">Monitor and manage machine alerts and notifications</p>
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
                    <div class="bg-slate-800 rounded-lg border {{ 
                        $alert->priority === 'critical' ? 'border-red-700 bg-red-900/10' : 
                        ($alert->priority === 'high' ? 'border-orange-700 bg-orange-900/10' : 
                        ($alert->priority === 'medium' ? 'border-yellow-700 bg-yellow-900/10' : 'border-slate-700')) 
                    }} p-4 hover:border-blue-600 transition">
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
                                    <span class="px-2 py-1 text-xs font-semibold rounded {{ 
                                        $alert->status === 'new' ? 'bg-green-900 text-green-300' : 
                                        ($alert->status === 'acknowledged' ? 'bg-blue-900 text-blue-300' : 
                                        ($alert->status === 'resolved' ? 'bg-slate-700 text-slate-300' : 'bg-slate-600 text-slate-400')) 
                                    }}">
                                        {{ ucfirst($alert->status) }}
                                    </span>

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
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6 w-96 max-h-96 overflow-y-auto">
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
                </div>

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
</div>
