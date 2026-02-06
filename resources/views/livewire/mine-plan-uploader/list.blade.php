<!-- Plans List View -->
<div class="space-y-6">
    @if($plans->count() > 0)
        <!-- Current Plan Highlight -->
        @if($currentPlan)
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 border-l-4 border-blue-600 p-6 rounded-lg">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-blue-900">Current Plan</h3>
                        <p class="text-blue-700 mt-1">{{ $currentPlan->title ?: $currentPlan->file_name }}</p>
                        <div class="flex items-center gap-4 mt-3 text-sm text-blue-600">
                            <span>Version {{ $currentPlan->version }}</span>
                            <span>{{ strtoupper($currentPlan->file_type) }}</span>
                            <span>{{ $this->formatFileSize($currentPlan->file_size) }}</span>
                            <span>Uploaded by {{ $currentPlan->uploader->name ?? 'Unknown' }}</span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button 
                            wire:click="previewPlan({{ $currentPlan->id }})"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium"
                        >
                            👁️ Preview
                        </button>
                        <button 
                            wire:click="startEditPlan({{ $currentPlan->id }})"
                            class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition font-medium"
                        >
                            ✏️ Edit
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Plans Table -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-300">File Name</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-300">Version</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-300">Type</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-300">Size</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-300">Uploaded</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-300">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($plans as $plan)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-white">{{ $plan->title ?: $plan->file_name }}</p>
                                        <p class="text-xs text-slate-500 mt-1">{{ $plan->file_name }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-400">
                                    v{{ $plan->version }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                        @if($plan->file_type === 'pdf') bg-red-100 text-red-800
                                        @elseif($plan->file_type === 'dwg') bg-purple-100 text-purple-800
                                        @elseif($plan->file_type === 'dxf') bg-indigo-100 text-indigo-800
                                        @elseif(in_array($plan->file_type, ['jpg', 'png'])) bg-green-100 text-green-800
                                        @else bg-slate-100 text-gray-100
                                        @endif
                                    ">
                                        {{ strtoupper($plan->file_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-400">
                                    {{ $this->formatFileSize($plan->file_size) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-400">
                                    {{ $plan->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        @if($plan->is_current)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                ✓ Current
                                            </span>
                                        @endif
                                        @if($plan->status === 'archived')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-gray-100">
                                                Archived
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Active
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm space-x-2">
                                    <button 
                                        wire:click="previewPlan({{ $plan->id }})"
                                        class="text-blue-600 hover:text-blue-800 font-medium transition"
                                    >
                                        Preview
                                    </button>
                                    @if(!$plan->is_current && $plan->status === 'active')
                                        <button 
                                            wire:click="setAsCurrent({{ $plan->id }})"
                                            class="text-green-600 hover:text-green-800 font-medium transition"
                                        >
                                            Current
                                        </button>
                                    @endif
                                    <button 
                                        wire:click="startEditPlan({{ $plan->id }})"
                                        class="text-amber-600 hover:text-amber-800 font-medium transition"
                                    >
                                        Edit
                                    </button>
                                    @if($plan->status === 'active')
                                        <button 
                                            wire:click="archivePlan({{ $plan->id }})"
                                            class="text-orange-600 hover:text-orange-800 font-medium transition"
                                        >
                                            Archive
                                        </button>
                                    @endif
                                    <button 
                                        wire:click="deletePlan({{ $plan->id }})"
                                        wire:confirm="Delete this plan? This cannot be undone."
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
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
                {{ $plans->links('pagination::tailwind') }}
            </div>
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 0v2m0-6H9m6 0h-3m0 0H9m6 0h3m-9-3h3m-6 0H9m6 0h3"></path>
            </svg>
            <h3 class="text-lg font-semibold text-white mb-1">No plans uploaded yet</h3>
            <p class="text-gray-400 mb-4">Start by uploading your first mine plan (PDF, DWG, DXF, PNG, or JPG)</p>
            <button 
                wire:click="showUploadForm"
                class="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium"
            >
                + Upload Plan
            </button>
        </div>
    @endif
</div>
