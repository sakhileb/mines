<div class="min-h-screen bg-slate-900 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">Settings</h1>
            <p class="text-slate-400">Manage team settings, users, and preferences</p>
        </div>

        <!-- Tabs Navigation -->
        <div class="flex gap-4 mb-6 border-b border-slate-700">
            <button 
                wire:click="setActiveTab('general')"
                class="px-4 py-3 font-medium {{ $activeTab === 'general' ? 'text-blue-400 border-b-2 border-blue-400' : 'text-slate-400 hover:text-slate-300' }}"
            >
                🏢 General
            </button>
            <button 
                wire:click="setActiveTab('users')"
                class="px-4 py-3 font-medium {{ $activeTab === 'users' ? 'text-blue-400 border-b-2 border-blue-400' : 'text-slate-400 hover:text-slate-300' }}"
            >
                👥 Users & Roles
            </button>
            <button 
                wire:click="setActiveTab('notifications')"
                class="px-4 py-3 font-medium {{ $activeTab === 'notifications' ? 'text-blue-400 border-b-2 border-blue-400' : 'text-slate-400 hover:text-slate-300' }}"
            >
                🔔 Notifications
            </button>
        </div>

        <!-- Content -->
        <div class="bg-slate-800 rounded-lg border border-slate-700 p-8">
            <!-- GENERAL SETTINGS TAB -->
            @if($activeTab === 'general')
                <div class="space-y-6">
                    <h2 class="text-2xl font-bold text-white mb-6">General Settings</h2>

                    <!-- Team Name -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Team Name</label>
                        <input 
                            type="text" 
                            wire:model="teamName"
                            class="w-full bg-slate-700 text-white px-4 py-2 rounded-lg border border-slate-600 focus:border-blue-500 focus:outline-none"
                        >
                        @error('teamName') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Team Email -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Team Email</label>
                        <input 
                            type="email" 
                            wire:model="teamEmail"
                            class="w-full bg-slate-700 text-white px-4 py-2 rounded-lg border border-slate-600 focus:border-blue-500 focus:outline-none"
                        >
                        @error('teamEmail') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Timezone -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Timezone</label>
                        <select 
                            wire:model="timezone"
                            class="w-full bg-slate-700 text-white px-4 py-2 rounded-lg border border-slate-600 focus:border-blue-500 focus:outline-none"
                        >
                            @foreach ($timezones as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Language -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Language</label>
                        <select 
                            wire:model="language"
                            class="w-full bg-slate-700 text-white px-4 py-2 rounded-lg border border-slate-600 focus:border-blue-500 focus:outline-none"
                        >
                            @foreach ($languages as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Currency -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Currency</label>
                        <select 
                            wire:model="currency"
                            class="w-full bg-slate-700 text-white px-4 py-2 rounded-lg border border-slate-600 focus:border-blue-500 focus:outline-none"
                        >
                            @foreach ($currencies as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-slate-400 text-xs mt-1">This currency will be used for all financial reports and displays</p>
                    </div>

                    <!-- Save Button -->
                    <div class="pt-4">
                        <button 
                            wire:click="saveGeneralSettings"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition"
                        >
                            Save Changes
                        </button>
                    </div>
                </div>
            @endif

            <!-- USERS & ROLES TAB -->
            @if($activeTab === 'users')
                <div class="space-y-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-white">Users & Roles</h2>
                        <button 
                            wire:click="toggleInviteForm"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition"
                        >
                            {{ $showInviteForm ? 'Cancel' : '+ Invite User' }}
                        </button>
                    </div>

                    <!-- Invite Form -->
                    @if($showInviteForm)
                        <div class="bg-slate-700/50 rounded-lg p-6 border border-slate-600 space-y-4 mb-6">
                            <h3 class="text-lg font-semibold text-white">Invite New User</h3>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Email Address</label>
                                <input 
                                    type="email" 
                                    wire:model="inviteEmail"
                                    placeholder="user@example.com"
                                    class="w-full bg-slate-700 text-white px-4 py-2 rounded-lg border border-slate-600 focus:border-blue-500 focus:outline-none"
                                >
                                @error('inviteEmail') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Role</label>
                                <select 
                                    wire:model="selectedRole"
                                    class="w-full bg-slate-700 text-white px-4 py-2 rounded-lg border border-slate-600 focus:border-blue-500 focus:outline-none"
                                >
                                    @foreach ($roles as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button 
                                wire:click="inviteUser"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition"
                            >
                                Send Invitation
                            </button>
                        </div>
                    @endif

                    <!-- Team Members List -->
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-4">Team Members</h3>
                        <div class="space-y-3">
                            @forelse($teamMembers as $member)
                                <div class="flex items-center justify-between bg-slate-700/50 rounded-lg p-4 border border-slate-600">
                                    <div class="flex-1">
                                        <p class="font-medium text-white">{{ $member['name'] }}</p>
                                        <p class="text-sm text-slate-400">{{ $member['email'] }}</p>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <select 
                                            wire:change="updateUserRole({{ $member['id'] }}, $event.target.value)"
                                            class="bg-slate-700 text-white px-3 py-1 rounded border border-slate-600 text-sm"
                                        >
                                            @foreach ($roles as $value => $label)
                                                <option value="{{ $value }}" {{ $member['role'] === $label ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <button 
                                            wire:click="removeUser({{ $member['id'] }})"
                                            class="text-red-400 hover:text-red-300 font-medium text-sm"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <p class="text-slate-400">No team members yet</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif

            <!-- NOTIFICATIONS TAB -->
            @if($activeTab === 'notifications')
                <div class="space-y-6">
                    <h2 class="text-2xl font-bold text-white mb-6">Notification Settings</h2>

                    <div class="space-y-4">
                        <!-- Email Alerts -->
                        <div class="flex items-center justify-between bg-slate-700/50 rounded-lg p-4 border border-slate-600">
                            <div>
                                <p class="font-medium text-white">Email Alerts</p>
                                <p class="text-sm text-slate-400">Receive alert notifications via email</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    wire:model="emailAlerts"
                                    class="sr-only peer"
                                >
                                <div class="w-11 h-6 bg-slate-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <!-- Email Reports -->
                        <div class="flex items-center justify-between bg-slate-700/50 rounded-lg p-4 border border-slate-600">
                            <div>
                                <p class="font-medium text-white">Email Reports</p>
                                <p class="text-sm text-slate-400">Receive scheduled reports via email</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    wire:model="emailReports"
                                    class="sr-only peer"
                                >
                                <div class="w-11 h-6 bg-slate-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <!-- In-App Alerts -->
                        <div class="flex items-center justify-between bg-slate-700/50 rounded-lg p-4 border border-slate-600">
                            <div>
                                <p class="font-medium text-white">In-App Alerts</p>
                                <p class="text-sm text-slate-400">Show notifications in the application</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    wire:model="inAppAlerts"
                                    class="sr-only peer"
                                >
                                <div class="w-11 h-6 bg-slate-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <!-- Quiet Hours -->
                        <div class="bg-slate-700/50 rounded-lg p-4 border border-slate-600 space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-white">Quiet Hours</p>
                                    <p class="text-sm text-slate-400">Disable notifications during quiet hours</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        wire:model="quietHoursEnabled"
                                        class="sr-only peer"
                                    >
                                    <div class="w-11 h-6 bg-slate-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            @if($quietHoursEnabled)
                                <div class="grid grid-cols-2 gap-4 pt-2">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-300 mb-2">Start Time</label>
                                        <input 
                                            type="time" 
                                            wire:model="quietHoursStart"
                                            class="w-full bg-slate-700 text-white px-3 py-2 rounded border border-slate-600 focus:border-blue-500 focus:outline-none"
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-300 mb-2">End Time</label>
                                        <input 
                                            type="time" 
                                            wire:model="quietHoursEnd"
                                            class="w-full bg-slate-700 text-white px-3 py-2 rounded border border-slate-600 focus:border-blue-500 focus:outline-none"
                                        >
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="pt-4">
                        <button 
                            wire:click="saveNotificationSettings"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition"
                        >
                            Save Settings
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
