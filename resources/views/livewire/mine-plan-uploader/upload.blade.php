<!-- File Upload Form -->
<div class="space-y-6">
    <!-- Upload Card -->
    <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-8">
        <h2 class="text-2xl font-semibold text-white mb-6">Upload New Mine Plan</h2>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Section -->
            <div class="lg:col-span-2 space-y-4">
                <!-- File Input -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Plan File *
                    </label>
                    <div class="relative border-2 border-dashed border-slate-300 rounded-lg p-8 hover:border-blue-500 transition"
                        @if($file) x-data="{ isDragging: false }"
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="isDragging = false; $wire.upload('file', $event.dataTransfer.files[0])"
                        :class="{ 'border-blue-500 bg-blue-50': isDragging }"
                        @endif>
                        @if($file)
                            <div class="text-center">
                                <svg class="w-12 h-12 text-green-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="font-medium text-white">{{ $file->getClientOriginalName() }}</p>
                                <p class="text-sm text-gray-400 mt-1">{{ number_format($file->getSize() / 1024, 1) }} KB</p>
                                <button 
                                    type="button"
                                    wire:click="$set('file', null)"
                                    class="mt-3 text-red-600 hover:text-red-800 font-medium transition"
                                >
                                    Change File
                                </button>
                            </div>
                        @else
                            <div class="text-center">
                                <svg class="w-12 h-12 text-slate-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="font-medium text-white">Drop file here or click to browse</p>
                                <p class="text-sm text-gray-400 mt-1">PDF, DWG, DXF, PNG, JPG (up to 100MB)</p>
                            </div>
                        @endif
                        <input 
                            type="file"
                            wire:model.live="file"
                            accept=".pdf,.dwg,.dxf,.png,.jpg,.jpeg"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                        />
                    </div>
                    @error('file')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Title *</label>
                    <input 
                        type="text" 
                        wire:model="title"
                        placeholder="e.g., North Pit A Survey - Jan 2026"
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
                        placeholder="Notes about this plan..."
                        rows="3"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    ></textarea>
                </div>

                <!-- Georeferencing -->
                <div class="border-t border-slate-200 pt-4">
                    <h3 class="font-semibold text-white mb-4">Georeferencing (Optional)</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Reference Latitude</label>
                            <input 
                                type="number" 
                                step="0.0001"
                                wire:model="refPointLat"
                                placeholder="-33.8688"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            @error('refPointLat')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Reference Longitude</label>
                            <input 
                                type="number" 
                                step="0.0001"
                                wire:model="refPointLon"
                                placeholder="151.2093"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            @error('refPointLon')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Scale</label>
                            <input 
                                type="number" 
                                step="0.1"
                                wire:model="scale"
                                placeholder="1.0"
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
                                placeholder="0.0"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>
                    </div>
                </div>

                @if($uploadError)
                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-700">{{ $uploadError }}</p>
                    </div>
                @endif
            </div>

            <!-- Info Sidebar -->
            <div class="space-y-4">
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <h4 class="font-semibold text-amber-900 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Supported Formats
                    </h4>
                    <ul class="text-sm text-amber-800 space-y-2">
                        <li class="flex items-start gap-2">
                            <span class="text-green-600 font-bold">✓</span>
                            <span><strong>DWG/DXF</strong> - AutoCAD, Surpac, Deswik, Vulcan</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-green-600 font-bold">✓</span>
                            <span><strong>PDF</strong> - Any mine planning software export</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-green-600 font-bold">✓</span>
                            <span><strong>PNG/JPG</strong> - Survey maps, aerial imagery</span>
                        </li>
                    </ul>
                    <div class="mt-3 pt-3 border-t border-amber-200">
                        <p class="text-xs text-amber-700 font-medium">Maximum file size: 100 MB</p>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-semibold text-blue-900 mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                        </svg>
                        Popular Software
                    </h4>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• Surpac</li>
                        <li>• Deswik</li>
                        <li>• Vulcan</li>
                        <li>• MineSched</li>
                        <li>• AutoCAD</li>
                        <li>• Maptek</li>
                    </ul>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h4 class="font-semibold text-green-900 mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Georeferencing
                    </h4>
                    <p class="text-sm text-green-800">
                        Provide reference coordinates and scale to align plans with the mine area on the map.
                    </p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 justify-end mt-6 pt-6 border-t border-slate-200">
            <button 
                wire:click="cancelUpload"
                class="px-6 py-2 bg-slate-200 text-white rounded-lg hover:bg-slate-300 transition font-medium"
            >
                Cancel
            </button>
            <button 
                wire:click="upload"
                wire:loading.attr="disabled"
                @if(!$file) disabled @endif
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span wire:loading.remove>Upload Plan</span>
                <span wire:loading>Uploading...</span>
            </button>
        </div>
    </div>
</div>
