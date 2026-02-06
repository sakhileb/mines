<!-- List View -->
<div class="space-y-6">
    <!-- Toolbar -->
    <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-end">
            <!-- Search -->
            <div class="flex-1 min-w-0">
                <label class="block text-sm font-medium text-gray-300 mb-2">Search</label>
                <input 
                    type="text" 
                    wire:model.live="searchTerm"
                    placeholder="Search mine areas..."
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>

            <!-- Type Filter -->
            <div class="w-full sm:w-48">
                <label class="block text-sm font-medium text-gray-300 mb-2">Type</label>
                <select 
                    wire:model.live="filterType"
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">All Types</option>
                    <option value="pit">Pit</option>
                    <option value="stockpile">Stockpile</option>
                    <option value="dump">Dump</option>
                    <option value="processing">Processing</option>
                    <option value="facility">Facility</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="w-full sm:w-48">
                <label class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                <select 
                    wire:model.live="filterStatus"
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="archived">Archived</option>
                </select>
            </div>

            <!-- Create Button -->
            <button 
                type="button"
                wire:click="startCreate"
                class="w-full sm:w-auto px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium shadow-md"
            >
                + New Mine Area
            </button>
        </div>
    </div>

    <!-- Mine Areas Table -->
    @if ($mineAreas->count() > 0)
        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-700 border-b border-gray-600">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-300">Name</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-300">Type</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-300">Area</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-300">Machines</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-300">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach ($mineAreas as $area)
                            <tr class="hover:bg-gray-700 hover:bg-opacity-50 transition">
                                <td class="px-6 py-4 text-sm font-medium text-white">
                                    {{ $area->name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-400">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                        @if($area->type === 'pit') bg-amber-100 text-amber-800
                                        @elseif($area->type === 'stockpile') bg-orange-100 text-orange-800
                                        @elseif($area->type === 'dump') bg-red-100 text-red-800
                                        @elseif($area->type === 'processing') bg-blue-100 text-blue-800
                                        @else bg-green-100 text-green-800
                                        @endif
                                    ">
                                        {{ ucfirst($area->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-400">
                                    {{ number_format($area->area_sqm ?? 0, 0) }} m²
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-400">
                                    {{ $area->machines->count() }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <button 
                                        wire:click="toggleStatus({{ $area->id }})"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium transition
                                            @if($area->status === 'active') 
                                                bg-green-100 text-green-800 hover:bg-green-200
                                            @else 
                                                bg-gray-600 text-gray-200 hover:bg-gray-500
                                            @endif
                                        "
                                    >
                                        <span class="w-2 h-2 rounded-full mr-2 
                                            @if($area->status === 'active') bg-green-600 @else bg-gray-400 @endif
                                        "></span>
                                        {{ ucfirst($area->status) }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-sm space-x-2">
                                    <button 
                                        wire:click="viewDetails({{ $area->id }})"
                                        class="text-blue-600 hover:text-blue-800 font-medium transition"
                                    >
                                        View
                                    </button>
                                    <a 
                                        href="{{ route('mine-areas.plans', $area) }}"
                                        class="text-amber-600 hover:text-amber-800 font-medium transition"
                                    >
                                        Plans
                                    </a>
                                    <button 
                                        wire:click="startEdit({{ $area->id }})"
                                        class="text-green-600 hover:text-green-800 font-medium transition"
                                    >
                                        Edit
                                    </button>
                                    <button 
                                        wire:click="delete({{ $area->id }})"
                                        wire:confirm="Are you sure you want to delete this mine area?"
                                        class="text-red-600 hover:text-red-800 font-medium transition"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-gray-700 bg-opacity-50 px-6 py-4 border-t border-gray-600">
                {{ $mineAreas->links('pagination::tailwind') }}
            </div>
        </div>
    @else
        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-white mb-1">No mine areas found</h3>
            <p class="text-gray-400 mb-4">Get started by creating your first mine area</p>
            <button 
                wire:click="startCreate"
                class="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium"
            >
                + Create Mine Area
            </button>
        </div>
    @endif
</div>
