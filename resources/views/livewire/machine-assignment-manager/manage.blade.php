<!-- Manage Tab - Remove machines from area -->
<div class="space-y-6">
    <!-- Toolbar -->
    <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-end">
            <div class="flex-1 min-w-0">
                <label class="block text-sm font-medium text-gray-300 mb-2">Search</label>
                <input 
                    type="text" 
                    wire:model.live="searchTerm"
                    placeholder="Search assigned machines..."
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
            </div>
            @if(count($selectedMachineIds) > 0)
                <button 
                    wire:click="unassignMultipleMachines"
                    wire:confirm="Unassign {{ count($selectedMachineIds) }} machine(s)?"
                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium"
                >
                    🗑️ Unassign Selected
                </button>
            @endif
        </div>
    </div>

    <!-- Machines List -->
    @if($assignedMachines->count() > 0)
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
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-300">Assigned</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($assignedMachines as $machine)
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
                            <td class="px-6 py-4 text-sm text-gray-400">
                                {{ $machine->pivot->assigned_at->format('M d') }}
                            </td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <button 
                                    wire:click="unassignMachine({{ $machine->id }})"
                                    wire:confirm="Remove {{ $machine->name }}?"
                                    class="text-red-600 hover:text-red-800 font-medium transition"
                                >
                                    Remove
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-12 text-center">
            <p class="text-gray-400">No machines assigned to this area</p>
        </div>
    @endif
</div>
