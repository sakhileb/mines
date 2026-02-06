<!-- Plan Edit Form -->
<div class="space-y-6">
    <button 
        wire:click="cancelEdit"
        class="flex items-center text-blue-600 hover:text-blue-800 font-medium transition"
    >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Back to Plans
    </button>

    @if($editingPlan)
        <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-semibold text-white mb-6">Edit Plan Metadata</h2>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Form Section -->
                <div class="lg:col-span-2 space-y-4">
                    <!-- File Info -->
                    <div class="p-4 bg-slate-50 rounded-lg border border-slate-200">
                        <p class="text-sm text-gray-400">File Name</p>
                        <p class="font-medium text-white">{{ $editingPlan->file_name }}</p>
                        <div class="flex gap-4 mt-2 text-sm text-gray-400">
                            <span>v{{ $editingPlan->version }}</span>
                            <span>{{ strtoupper($editingPlan->file_type) }}</span>
                            <span>{{ number_format($editingPlan->file_size / 1024, 1) }} KB</span>
                        </div>
                    </div>

                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Title *</label>
                        <input 
                            type="text" 
                            wire:model="title"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                @error('title') border-red-500 @enderror"
                        />
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                        <textarea 
                            wire:model="description"
                            rows="3"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        ></textarea>
                    </div>

                    <!-- Georeferencing -->
                    <div class="border-t border-slate-200 pt-4">
                        <h3 class="font-semibold text-white mb-4">Georeferencing</h3>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Reference Latitude</label>
                                <input 
                                    type="number" 
                                    step="0.0001"
                                    wire:model="refPointLat"
                                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Reference Longitude</label>
                                <input 
                                    type="number" 
                                    step="0.0001"
                                    wire:model="refPointLon"
                                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Scale</label>
                                <input 
                                    type="number" 
                                    step="0.1"
                                    wire:model="scale"
                                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Rotation (°)</label>
                                <input 
                                    type="number" 
                                    step="0.1"
                                    min="0"
                                    max="360"
                                    wire:model="rotation"
                                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Sidebar -->
                <div class="space-y-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="font-semibold text-blue-900 mb-2">Current Settings</h4>
                        <ul class="text-sm text-blue-800 space-y-2">
                            <li>
                                <span class="text-blue-600">Scale:</span> {{ $scale }}
                            </li>
                            <li>
                                <span class="text-blue-600">Rotation:</span> {{ $rotation }}°
                            </li>
                            @if($refPointLat && $refPointLon)
                                <li>
                                    <span class="text-blue-600">Ref Point:</span>
                                    <br/>{{ number_format($refPointLat, 4) }}, {{ number_format($refPointLon, 4) }}
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 justify-end mt-6 pt-6 border-t border-slate-200">
                <button 
                    wire:click="cancelEdit"
                    class="px-6 py-2 bg-slate-200 text-white rounded-lg hover:bg-slate-300 transition font-medium"
                >
                    Cancel
                </button>
                <button 
                    wire:click="updatePlanMetadata"
                    wire:loading.attr="disabled"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove>Save Changes</span>
                    <span wire:loading>Saving...</span>
                </button>
            </div>
        </div>
    @endif
</div>
