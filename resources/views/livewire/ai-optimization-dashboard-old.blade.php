<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">🤖 AI Optimization Center</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    AI-powered insights and recommendations for optimizing mining operations
                </p>
            </div>
            <button
                wire:click="runAnalysis"
                wire:loading.attr="disabled"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150">
                <span wire:loading.remove wire:target="runAnalysis">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Run AI Analysis
                </span>
                <span wire:loading wire:target="runAnalysis">
                    <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Analyzing...
                </span>
            </button>
        </div>

        @if (session()->has('success'))
            <div class="mb-4 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-400 p-4">
                <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-400 p-4">
                <p class="text-sm text-red-700 dark:text-red-400">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Recommendations -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Recommendations</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['pending_recommendations'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Savings -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Potential Savings</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                                R{{ number_format($stats['total_savings'] ?? 0, 0) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Implemented -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Implemented</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['implemented_recommendations'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Agents -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active AI Agents</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['active_agents'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Predictive Alerts -->
        @if($predictiveAlerts->count() > 0)
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">⚠️ Predictive Alerts</h2>
            <div class="space-y-3">
                @foreach($predictiveAlerts as $alert)
                <div class="bg-white dark:bg-gray-800 border-l-4 @if($alert->severity === 'critical') border-red-500 @elseif($alert->severity === 'high') border-orange-500 @else border-yellow-500 @endif rounded-r-lg shadow-sm p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($alert->severity === 'critical') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                    @elseif($alert->severity === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                    @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 @endif">
                                    {{ strtoupper($alert->severity) }}
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $alert->probability * 100 }}% probability
                                </span>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $alert->title }}</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $alert->description }}</p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                                Predicted occurrence: <strong>{{ $alert->predicted_occurrence->format('M d, Y H:i') }}</strong>
                            </p>
                        </div>
                        <button
                            wire:click="acknowledgeAlert({{ $alert->id }})"
                            class="ml-4 text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                            Acknowledge
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Recent Insights -->
        @if($insights->count() > 0)
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">💡 Recent Insights</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($insights->take(6) as $insight)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border-l-4 
                    @if($insight->severity === 'critical') border-red-500
                    @elseif($insight->severity === 'warning') border-yellow-500
                    @elseif($insight->severity === 'success') border-green-500
                    @else border-blue-500 @endif">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium mb-2
                                @if($insight->severity === 'critical') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                @elseif($insight->severity === 'warning') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                @elseif($insight->severity === 'success') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @endif">
                                {{ ucfirst($insight->insight_type) }}
                            </span>
                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $insight->title }}</h4>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $insight->description }}</p>
                        </div>
                        @if(!$insight->is_read)
                        <button wire:click="markInsightAsRead({{ $insight->id }})" class="ml-2">
                            <svg class="w-5 h-5 text-gray-400 hover:text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Recommendations Section -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">📋 AI Recommendations</h2>
                
                <!-- Filters -->
                <div class="mt-4 flex flex-wrap gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                        <select wire:model.live="selectedCategory" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="all">All Categories</option>
                            <option value="fleet">Fleet</option>
                            <option value="fuel">Fuel</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="production">Production</option>
                            <option value="route">Route</option>
                            <option value="cost">Cost</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priority</label>
                        <select wire:model.live="selectedPriority" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="all">All Priorities</option>
                            <option value="critical">Critical</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="p-6">
                @if($recommendations->count() > 0)
                <div class="space-y-4">
                    @foreach($recommendations as $recommendation)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($recommendation->priority === 'critical') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                        @elseif($recommendation->priority === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                        @elseif($recommendation->priority === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @endif">
                                        {{ strtoupper($recommendation->priority) }}
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ ucfirst($recommendation->category) }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ round($recommendation->confidence_score * 100) }}% confidence
                                    </span>
                                </div>
                                
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                    {{ $recommendation->title }}
                                </h3>
                                
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                    {{ $recommendation->description }}
                                </p>

                                @if($recommendation->estimated_savings)
                                <div class="flex items-center space-x-4 text-sm">
                                    <div class="flex items-center text-green-600 dark:text-green-400">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Potential Savings: R{{ number_format($recommendation->estimated_savings, 0) }}
                                    </div>
                                </div>
                                @endif

                                @if($recommendation->estimated_efficiency_gain)
                                <div class="flex items-center space-x-4 text-sm mt-1">
                                    <div class="flex items-center text-blue-600 dark:text-blue-400">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                        </svg>
                                        Efficiency Gain: {{ round($recommendation->estimated_efficiency_gain) }}%
                                    </div>
                                </div>
                                @endif
                            </div>

                            <div class="ml-4 flex flex-col space-y-2">
                                <button
                                    wire:click="implementRecommendation({{ $recommendation->id }})"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    ✓ Implement
                                </button>
                                <button
                                    wire:click="rejectRecommendation({{ $recommendation->id }})"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    ✗ Reject
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $recommendations->links() }}
                </div>
                @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No recommendations</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Run an AI analysis to get personalized recommendations.
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
