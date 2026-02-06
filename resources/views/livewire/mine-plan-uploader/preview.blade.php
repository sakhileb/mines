<!-- Plan Preview View -->
<div class="space-y-6">
    <button 
        wire:click="$set('previewMode', 'list')"
        class="flex items-center text-blue-600 hover:text-blue-800 font-medium transition"
    >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Back to Plans
    </button>

    @if($previewPlan)
        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg overflow-hidden">
            <!-- Preview Header -->
            <div class="bg-slate-50 border-b border-slate-200 px-8 py-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-white">{{ $previewPlan->title ?: $previewPlan->file_name }}</h2>
                        <p class="text-gray-400 mt-1">{{ $previewPlan->description }}</p>
                        <div class="flex items-center gap-4 mt-3 text-sm text-gray-400">
                            <span>v{{ $previewPlan->version }}</span>
                            <span>{{ strtoupper($previewPlan->file_type) }}</span>
                            <span>{{ $this->formatFileSize($previewPlan->file_size) }}</span>
                            <span>{{ $previewPlan->created_at->format('M d, Y H:i') }}</span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button 
                            wire:click="downloadPlan({{ $previewPlan->id }})"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium"
                        >
                            ⬇️ Download
                        </button>
                        <button 
                            wire:click="startEditPlan({{ $previewPlan->id }})"
                            class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition font-medium"
                        >
                            ✏️ Edit
                        </button>
                    </div>
                </div>
            </div>

            <!-- Preview Content -->
            <div class="p-8">
                @if(in_array($previewPlan->file_type, ['jpg', 'png']))
                    <!-- Image Preview -->
                    <div class="space-y-4">
                        <div class="bg-slate-100 rounded-lg overflow-hidden border border-slate-300">
                            <img 
                                src="{{ route('mine-plans.preview', $previewPlan->id) }}" 
                                alt="{{ $previewPlan->file_name }}"
                                class="w-full h-auto max-h-96 object-contain"
                            />
                        </div>

                        @if($previewPlan->reference_point_lat && $previewPlan->reference_point_lon)
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <h3 class="font-semibold text-blue-900 mb-2">Georeferencing Information</h3>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <p class="text-blue-600">Reference Point</p>
                                        <p class="font-mono text-blue-900">
                                            {{ number_format($previewPlan->reference_point_lat, 4) }},
                                            {{ number_format($previewPlan->reference_point_lon, 4) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-blue-600">Scale</p>
                                        <p class="font-mono text-blue-900">{{ $previewPlan->scale }}</p>
                                    </div>
                                    <div>
                                        <p class="text-blue-600">Rotation</p>
                                        <p class="font-mono text-blue-900">{{ $previewPlan->rotation_degrees }}°</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @elseif($previewPlan->file_type === 'pdf')
                    <!-- PDF Preview -->
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-red-400 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M5 3a2 2 0 012-2h6a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V3z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-white mb-2">PDF Plan</h3>
                        <p class="text-gray-400 mb-4">Online PDF preview not available</p>
                        <button 
                            wire:click="downloadPlan({{ $previewPlan->id }})"
                            class="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium"
                        >
                            ⬇️ Download to View
                        </button>
                    </div>
                @elseif(in_array($previewPlan->file_type, ['dwg', 'dxf']))
                    <!-- CAD File Preview -->
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-purple-400 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M5 3a2 2 0 012-2h6a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V3z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-white mb-2">CAD Plan ({{ strtoupper($previewPlan->file_type) }})</h3>
                        <p class="text-gray-400 mb-4">CAD files require specialized viewers</p>
                        <button 
                            wire:click="downloadPlan({{ $previewPlan->id }})"
                            class="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium"
                        >
                            ⬇️ Download to View
                        </button>
                    </div>
                @endif
            </div>

            <!-- Metadata Footer -->
            <div class="bg-slate-50 border-t border-slate-200 px-8 py-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-gray-400">Uploaded By</p>
                        <p class="font-medium text-white">{{ $previewPlan->uploader->name ?? 'Unknown' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Status</p>
                        <p class="font-medium text-white">
                            @if($previewPlan->is_current)
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">Current</span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-slate-100 text-gray-100">{{ ucfirst($previewPlan->status) }}</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-400">Version</p>
                        <p class="font-medium text-white">{{ $previewPlan->version }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Uploaded</p>
                        <p class="font-medium text-white">{{ $previewPlan->created_at->format('M d') }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
