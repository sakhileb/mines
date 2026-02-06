    <!-- Upgrade Prompt Modal -->
    @if($showUpgradePrompt)
        <div class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-8 w-full max-w-md text-center shadow-xl">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Upgrade Required</h3>
                <p class="text-gray-700 mb-4">You have reached the maximum number of machines allowed for your current subscription plan. To add more machines, please upgrade your subscription.</p>
                <a href="/billing" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">Upgrade Subscription</a>
                <button wire:click="$set('showUpgradePrompt', false)" class="ml-4 px-5 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium">Close</button>
            </div>
        </div>
    @endif
<!-- Assign Tab - Add machines to area -->
<div class="space-y-6">
    <!-- Toolbar -->
    <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-end">
            <div class="flex-1 min-w-0">
                <label class="block text-sm font-medium text-gray-300 mb-2">Search</label>
                <input 
                    type="text" 
                    wire:model.live="searchTerm"
                    placeholder="Search unassigned machines..."
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
            </div>
            <div class="w-full sm:w-auto">
                <label class="block text-sm font-medium text-gray-300 mb-2">Filter</label>
                <select 
                    wire:model.live="filterStatus"
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="">All Statuses</option>
                    <option value="online">Online</option>
                    <option value="offline">Offline</option>
                </select>
            </div>
            @if(count($selectedMachineIds) > 0)
                <button 
                    wire:click="assignSelectedMachines"
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium"
                >
                    ✓ Assign Selected ({{ count($selectedMachineIds) }})
                </button>
            @endif
        </div>
    </div>

    <!-- Info Alert -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-sm text-blue-900">
            <strong>Unassigned:</strong> {{ $unassignedCount }} machine(s) available for assignment
        </p>
    </div>

    <!-- Machines List -->
    @if($machines->count() > 0)
        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input 
                                type="checkbox"
                                wire:model.live="selectAll"
                                @change="$wire.toggleSelectAll()"
                                class="rounded"
                            />
                        </th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-300">Machine</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-300">Model</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-300">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($machines as $machine)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4">
                                <input 
                                    type="checkbox"
                                    wire:model.live="selectedMachineIds"
                                    value="{{ $machine->id }}"
                                    class="rounded"
                                />
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-white">{{ $machine->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-400">{{ $machine->model }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                    @if($machine->status === 'online') bg-green-100 text-green-800
                                    @else bg-slate-100 text-gray-100
                                    @endif
                                ">
                                    {{ ucfirst($machine->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <button 
                                    wire:click="showAssignForm({{ $machine->id }})"
                                    class="text-blue-600 hover:text-blue-800 font-medium transition"
                                >
                                    Assign
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6 border-t border-slate-200">
            {{ $machines->links('pagination::tailwind') }}
        </div>
    @else
        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-12 text-center">
            <p class="text-gray-400 mb-4">No unassigned machines available</p>
            @if($searchTerm)
                <button 
                    wire:click="$set('searchTerm', '')"
                    class="text-blue-600 hover:text-blue-800 font-medium transition"
                >
                    Clear search
                </button>
            @endif
        </div>
    @endif

    <!-- Individual Assignment Form -->
    @if($showAssignForm && $selectedMachine)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Assign Machine</h3>
                
                <div class="mb-4 p-3 bg-slate-50 rounded-lg">
                    <p class="text-sm text-gray-400">Machine</p>
                    <p class="font-medium text-white">{{ $selectedMachine->name }}</p>
                    <p class="text-sm text-gray-400 mt-1">{{ $selectedMachine->model }}</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Notes (Optional)</label>
                    <textarea 
                        wire:model="selectedNotes"
                        placeholder="Add notes for this assignment..."
                        rows="3"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900"
                    ></textarea>
                </div>

                <div class="flex gap-3">
                    <button 
                        wire:click="cancelAssignForm"
                        class="flex-1 px-4 py-2 bg-slate-200 text-white rounded-lg hover:bg-slate-300 transition font-medium"
                    >
                        Cancel
                    </button>
                    <button 
                        wire:click="assignSingleMachine({{ $selectedMachine->id }})"
                        class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium"
                    >
                        Assign
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
