<div class="min-h-screen bg-slate-900 p-6">
    <div class="max-w-6xl mx-auto">

        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white">Feed Admin Panel</h1>
                <p class="text-slate-400 text-sm mt-1">Audit log, mine section management, and shift settings</p>
            </div>
            <a href="{{ route('feed') }}" class="text-slate-400 hover:text-white text-sm flex items-center gap-1 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Feed
            </a>
        </div>

        <!-- Tab Navigation -->
        <div class="flex gap-1 bg-slate-800 p-1 rounded-xl border border-slate-700 mb-6">
            @foreach([
                ['audit',    'Audit Log',       'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['sections', 'Mine Sections',   'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6 3m-6-3v-13m6 3l5.553-2.776A1 1 0 0121 5.618v10.764a1 1 0 01-1.447.894L15 20m0-13v13'],
                ['shifts',   'Active Shifts',   'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ] as [$tab, $label, $svg])
            <button
                wire:click="$set('activeTab', '{{ $tab }}')"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all
                    {{ $activeTab === $tab ? 'bg-blue-600 text-white shadow' : 'text-slate-400 hover:text-white hover:bg-slate-700' }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $svg }}"/>
                </svg>
                {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- ═══════════════════════════ AUDIT LOG ════════════════════════════ --}}
        @if($activeTab === 'audit')

        <div class="bg-slate-800 rounded-lg p-4 border border-slate-700 mb-4">
            <div class="flex items-center gap-4">
                <label class="text-sm text-slate-400">Filter by action:</label>
                <select wire:model.live="auditAction"
                    class="bg-slate-700 text-white px-3 py-1.5 rounded-lg border border-slate-600 text-sm focus:border-blue-500 focus:outline-none">
                    <option value="">All actions</option>
                    @foreach(\App\Models\FeedAuditLog::ACTIONS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($auditLogs->count() > 0)
        <div class="bg-slate-800 border border-slate-700 rounded-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-slate-700/50 border-b border-slate-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-300">Action</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-300">Actor</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-300">Subject</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-300">Details</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-300">When</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @foreach($auditLogs as $log)
                    <tr class="hover:bg-slate-700/20 transition">
                        <td class="px-6 py-3">
                            @php
                                $actionColors = [
                                    'pin'               => 'bg-yellow-900 text-yellow-300',
                                    'unpin'             => 'bg-slate-700 text-slate-300',
                                    'admin_delete'      => 'bg-red-900 text-red-300',
                                    'override_approval' => 'bg-purple-900 text-purple-300',
                                    'invite_sent'       => 'bg-blue-900 text-blue-300',
                                    'go_live_set'       => 'bg-green-900 text-green-300',
                                ];
                                $color = $actionColors[$log->action] ?? 'bg-slate-700 text-slate-300';
                            @endphp
                            <span class="px-2 py-0.5 rounded text-xs font-medium {{ $color }}">
                                {{ \App\Models\FeedAuditLog::ACTIONS[$log->action] ?? $log->action }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-slate-300 text-sm">{{ $log->actor?->name ?? '—' }}</td>
                        <td class="px-6 py-3 text-slate-500 text-xs font-mono">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</td>
                        <td class="px-6 py-3 text-slate-400 text-xs max-w-xs">
                            @if($log->meta)
                                @foreach($log->meta as $key => $val)
                                    <div><span class="text-slate-500">{{ str_replace('_', ' ', $key) }}:</span> {{ Str::limit((string) $val, 60) }}</div>
                                @endforeach
                            @endif
                        </td>
                        <td class="px-6 py-3 text-slate-500 text-xs">{{ $log->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $auditLogs->links() }}</div>
        @else
        <div class="bg-slate-800 border border-slate-700 rounded-lg p-12 text-center">
            <p class="text-slate-400">No audit log entries yet. Admin actions on the feed will appear here.</p>
        </div>
        @endif

        @endif {{-- end audit tab --}}

        {{-- ═══════════════════════════ MINE SECTIONS ═════════════════════════ --}}
        @if($activeTab === 'sections')

        <div class="bg-slate-800 border border-slate-700 rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-700">
                <h3 class="text-white font-semibold">Active Mine Sections</h3>
                <p class="text-slate-400 text-sm mt-1">Toggle sections to control their visibility in the feed filter.</p>
            </div>
            @if($mineAreas->count() > 0)
            <table class="w-full">
                <thead class="bg-slate-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-300">Section</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-300">Machines</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-300">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-300">Toggle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @foreach($mineAreas as $area)
                    <tr class="hover:bg-slate-700/20 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full {{ $area->is_active ? 'bg-green-400' : 'bg-slate-600' }}"></div>
                                <span class="text-white font-medium">{{ $area->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-400 text-sm">{{ $area->machines_count ?? 0 }}</td>
                        <td class="px-6 py-4">
                            @if($area->is_active)
                                <span class="px-2 py-0.5 bg-green-900 text-green-300 rounded text-xs font-medium">Active</span>
                            @else
                                <span class="px-2 py-0.5 bg-slate-700 text-slate-400 rounded text-xs font-medium">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <button wire:click="toggleMineArea({{ $area->id }})"
                                class="{{ $area->is_active
                                    ? 'bg-slate-700 hover:bg-red-900 text-slate-300 hover:text-red-300'
                                    : 'bg-slate-700 hover:bg-green-900 text-slate-300 hover:text-green-300'
                                }} px-3 py-1 rounded-lg text-sm font-medium transition-colors">
                                {{ $area->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="p-12 text-center">
                <p class="text-slate-400">No mine areas found. <a href="{{ route('mine-areas') }}" class="text-blue-400 hover:underline">Create mine areas</a> first.</p>
            </div>
            @endif
        </div>

        @endif {{-- end sections tab --}}

        {{-- ═══════════════════════════ ACTIVE SHIFTS ═════════════════════════ --}}
        @if($activeTab === 'shifts')

        <div class="bg-slate-800 border border-slate-700 rounded-lg p-6 max-w-lg">
            <h3 class="text-white font-semibold mb-2">Active Shifts for this Mine</h3>
            <p class="text-slate-400 text-sm mb-6">Control which shifts are currently operational. Inactive shifts are hidden from the feed filter and compose form.</p>

            <div class="flex gap-4 mb-6">
                @foreach(['A', 'B', 'C'] as $shift)
                <div class="flex-1">
                    <button wire:click="toggleShift('{{ $shift }}')"
                        class="w-full py-6 rounded-xl text-2xl font-bold border-2 transition-all
                            {{ in_array($shift, $activeShifts) ? 'border-blue-500 bg-blue-600 text-white shadow-lg shadow-blue-900/30' : 'border-slate-600 bg-slate-700 text-slate-400 hover:border-slate-500' }}">
                        Shift {{ $shift }}
                        @if(in_array($shift, $activeShifts))
                        <div class="text-xs font-normal text-blue-200 mt-1">Active</div>
                        @else
                        <div class="text-xs font-normal text-slate-500 mt-1">Inactive</div>
                        @endif
                    </button>
                </div>
                @endforeach
            </div>

            <button wire:click="saveShifts"
                class="w-full bg-blue-600 hover:bg-blue-500 text-white font-semibold py-2.5 rounded-lg transition-colors">
                Save Shift Settings
            </button>
        </div>

        @endif {{-- end shifts tab --}}

    </div>
</div>
