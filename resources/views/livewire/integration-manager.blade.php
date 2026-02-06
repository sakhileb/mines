<div class="px-6 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
                    <div class="mb-4 p-4 bg-blue-900/30 rounded-lg text-blue-200 text-sm">
                        <strong>Integration Setup Guide:</strong><br>
                        1. Select your equipment manufacturer.<br>
                        2. Enter your API credentials (get these from your provider dashboard).<br>
                        3. Optionally set a custom API endpoint, sync frequency, and connection type.<br>
                        4. Test the connection before saving.<br>
                        5. For webhook integrations, copy the provided URL and set it in your provider dashboard.<br>
                        6. You will receive alerts at your notification email if sync fails.
                    </div>
            <div>
                <h2 class="text-3xl font-bold text-white">Integrations</h2>
                <p class="text-gray-400 mt-2">Manage your equipment manufacturer connections</p>
            </div>
            <button 
                wire:click="openAddModal"
                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition"
            >
                + Add Integration
            </button>
        </div>
    </div>

    <!-- Available Manufacturers Info -->
    <div class="grid grid-cols-1 lg:grid-cols-7 gap-4 mb-8">
        @foreach($availableManufacturers as $key => $mfr)
            <div class="bg-gray-800 border border-gray-700 rounded-lg p-4 text-center">
                <div class="text-3xl mb-2">{{ $mfr['icon'] }}</div>
                <h3 class="text-white font-semibold">{{ $mfr['name'] }}</h3>
                <p class="text-gray-400 text-xs mt-1">{{ $mfr['description'] }}</p>
                <div class="mt-3">
                    @if(in_array($key, array_map(fn($i) => $i['provider'], $integrations)))
                        <span class="inline-block px-2 py-1 bg-green-900 text-green-200 text-xs rounded">Connected</span>
                    @else
                        <span class="inline-block px-2 py-1 bg-gray-700 text-gray-300 text-xs rounded">Available</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Integrations List -->
    @if(count($integrations) > 0)
        <div class="bg-gray-800 border border-gray-700 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-900 border-b border-gray-700">
                        <tr>
                            <th class="px-6 py-4 text-left text-white font-semibold">Manufacturer</th>
                            <th class="px-6 py-4 text-left text-white font-semibold">Status</th>
                            <th class="px-6 py-4 text-left text-white font-semibold">Last Sync</th>
                            <th class="px-6 py-4 text-left text-white font-semibold">Created</th>
                            <th class="px-6 py-4 text-right text-white font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($integrations as $integration)
                            <tr class="border-t border-gray-700 hover:bg-gray-750 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <span class="text-2xl mr-3">
                                            {{ $availableManufacturers[$integration['provider']]['icon'] ?? '📦' }}
                                        </span>
                                        <div>
                                            <p class="text-white font-medium">
                                                {{ $availableManufacturers[$integration['provider']]['name'] ?? ucfirst($integration['provider']) }}
                                            </p>
                                            <p class="text-gray-400 text-sm">{{ $integration['provider'] }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($integration['status'] === 'connected')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-900 text-green-200">
                                            <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                                            Connected
                                        </span>
                                    @elseif($integration['status'] === 'pending')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-900 text-yellow-200">
                                            <span class="w-2 h-2 bg-yellow-400 rounded-full mr-2"></span>
                                            Pending
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-900 text-red-200">
                                            <span class="w-2 h-2 bg-red-400 rounded-full mr-2"></span>
                                            Disconnected
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="text-white text-sm">{{ $integration['last_sync_at'] }}</p>
                                        <p class="text-gray-400 text-xs">
                                            @if($integration['last_sync_status'] === 'success')
                                                <span class="text-green-400">Success</span>
                                            @elseif($integration['last_sync_status'] === 'failed')
                                                <span class="text-red-400">Failed</span>
                                            @else
                                                <span class="text-gray-400">Not synced</span>
                                            @endif
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-400 text-sm">
                                    {{ $integration['created_at'] }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button 
                                            wire:click="testConnection({{ $integration['id'] }})"
                                            class="px-3 py-2 bg-blue-900 hover:bg-blue-800 text-blue-200 rounded text-sm transition flex items-center gap-1"
                                            title="Test connection"
                                            wire:loading.attr="disabled"
                                            wire:target="testConnection({{ $integration['id'] }})"
                                        >
                                            <span wire:loading.remove wire:target="testConnection({{ $integration['id'] }})">🧪 Test</span>
                                            <span wire:loading wire:target="testConnection({{ $integration['id'] }})" class="flex items-center gap-1">
                                                <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                Testing...
                                            </span>
                                        </button>
                                        <button 
                                            wire:click="syncMachines({{ $integration['id'] }})"
                                            class="px-3 py-2 bg-green-900 hover:bg-green-800 text-green-200 rounded text-sm transition flex items-center gap-1"
                                            title="Sync machines"
                                            wire:loading.attr="disabled"
                                            wire:target="syncMachines({{ $integration['id'] }})"
                                        >
                                            <span wire:loading.remove wire:target="syncMachines({{ $integration['id'] }})">🔄 Sync</span>
                                            <span wire:loading wire:target="syncMachines({{ $integration['id'] }})" class="flex items-center gap-1">
                                                <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                Syncing...
                                            </span>
                                        </button>
                                        <button 
                                            wire:click="deleteIntegration({{ $integration['id'] }})"
                                            wire:confirm="Are you sure you want to delete this integration?"
                                            class="px-3 py-2 bg-red-900 hover:bg-red-800 text-red-200 rounded text-sm transition"
                                            title="Delete integration"
                                        >
                                            🗑️ Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-12 text-center">
            <div class="text-4xl mb-4">📦</div>
            <h3 class="text-xl font-semibold text-white mb-2">No Integrations Yet</h3>
            <p class="text-gray-400 mb-6">Get started by adding your first equipment manufacturer integration</p>
            <button 
                wire:click="openAddModal"
                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition"
            >
                + Add Your First Integration
            </button>
        </div>
    @endif

    <!-- Add Integration Modal -->
    @if($showAddModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="backdrop-filter: blur(4px);">
            <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-xl max-w-md w-full mx-4">
                <div class="p-6 border-b border-gray-700">
                    <h3 class="text-xl font-bold text-white">Add New Integration</h3>
                </div>

                <div class="p-6 space-y-4">
                    @error('general')
                        <div class="p-4 bg-red-900 border border-red-700 rounded text-red-200 text-sm">
                            {{ $message }}
                        </div>
                    @enderror

                    <!-- Provider Selection -->
                    <div>
                        <label class="block text-white font-medium mb-2">Manufacturer *</label>
                        <select 
                            wire:model="formData.provider"
                            class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded text-white focus:outline-none focus:border-blue-500"
                        >
                            <option value="">Select a manufacturer...</option>
                            @foreach($availableManufacturers as $key => $mfr)
                                <option value="{{ $key }}">
                                    {{ $mfr['icon'] }} {{ $mfr['name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('provider')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        @if($formData['provider'])
                            <div class="mt-3 text-xs text-blue-200 bg-blue-900/30 rounded p-2">
                                <strong>Integration requirements for {{ $availableManufacturers[$formData['provider']]['name'] ?? $formData['provider'] }}:</strong>
                                @switch($formData['provider'])
                                    @case('volvo')
                                    @case('cat')
                                    @case('komatsu')
                                    @case('bell')
                                        <div>Requires API Key and Secret from OEM portal.</div>
                                        @break
                                    @case('ctrack')
                                        <div>Requires API Key, Secret, and custom endpoint URL.</div>
                                        @break
                                    @case('john-deere')
                                        <div>Requires OAuth Client ID/Secret and endpoint.</div>
                                        @break
                                    @case('liebherr')
                                    @case('hyundai')
                                    @case('doosan')
                                    @case('jcb')
                                    @case('case')
                                    @case('sany')
                                    @case('xcmg')
                                    @case('kobelco')
                                    @case('new-holland')
                                    @case('takeuchi')
                                    @case('kubota')
                                    @case('bobcat')
                                    @case('yanmar')
                                        <div>Requires API Key and Secret from manufacturer.</div>
                                        @break
                                    @case('atlas-copco')
                                    @case('sandvik')
                                    @case('epiroc')
                                        <div>Requires API Key, Secret, and site code.</div>
                                        @break
                                    @default
                                        <div>Standard API credentials required.</div>
                                @endswitch
                            </div>
                        @endif
                    </div>

                    <!-- API Key -->
                    <div>
                        @if($formData['provider'] === 'ctrack')
                            <label class="block text-white font-medium mb-2 mt-4">API Key *</label>
                            <input type="password" wire:model="formData.credentials.api_key" placeholder="Enter your API key" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded text-white placeholder-gray-500 focus:outline-none focus:border-blue-500" />
                            <label class="block text-white font-medium mb-2 mt-4">API Secret *</label>
                            <input type="password" wire:model="formData.credentials.api_secret" placeholder="Enter your API secret" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded text-white placeholder-gray-500 focus:outline-none focus:border-blue-500" />
                            <label class="block text-white font-medium mb-2 mt-4">Endpoint URL *</label>
                            <input type="text" wire:model="formData.credentials.endpoint" placeholder="https://api.ctrack.com/..." class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded text-white placeholder-gray-500 focus:outline-none focus:border-blue-500" />
                        @elseif($formData['provider'] === 'john-deere')
                            <label class="block text-white font-medium mb-2 mt-4">OAuth Client ID *</label>
                            <input type="text" wire:model="formData.credentials.client_id" placeholder="Enter Client ID" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded text-white placeholder-gray-500 focus:outline-none focus:border-blue-500" />
                            <label class="block text-white font-medium mb-2 mt-4">OAuth Client Secret *</label>
                            <input type="password" wire:model="formData.credentials.client_secret" placeholder="Enter Client Secret" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded text-white placeholder-gray-500 focus:outline-none focus:border-blue-500" />
                            <label class="block text-white font-medium mb-2 mt-4">Endpoint URL *</label>
                            <input type="text" wire:model="formData.credentials.endpoint" placeholder="https://api.deere.com/..." class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded text-white placeholder-gray-500 focus:outline-none focus:border-blue-500" />
                        @elseif(in_array($formData['provider'], ['atlas-copco','sandvik','epiroc']))
                            <label class="block text-white font-medium mb-2 mt-4">API Key *</label>
                            <input type="password" wire:model="formData.credentials.api_key" placeholder="Enter your API key" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded text-white placeholder-gray-500 focus:outline-none focus:border-blue-500" />
                            <label class="block text-white font-medium mb-2 mt-4">API Secret *</label>
                            <input type="password" wire:model="formData.credentials.api_secret" placeholder="Enter your API secret" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded text-white placeholder-gray-500 focus:outline-none focus:border-blue-500" />
                            <label class="block text-white font-medium mb-2 mt-4">Site Code *</label>
                            <input type="text" wire:model="formData.credentials.site_code" placeholder="Enter site code" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded text-white placeholder-gray-500 focus:outline-none focus:border-blue-500" />
                        @else
                            <label class="block text-white font-medium mb-2 mt-4">API Key *</label>
                            <input type="password" wire:model="formData.credentials.api_key" placeholder="Enter your API key" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded text-white placeholder-gray-500 focus:outline-none focus:border-blue-500" />
                            <label class="block text-white font-medium mb-2 mt-4">API Secret *</label>
                            <input type="password" wire:model="formData.credentials.api_secret" placeholder="Enter your API secret" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded text-white placeholder-gray-500 focus:outline-none focus:border-blue-500" />
                        @endif
                        <p class="text-gray-400 text-sm mt-2">
                            💡 Tip: Your credentials are encrypted and stored securely. You can test the connection before saving.
                        </p>
                </div>

                <div class="p-6 border-t border-gray-700 flex gap-3">
                    <button 
                        wire:click="closeAddModal"
                        class="flex-1 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded font-medium transition"
                    >
                        Cancel
                    </button>
                    <button 
                        wire:click="createIntegration"
                        class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-medium transition"
                    >
                        Create Integration
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Test Connection Modal -->
    @if($showTestModal && $testResult)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="backdrop-filter: blur(4px);">
            <div class="bg-gray-800 border border-gray-700 rounded-lg shadow-xl max-w-md w-full mx-4">
                <div class="p-6 text-center">
                    @if($testResult['success'])
                        <div class="text-5xl mb-4">✅</div>
                        <h3 class="text-xl font-bold text-green-400 mb-2">Connection Successful</h3>
                        <p class="text-gray-400">{{ $testResult['message'] }}</p>
                    @else
                        <div class="text-5xl mb-4">❌</div>
                        <h3 class="text-xl font-bold text-red-400 mb-2">Connection Failed</h3>
                        <p class="text-gray-400">{{ $testResult['message'] }}</p>
                    @endif
                </div>
                <div class="p-6 border-t border-gray-700">
                    <button 
                        wire:click="$set('showTestModal', false)"
                        class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-medium transition"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
