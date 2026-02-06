<div class="p-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold">Fuel Management</h1>
            <p class="text-gray-600 mt-2">Monitor fuel levels, track consumption, and manage fuel operations</p>
        </div>
        <div class="flex items-center gap-3">
            <button 
                wire:click="openTankModal" 
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Fuel Tank
            </button>
            <button 
                wire:click="openAllocationModal" 
                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Set Monthly Allocation
            </button>
        </div>
    </div>

    <!-- Current Month Allocation Card -->
    @if($currentAllocation)
    <div class="card bg-gradient-to-br from-blue-900 to-blue-800 text-white mb-6 border border-blue-700">
        <div class="card-body">
            <div class="flex items-center justify-between mb-2">
                <h2 class="card-title text-2xl">{{ $currentAllocation->period_name }} Fuel Allocation</h2>
                @if($currentAllocation->mine_area_id && $currentAllocation->mineArea)
                    <span class="badge badge-lg bg-purple-600 text-white border-purple-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        {{ $currentAllocation->mineArea->name }}
                    </span>
                @else
                    <span class="badge badge-lg bg-gray-600 text-white border-gray-500">
                        General Team Allocation
                    </span>
                @endif
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                <div class="stat bg-white/10 rounded-lg p-4">
                    <div class="stat-title text-blue-200">Allocated</div>
                    <div class="stat-value text-2xl">{{ number_format($currentAllocation->allocated_liters) }}L</div>
                    <div class="stat-desc text-blue-300">R{{ number_format($currentAllocation->total_budget_zar, 2) }}</div>
                </div>
                <div class="stat bg-white/10 rounded-lg p-4">
                    <div class="stat-title text-blue-200">Consumed</div>
                    <div class="stat-value text-2xl {{ $currentAllocation->isExceeded() ? 'text-red-400' : '' }}">
                        {{ number_format($currentAllocation->consumed_liters) }}L
                    </div>
                    <div class="stat-desc text-blue-300">R{{ number_format($currentAllocation->spent_zar, 2) }}</div>
                </div>
                <div class="stat bg-white/10 rounded-lg p-4">
                    <div class="stat-title text-blue-200">Remaining</div>
                    <div class="stat-value text-2xl {{ $currentAllocation->isNearingLimit() ? 'text-yellow-400' : 'text-green-400' }}">
                        {{ number_format($currentAllocation->remaining_liters) }}L
                    </div>
                    <div class="stat-desc text-blue-300">R{{ number_format($currentAllocation->remaining_budget_zar, 2) }}</div>
                </div>
                <div class="stat bg-white/10 rounded-lg p-4">
                    <div class="stat-title text-blue-200">Usage</div>
                    <div class="stat-value text-2xl">{{ number_format($currentAllocation->consumption_percentage, 1) }}%</div>
                    <progress class="progress progress-success w-full mt-2" value="{{ $currentAllocation->consumption_percentage }}" max="100"></progress>
                </div>
            </div>
            @if($currentAllocation->isExceeded())
                <div class="alert alert-error mt-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span><strong>Budget Exceeded!</strong> You have consumed {{ number_format($currentAllocation->consumed_liters - $currentAllocation->allocated_liters) }}L more than allocated.</span>
                </div>
            @elseif($currentAllocation->isNearingLimit())
                <div class="alert alert-warning mt-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span>You've used {{ number_format($currentAllocation->consumption_percentage, 1) }}% of your monthly allocation. Only {{ number_format($currentAllocation->remaining_liters) }}L remaining.</span>
                </div>
            @endif
        </div>
    </div>
    @else
    <div class="alert alert-info mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span>No monthly fuel allocation set for {{ now()->format('F Y') }}. Click "Set Monthly Allocation" to configure.</span>
    </div>
    @endif

    <!-- Period Selector -->
    <div class="mb-6 flex items-center gap-4">
        <select wire:model.live="selectedPeriod" class="select select-bordered bg-white text-gray-900 [&>option]:text-gray-900">
            <option value="today">Today</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
            <option value="quarter">This Quarter</option>
            <option value="year">This Year</option>
        </select>
        
        <label class="flex items-center gap-2">
            <input type="checkbox" wire:model.live="showLowFuelOnly" class="checkbox checkbox-primary" />
            <span>Show Low Fuel Only</span>
        </label>

        <!-- Loading Indicator for Filter Changes -->
        <div wire:loading wire:target="selectedPeriod,showLowFuelOnly" class="flex items-center gap-2 text-sm text-blue-500">
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Loading...</span>
        </div>
    </div>

    <!-- Tank Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat bg-base-200 rounded-lg">
            <div class="stat-title">Total Tanks</div>
            <div class="stat-value text-primary">{{ $tankStats['total'] }}</div>
            <div class="stat-desc">{{ $tankStats['active'] }} active</div>
        </div>
        
        <div class="stat bg-base-200 rounded-lg">
            <div class="stat-title">Low Fuel Tanks</div>
            <div class="stat-value text-warning">{{ $tankStats['low_fuel'] }}</div>
            <div class="stat-desc">{{ $tankStats['critical'] }} critical</div>
        </div>
        
        <div class="stat bg-base-200 rounded-lg">
            <div class="stat-title">Total Capacity</div>
            <div class="stat-value">{{ number_format($tankStats['total_capacity']) }}L</div>
            <div class="stat-desc">{{ number_format($tankStats['current_level']) }}L current</div>
        </div>
        
        <div class="stat bg-base-200 rounded-lg">
            <div class="stat-title">Fill Percentage</div>
            <div class="stat-value text-success">
                {{ $tankStats['total_capacity'] > 0 ? number_format(($tankStats['current_level'] / $tankStats['total_capacity']) * 100, 1) : 0 }}%
            </div>
        </div>
    </div>

    <!-- Transaction Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="stat bg-base-200 rounded-lg">
            <div class="stat-title">Total Refueled</div>
            <div class="stat-value text-sm">{{ number_format($transactionStats['total_refueled']) }}L</div>
        </div>
        
        <div class="stat bg-base-200 rounded-lg">
            <div class="stat-title">Total Consumed</div>
            <div class="stat-value text-sm">{{ number_format($transactionStats['total_consumed']) }}L</div>
        </div>
        
        <div class="stat bg-base-200 rounded-lg">
            <div class="stat-title">Total Cost</div>
            <div class="stat-value text-sm text-green-600">R{{ number_format($transactionStats['total_cost'], 2) }}</div>
            <div class="stat-desc">South African Rands</div>
        </div>
        
        <div class="stat bg-base-200 rounded-lg">
            <div class="stat-title">Transactions</div>
            <div class="stat-value text-sm">{{ number_format($transactionStats['transaction_count']) }}</div>
        </div>
    </div>

    <!-- Active Alerts -->
    @if($activeAlerts->count() > 0)
    <div class="card bg-warning text-warning-content mb-6">
        <div class="card-body">
            <h2 class="card-title">Active Fuel Alerts ({{ $activeAlerts->count() }})</h2>
            <div class="space-y-2">
                @foreach($activeAlerts as $alert)
                <div class="alert alert-warning">
                    <div>
                        <strong>{{ $alert->title }}</strong>
                        <p class="text-sm">{{ $alert->message }}</p>
                        <p class="text-xs opacity-70">{{ $alert->triggered_at->diffForHumans() }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- AI-Powered Fuel Optimization Insights -->
    @if($aiRecommendations->count() > 0 || $aiInsights->count() > 0)
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-4">
            <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
            </svg>
            <h2 class="text-2xl font-bold">AI Fuel Optimization</h2>
            <span class="badge badge-primary">Powered by AI</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- AI Recommendations -->
            @if($aiRecommendations->count() > 0)
            <div class="card bg-gradient-to-br from-purple-900 to-blue-900 text-white border border-purple-700">
                <div class="card-body">
                    <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Optimization Recommendations
                    </h3>
                    <div class="space-y-3">
                        @foreach($aiRecommendations as $recommendation)
                        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                            <div class="flex items-start justify-between mb-2">
                                <span class="font-semibold">{{ $recommendation['title'] }}</span>
                                <span class="badge 
                                    @if($recommendation['priority'] === 'critical') badge-error
                                    @elseif($recommendation['priority'] === 'high') badge-warning
                                    @else badge-info
                                    @endif
                                ">{{ ucfirst($recommendation['priority']) }}</span>
                            </div>
                            <p class="text-sm text-gray-200 mb-2">{{ $recommendation['description'] }}</p>
                            @if(isset($recommendation['estimated_savings']) && $recommendation['estimated_savings'] > 0)
                            <div class="flex items-center gap-2 text-green-300 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Potential Savings: R{{ number_format($recommendation['estimated_savings'], 2) }}/month</span>
                            </div>
                            @endif
                            <div class="flex items-center gap-2 mt-2 text-xs text-gray-300">
                                <span>Confidence: {{ number_format($recommendation['confidence_score'] * 100, 0) }}%</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- AI Insights -->
            @if($aiInsights->count() > 0)
            <div class="card bg-gradient-to-br from-blue-900 to-cyan-900 text-white border border-blue-700">
                <div class="card-body">
                    <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Consumption Insights
                    </h3>
                    <div class="space-y-3">
                        @foreach($aiInsights as $insight)
                        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                            <div class="flex items-start justify-between mb-2">
                                <span class="font-semibold">{{ $insight['title'] }}</span>
                                <span class="badge 
                                    @if($insight['severity'] === 'critical') badge-error
                                    @elseif($insight['severity'] === 'warning') badge-warning
                                    @elseif($insight['severity'] === 'success') badge-success
                                    @else badge-info
                                    @endif
                                ">{{ ucfirst($insight['type']) }}</span>
                            </div>
                            <p class="text-sm text-gray-200">{{ $insight['description'] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Fuel Tanks & Top Consumers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Fuel Tanks -->
        <div class="card bg-base-200">
            <div class="card-body">
                <h2 class="card-title">Fuel Tanks</h2>
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Tank</th>
                                <th>Location</th>
                                <th>Level</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tanks as $tank)
                            <tr>
                                <td>
                                    <strong>{{ $tank->name }}</strong><br>
                                    <span class="text-xs">{{ $tank->fuel_type }}</span>
                                </td>
                                <td>{{ $tank->mineArea?->name ?? 'N/A' }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <progress class="progress w-20 
                                            @if($tank->isCritical()) progress-error
                                            @elseif($tank->isBelowMinimum()) progress-warning
                                            @else progress-success
                                            @endif
                                        " value="{{ $tank->fill_percentage }}" max="100"></progress>
                                        <span class="text-sm">{{ number_format($tank->fill_percentage, 1) }}%</span>
                                    </div>
                                    <span class="text-xs">{{ number_format($tank->current_level_liters) }}L / {{ number_format($tank->capacity_liters) }}L</span>
                                </td>
                                <td>
                                    <span class="badge 
                                        @if($tank->status === 'active') badge-success
                                        @elseif($tank->status === 'maintenance') badge-warning
                                        @else badge-error
                                        @endif
                                    ">{{ $tank->status }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No fuel tanks found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Consumers -->
        <div class="card bg-base-200">
            <div class="card-body">
                <h2 class="card-title">Top Fuel Consumers</h2>
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Machine</th>
                                <th>Type</th>
                                <th>Consumed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topConsumers as $consumer)
                            <tr>
                                <td>
                                    <strong>{{ $consumer['machine']->name }}</strong>
                                </td>
                                <td>{{ $consumer['machine']->machine_type }}</td>
                                <td>
                                    <strong>{{ number_format($consumer['total_consumed']) }}L</strong>
                                    @if($consumer['total_cost'] > 0)
                                        <br><span class="text-xs text-success">R{{ number_format($consumer['total_cost'], 2) }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No consumption data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card bg-base-200">
        <div class="card-body">
            <h2 class="card-title">Recent Transactions</h2>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Tank</th>
                            <th>Machine</th>
                            <th>Quantity</th>
                            <th>Cost</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $transaction)
                        <tr>
                            <td>{{ $transaction->transaction_date->format('M d, Y H:i') }}</td>
                            <td>
                                <span class="badge 
                                    @if($transaction->transaction_type === 'dispensing') badge-primary
                                    @elseif(in_array($transaction->transaction_type, ['refill', 'delivery'])) badge-success
                                    @else badge-warning
                                    @endif
                                ">{{ $transaction->transaction_type }}</span>
                            </td>
                            <td>{{ $transaction->fuelTank?->name ?? 'N/A' }}</td>
                            <td>{{ $transaction->machine?->name ?? '-' }}</td>
                            <td>{{ number_format($transaction->quantity_liters) }}L</td>
                            <td class="text-success font-semibold">R{{ number_format($transaction->total_cost, 2) }}</td>
                            <td>{{ $transaction->user?->name ?? 'System' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No recent transactions</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Monthly Allocation Modal -->
    @if($showAllocationModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click="closeAllocationModal">
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4" wire:click.stop>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Set Monthly Fuel Allocation</h2>
                <button wire:click="closeAllocationModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form wire:submit="saveAllocation" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                        <select wire:model="allocationYear" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-gray-900">
                            @for($year = now()->year - 1; $year <= now()->year + 1; $year++)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </select>
                        @error('allocationYear') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                        <select wire:model="allocationMonth" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-gray-900">
                            @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $index => $month)
                                <option value="{{ $index + 1 }}">{{ $month }}</option>
                            @endforeach
                        </select>
                        @error('allocationMonth') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mine Area (Optional)</label>
                    <select wire:model="mineAreaId" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-gray-900">
                        <option value="">All Mine Areas (General Allocation)</option>
                        @foreach($mineAreas as $area)
                            <option value="{{ $area->id }}">{{ $area->name }} ({{ ucfirst($area->type) }})</option>
                        @endforeach
                    </select>
                    @error('mineAreaId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    <p class="text-xs text-gray-500 mt-1">Select a specific mine area or leave blank for general team allocation</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Allocated Liters</label>
                    <input 
                        type="number" 
                        wire:model.live="allocatedLiters" 
                        step="0.01"
                        placeholder="e.g., 50000"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                    @error('allocatedLiters') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    <p class="text-xs text-gray-500 mt-1">Enter the total liters available for this month</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fuel Price per Liter (ZAR)</label>
                    <input 
                        type="number" 
                        wire:model.live="fuelPricePerLiter" 
                        step="0.01"
                        placeholder="e.g., 23.50"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                    @error('fuelPricePerLiter') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    <p class="text-xs text-gray-500 mt-1">Current fuel price in South African Rands</p>
                </div>

                @if($allocatedLiters && $fuelPricePerLiter)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 font-medium">Total Budget:</span>
                        <span class="text-2xl font-bold text-blue-600">R{{ number_format($allocatedLiters * $fuelPricePerLiter, 2) }}</span>
                    </div>
                    <p class="text-xs text-gray-600 mt-1">{{ number_format($allocatedLiters) }} liters × R{{ number_format($fuelPricePerLiter, 2) }} per liter</p>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                    <textarea 
                        wire:model="allocationNotes" 
                        rows="3"
                        placeholder="Any additional notes or comments..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    ></textarea>
                </div>

                <div class="flex gap-3 pt-4">
                    <button 
                        type="submit" 
                        class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-medium flex items-center justify-center gap-2"
                        wire:loading.attr="disabled"
                        wire:target="saveAllocation"
                    >
                        <span wire:loading.remove wire:target="saveAllocation">Save Allocation</span>
                        <span wire:loading wire:target="saveAllocation" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Saving...
                        </span>
                    </button>
                    <button 
                        type="button" 
                        wire:click="closeAllocationModal"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors font-medium"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Fuel Tank Creation Modal -->
    @if($showTankModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click="closeTankModal">
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto" wire:click.stop>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Add New Fuel Tank</h2>
                <button wire:click="closeTankModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form wire:submit="saveTank" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tank Name *</label>
                    <input 
                        type="text" 
                        wire:model="tankName" 
                        placeholder="e.g., Main Diesel Tank A"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                    @error('tankName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tank Number</label>
                        <input 
                            type="text" 
                            wire:model="tankNumber" 
                            placeholder="e.g., T-001"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        >
                        @error('tankNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fuel Type *</label>
                        <select wire:model="tankFuelType" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-gray-900">
                            <option value="diesel">Diesel</option>
                            <option value="petrol">Petrol</option>
                            <option value="aviation_fuel">Aviation Fuel</option>
                            <option value="biodiesel">Biodiesel</option>
                        </select>
                        @error('tankFuelType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mine Area (Optional)</label>
                    <select wire:model="tankMineAreaId" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-gray-900">
                        <option value="">No specific mine area</option>
                        @foreach($mineAreas as $area)
                            <option value="{{ $area->id }}">{{ $area->name }} ({{ ucfirst($area->type) }})</option>
                        @endforeach
                    </select>
                    @error('tankMineAreaId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    <p class="text-xs text-gray-500 mt-1">Assign this tank to a specific mine area</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Capacity (Liters) *</label>
                        <input 
                            type="number" 
                            wire:model.live="tankCapacity" 
                            step="0.01"
                            placeholder="e.g., 50000"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        >
                        @error('tankCapacity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Level (Liters) *</label>
                        <input 
                            type="number" 
                            wire:model.live="tankMinimumLevel" 
                            step="0.01"
                            placeholder="e.g., 5000"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        >
                        @error('tankMinimumLevel') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        <p class="text-xs text-gray-500 mt-1">Alert threshold</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Location Description</label>
                    <input 
                        type="text" 
                        wire:model="tankLocationDescription" 
                        placeholder="e.g., Near north pit entrance"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                    @error('tankLocationDescription') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                    <textarea 
                        wire:model="tankNotes" 
                        rows="3"
                        placeholder="Any additional notes..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    ></textarea>
                </div>

                @if($tankCapacity && $tankMinimumLevel)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-gray-700 font-medium">Tank will start at full capacity</span>
                        <span class="text-lg font-bold text-blue-600">{{ number_format($tankCapacity) }}L</span>
                    </div>
                    <div class="text-sm text-gray-600">
                        <span class="font-medium">Alert at:</span> {{ number_format($tankMinimumLevel) }}L ({{ $tankCapacity > 0 ? number_format(($tankMinimumLevel / $tankCapacity) * 100, 1) : 0 }}%)
                    </div>
                </div>
                @endif

                <div class="flex gap-3 pt-4">
                    <button 
                        type="submit" 
                        class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-medium flex items-center justify-center gap-2"
                        wire:loading.attr="disabled"
                        wire:target="saveTank"
                    >
                        <span wire:loading.remove wire:target="saveTank" class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Create Tank
                        </span>
                        <span wire:loading wire:target="saveTank" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Creating...
                        </span>
                    </button>
                    <button 
                        type="button" 
                        wire:click="closeTankModal"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors font-medium"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
