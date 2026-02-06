<!-- Overview Tab -->
<div class="space-y-6">
    <!-- Current Assignments -->
    <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-semibold text-white">Assigned Machines</h2>
            @if($assignedMachines->count() > 0)
                <button 
                    wire:click="exportAssignmentReport"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium"
                >
                    ⬇️ Export Report
                </button>
            @endif
        </div>

        @if($assignedMachines->count() > 0)
            <div class="space-y-2 max-h-96 overflow-y-auto">
                @foreach($assignedMachines as $machine)
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg border border-slate-200 hover:bg-slate-100 transition">
                        <div class="flex-1">
                            <p class="font-medium text-white">{{ $machine->name }}</p>
                            <p class="text-sm text-gray-400">{{ $machine->model }}</p>
                            <p class="text-xs text-slate-500 mt-1">
                                Assigned: {{ $machine->pivot->assigned_at->format('M d, Y H:i') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                @if($machine->status === 'online') bg-green-100 text-green-800
                                @else bg-slate-100 text-gray-100
                                @endif
                            ">
                                {{ ucfirst($machine->status) }}
                            </span>
                            <button 
                                wire:click="unassignMachine({{ $machine->id }})"
                                class="text-red-600 hover:text-red-800 font-medium transition"
                            >
                                Remove
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Statistics -->
            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-blue-600 font-medium">Total Assigned</p>
                        <p class="text-2xl font-bold text-blue-900">{{ $assignedMachines->count() }}</p>
                    </div>
                    <div>
                        <p class="text-blue-600 font-medium">Online</p>
                        <p class="text-2xl font-bold text-blue-900">{{ $assignedMachines->where('status', 'online')->count() }}</p>
                    </div>
                    <div>
                        <p class="text-blue-600 font-medium">Offline</p>
                        <p class="text-2xl font-bold text-blue-900">{{ $assignedMachines->where('status', 'offline')->count() }}</p>
                    </div>
                    <div>
                        <p class="text-blue-600 font-medium">Coverage</p>
                        <p class="text-2xl font-bold text-blue-900">{{ round(($assignedMachines->count() / $totalMachines) * 100) }}%</p>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 0v2m0-6H9m6 0h-3"></path>
                </svg>
                <p class="text-gray-400 mb-4">No machines assigned yet</p>
                <button 
                    wire:click="switchToAssign"
                    class="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium"
                >
                    + Assign Machines
                </button>
            </div>
        @endif
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Area Info -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
            <h3 class="font-semibold text-white mb-4">Mine Area Details</h3>
            <div class="space-y-3 text-sm">
                <div>
                    <p class="text-gray-400">Type</p>
                    <p class="font-medium text-white">{{ ucfirst($mineArea->type) }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Area</p>
                    <p class="font-medium text-white">{{ number_format($mineArea->area_sqm ?? 0, 0) }} m²</p>
                </div>
                <div>
                    <p class="text-gray-400">Status</p>
                    <p class="font-medium text-white">
                        @if($mineArea->status === 'active')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-gray-100">{{ ucfirst($mineArea->status) }}</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
            <h3 class="font-semibold text-white mb-4">Recent Activity</h3>
            <div class="space-y-3 text-sm">
                <div class="p-3 bg-slate-50 rounded-lg">
                    <p class="text-gray-400">Last Updated</p>
                    <p class="font-medium text-white">{{ $mineArea->updated_at->format('M d, Y H:i') }}</p>
                </div>
                <div class="p-3 bg-slate-50 rounded-lg">
                    <p class="text-gray-400">Created</p>
                    <p class="font-medium text-white">{{ $mineArea->created_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
