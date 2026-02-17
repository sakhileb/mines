<div class="h-screen flex flex-col bg-gray-900">
    <!-- Header -->
    <div class="bg-gray-800 border-b border-gray-700 p-6">
        <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="text-3xl">📊</span>
                    Production Dashboard
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Track loads, cycles, tonnages, and BCM's across your fleet
                </p>
            </div>

            <!-- Date Filters -->
            <div class="flex flex-wrap gap-2 items-end">
                <div class="flex gap-2">
                    <button wire:click="$set('dateFilter', 'day')" 
                        class="px-4 py-2 rounded-lg transition-all {{ $dateFilter === 'day' ? 'bg-amber-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        Today
                    </button>
                    <button wire:click="$set('dateFilter', 'week')" 
                        class="px-4 py-2 rounded-lg transition-all {{ $dateFilter === 'week' ? 'bg-amber-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        Week
                    </button>
                    <button wire:click="$set('dateFilter', 'month')" 
                        class="px-4 py-2 rounded-lg transition-all {{ $dateFilter === 'month' ? 'bg-amber-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        Month
                    </button>
                    <button wire:click="$set('dateFilter', 'year')" 
                        class="px-4 py-2 rounded-lg transition-all {{ $dateFilter === 'year' ? 'bg-amber-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        Year
                    </button>
                </div>

                <!-- Custom Date Range Picker -->
                <div class="flex gap-2 items-center bg-white dark:bg-gray-800 rounded-lg px-3 py-2 border border-gray-300 dark:border-gray-600">
                    <input type="date" wire:model.live="startDate" 
                        class="bg-transparent border-0 text-sm focus:ring-0 dark:text-white text-gray-900 px-4 py-2 h-8">
                    <span class="text-gray-500 dark:text-gray-400">→</span>
                    <input type="date" wire:model.live="endDate" 
                        class="bg-transparent border-0 text-sm focus:ring-0 dark:text-white text-gray-900 px-4 py-2 h-8">
                </div>
            </div>
        </div>

        <!-- Production Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 mb-8">
            <!-- Total Loads -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow p-3 text-white">
                <div class="flex items-center justify-between mb-1.5">
                    <div class="p-1.5 bg-white/20 rounded backdrop-blur-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl font-bold mb-0">{{ number_format($summary['total_loads']) }}</h3>
                <p class="text-[10px] opacity-90">Total Loads</p>
            </div>

            <!-- Total Cycles -->
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow p-3 text-white">
                <div class="flex items-center justify-between mb-1.5">
                    <div class="p-1.5 bg-white/20 rounded backdrop-blur-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl font-bold mb-0">{{ number_format($summary['total_cycles']) }}</h3>
                <p class="text-[10px] opacity-90">Total Cycles</p>
            </div>

            <!-- Total Tonnage -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow p-3 text-white">
                <div class="flex items-center justify-between mb-1.5">
                    <div class="p-1.5 bg-white/20 rounded backdrop-blur-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl font-bold mb-0">{{ number_format($summary['total_tonnage'], 2) }}</h3>
                <p class="text-[10px] opacity-90">Tonnage (T)</p>
            </div>

            <!-- Total BCM -->
            <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-lg shadow p-3 text-white">
                <div class="flex items-center justify-between mb-1.5">
                    <div class="p-1.5 bg-white/20 rounded backdrop-blur-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl font-bold mb-0">{{ number_format($summary['total_bcm'], 2) }}</h3>
                <p class="text-[10px] opacity-90">BCM (m³)</p>
            </div>

            <!-- Active Areas -->
            <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg shadow p-3 text-white">
                <div class="flex items-center justify-between mb-1.5">
                    <div class="p-1.5 bg-white/20 rounded backdrop-blur-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6 3m-6-3v-13m6 3l5.553-2.776A1 1 0 0121 5.618v10.764a1 1 0 01-1.447.894L15 20m0-13v13"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl font-bold mb-0">{{ number_format($summary['active_areas']) }}</h3>
                <p class="text-[10px] opacity-90">Active Areas</p>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Daily Production Trend -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Daily Production Trend
                </h2>

                @if(count($dailyChart) > 0)
                    <div class="space-y-6">
                        @php 
                            $maxTonnage = collect($dailyChart)->max('tonnage') ?: 1;
                            $maxLoads = collect($dailyChart)->max('loads') ?: 1;
                        @endphp

                        <!-- Tonnage Chart -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Tonnage (T)</h3>
                            <div class="flex items-end gap-1 h-32">
                                @foreach($dailyChart as $day)
                                    <div class="flex-1 flex flex-col items-center gap-1 group">
                                        <div class="w-full bg-green-500 hover:bg-green-600 rounded-t transition-all relative" 
                                             style="height: {{ $maxTonnage > 0 ? ($day['tonnage'] / $maxTonnage * 100) : 0 }}%"
                                             title="{{ $day['date'] }}: {{ number_format($day['tonnage'], 2) }}T">
                                            <span class="hidden group-hover:block absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded whitespace-nowrap z-10">
                                                {{ number_format($day['tonnage'], 2) }}T
                                            </span>
                                        </div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 rotate-45 origin-left mt-2">{{ $day['date'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Loads Chart -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Loads</h3>
                            <div class="flex items-end gap-1 h-32">
                                @foreach($dailyChart as $day)
                                    <div class="flex-1 flex flex-col items-center gap-1 group">
                                        <div class="w-full bg-blue-500 hover:bg-blue-600 rounded-t transition-all relative" 
                                             style="height: {{ $maxLoads > 0 ? ($day['loads'] / $maxLoads * 100) : 0 }}%"
                                             title="{{ $day['date'] }}: {{ number_format($day['loads']) }} loads">
                                            <span class="hidden group-hover:block absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded whitespace-nowrap z-10">
                                                {{ number_format($day['loads']) }} loads
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center h-64 text-gray-500 dark:text-gray-400">
                        <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <p class="text-sm">No production data available for this period</p>
                    </div>
                @endif
            </div>

            <!-- Material Type Breakdown -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                    Material Breakdown
                </h2>

                @if(count($materialBreakdown) > 0)
                    <div class="space-y-4">
                        @php 
                            $maxMaterialTonnage = collect($materialBreakdown)->max('tonnage') ?: 1;
                            $colors = ['bg-blue-500', 'bg-green-500', 'bg-purple-500', 'bg-amber-500', 'bg-red-500', 'bg-indigo-500'];
                        @endphp
                        
                        @foreach($materialBreakdown as $index => $material)
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $material['material'] }}</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ number_format($material['tonnage'], 2) }}T</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="{{ $colors[$index % count($colors)] }} h-2 rounded-full transition-all" 
                                         style="width: {{ ($material['tonnage'] / $maxMaterialTonnage * 100) }}%"></div>
                                </div>
                                <div class="flex justify-between items-center mt-1">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($material['loads']) }} loads</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $material['records'] }} records</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center h-64 text-gray-500 dark:text-gray-400">
                        <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        </svg>
                        <p class="text-sm">No material data available</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Operator Fatigue Management Section -->
        <div class="mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Operator Fatigue Management
                    </h2>
                    <div class="flex gap-2 text-xs">
                        <span class="px-2 py-1 rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">● Normal</span>
                        <span class="px-2 py-1 rounded bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">● Caution</span>
                        <span class="px-2 py-1 rounded bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">● Alert</span>
                        <span class="px-2 py-1 rounded bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">● Critical</span>
                    </div>
                </div>

                @if(count($fatigueData) > 0)
                    <!-- Fatigue Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-lg p-4 border border-green-200 dark:border-green-700">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-2xl font-bold text-green-700 dark:text-green-300">{{ $fatigueStats['well_rested'] }}</p>
                                    <p class="text-xs text-green-600 dark:text-green-400">Well Rested</p>
                                </div>
                                <div class="p-2 bg-green-200 dark:bg-green-700 rounded-lg">
                                    <svg class="w-5 h-5 text-green-700 dark:text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 rounded-lg p-4 border border-yellow-200 dark:border-yellow-700">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-300">{{ $fatigueStats['needs_monitoring'] }}</p>
                                    <p class="text-xs text-yellow-600 dark:text-yellow-400">Need Monitoring</p>
                                </div>
                                <div class="p-2 bg-yellow-200 dark:bg-yellow-700 rounded-lg">
                                    <svg class="w-5 h-5 text-yellow-700 dark:text-yellow-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 rounded-lg p-4 border border-orange-200 dark:border-orange-700">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-2xl font-bold text-orange-700 dark:text-orange-300">{{ $fatigueStats['high_fatigue'] }}</p>
                                    <p class="text-xs text-orange-600 dark:text-orange-400">High Fatigue</p>
                                </div>
                                <div class="p-2 bg-orange-200 dark:bg-orange-700 rounded-lg">
                                    <svg class="w-5 h-5 text-orange-700 dark:text-orange-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-lg p-4 border border-red-200 dark:border-red-700">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-2xl font-bold text-red-700 dark:text-red-300">{{ $fatigueStats['needs_rest'] }}</p>
                                    <p class="text-xs text-red-600 dark:text-red-400">Needs Rest</p>
                                </div>
                                <div class="p-2 bg-red-200 dark:bg-red-700 rounded-lg">
                                    <svg class="w-5 h-5 text-red-700 dark:text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Operator Fatigue Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Operator</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Machine</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Shift</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Hours</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Consec. Days</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fatigue Level</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($fatigueData as $fatigue)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white text-xs font-bold">
                                                    {{ substr($fatigue['operator_name'], 0, 2) }}
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $fatigue['operator_name'] }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="text-xs font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-gray-700 dark:text-gray-300">
                                                {{ $fatigue['machine_name'] ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="text-xs px-2 py-1 rounded font-medium
                                                {{ $fatigue['shift_type'] === 'morning' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200' : '' }}
                                                {{ $fatigue['shift_type'] === 'afternoon' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' : '' }}
                                                {{ $fatigue['shift_type'] === 'night' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200' : '' }}">
                                                {{ ucfirst($fatigue['shift_type']) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm text-gray-900 dark:text-white font-medium">
                                            {{ number_format($fatigue['hours_worked'], 1) }}h
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm text-gray-900 dark:text-white font-medium">
                                            {{ number_format($fatigue['consecutive_days'], 0) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="flex flex-col items-center gap-1">
                                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                    <div class="h-2 rounded-full transition-all
                                                        {{ $fatigue['fatigue_score'] < 20 ? 'bg-green-500' : '' }}
                                                        {{ $fatigue['fatigue_score'] >= 20 && $fatigue['fatigue_score'] < 40 ? 'bg-yellow-500' : '' }}
                                                        {{ $fatigue['fatigue_score'] >= 40 && $fatigue['fatigue_score'] < 60 ? 'bg-orange-500' : '' }}
                                                        {{ $fatigue['fatigue_score'] >= 60 ? 'bg-red-500' : '' }}" 
                                                        style="width: {{ $fatigue['fatigue_score'] }}%">
                                                    </div>
                                                </div>
                                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ $fatigue['fatigue_score'] }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            @if($fatigue['alert_level'] === 'none')
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    Normal
                                                </span>
                                            @elseif($fatigue['alert_level'] === 'low' || $fatigue['alert_level'] === 'medium')
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                    Monitor
                                                </span>
                                            @elseif($fatigue['alert_level'] === 'high')
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                                    Caution
                                                </span>
                                            @else
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                    Rest Required
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-16 text-gray-500 dark:text-gray-400">
                        <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <p class="text-sm">No fatigue data available for this period</p>
                        <p class="text-xs mt-2">Operator fatigue tracking will appear here once shifts are logged</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Area Performance Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6 3m-6-3v-13m6 3l5.553-2.776A1 1 0 0121 5.618v10.764a1 1 0 01-1.447.894L15 20m0-13v13"/>
                    </svg>
                    Mine Area Performance
                </h2>
            </div>
            
            @if(count($areaPerformance) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Area</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Loads</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cycles</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tonnage</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">BCM</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($areaPerformance as $area)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $area['area_name'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ $area['area_type'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900 dark:text-white font-medium">
                                        {{ number_format($area['loads']) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900 dark:text-white font-medium">
                                        {{ number_format($area['cycles']) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900 dark:text-white font-medium">
                                        {{ number_format($area['tonnage'], 2) }}T
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900 dark:text-white font-medium">
                                        {{ number_format($area['bcm'], 2) }}m³
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-16 text-gray-500 dark:text-gray-400">
                    <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6 3m-6-3v-13m6 3l5.553-2.776A1 1 0 0121 5.618v10.764a1 1 0 01-1.447.894L15 20m0-13v13"/>
                    </svg>
                    <p class="text-sm">No area performance data available for this period</p>
                    <p class="text-xs mt-2">Production records will appear here once data is recorded</p>
                </div>
            @endif
        </div>
    </div>
</div>
