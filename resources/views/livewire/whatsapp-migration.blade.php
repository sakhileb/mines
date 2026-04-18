<div class="min-h-screen bg-slate-900 text-white p-6">

    {{-- Header --}}
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <a href="{{ route('feed') }}" class="text-amber-400 hover:text-amber-300 text-sm flex items-center gap-1 mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Feed
                </a>
                <h1 class="text-2xl font-bold text-white">WhatsApp Migration</h1>
                <p class="text-slate-400 mt-1">Manage the transition from WhatsApp to the Operations Feed</p>
            </div>
            <a href="{{ route('feed.admin') }}" class="px-4 py-2 bg-slate-700 text-slate-300 rounded-lg hover:bg-slate-600 text-sm">
                Admin Panel
            </a>
        </div>

        {{-- Notification flash --}}
        @if (session()->has('notify'))
            <div class="mb-4 p-4 rounded-lg bg-emerald-900/40 border border-emerald-600 text-emerald-300 text-sm">
                {{ session('notify') }}
            </div>
        @endif

        <div x-data="{ notification: null }"
             x-on:notify.window="notification = $event.detail; setTimeout(() => notification = null, 4000)">
            <div x-show="notification"
                 x-transition
                 class="mb-4 p-4 rounded-lg bg-emerald-900/40 border border-emerald-600 text-emerald-300 text-sm"
                 x-text="notification?.message"></div>
        </div>

        {{-- ─══════════════════════════════════════════════════════════════════════── --}}
        {{-- Section 1: Go-Live Date                                                  --}}
        {{-- ─══════════════════════════════════════════════════════════════════════── --}}
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 mb-6">
            <div class="flex items-start gap-4 mb-5">
                <div class="w-10 h-10 bg-amber-600/20 border border-amber-600/40 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-white">Set Go-Live Date</h2>
                    <p class="text-slate-400 text-sm mt-1">Set the official date when WhatsApp channels will be decommissioned and the Operations Feed goes live.</p>
                </div>
            </div>

            <form wire:submit.prevent="saveGoLiveDate">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm text-slate-400 mb-1">Go-Live Date</label>
                        <input type="date" wire:model="goLiveDate"
                               class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                        @error('goLiveDate') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm text-slate-400 mb-1">Go-Live Time</label>
                        <input type="time" wire:model="goLiveTime"
                               class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                        @error('goLiveTime') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                @if ($goLiveDate)
                    @php
                        $dt = \Carbon\Carbon::parse($goLiveDate . ' ' . $goLiveTime);
                        $isPast = $dt->isPast();
                        $diffForHumans = $dt->diffForHumans();
                    @endphp
                    <div class="mb-4 p-3 rounded-lg {{ $isPast ? 'bg-red-900/30 border border-red-700 text-red-300' : 'bg-amber-900/30 border border-amber-700 text-amber-300' }} text-sm">
                        @if ($isPast)
                            ⚠️ This date is in the past ({{ $diffForHumans }}). WhatsApp channels should now be decommissioned.
                        @else
                            🕐 Go-live is {{ $diffForHumans }} — on <strong>{{ $dt->format('l, F j, Y \a\t H:i') }}</strong>.
                        @endif
                    </div>
                @endif

                <button type="submit"
                        wire:loading.attr="disabled"
                        class="px-5 py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-lg text-sm font-medium transition-colors disabled:opacity-50">
                    <span wire:loading.remove wire:target="saveGoLiveDate">Save Go-Live Date</span>
                    <span wire:loading wire:target="saveGoLiveDate">Saving…</span>
                </button>
            </form>
        </div>

        {{-- ─══════════════════════════════════════════════════════════════════════── --}}
        {{-- Section 2: Send Onboarding Invites                                       --}}
        {{-- ─══════════════════════════════════════════════════════════════════════── --}}
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 mb-6">
            <div class="flex items-start gap-4 mb-5">
                <div class="w-10 h-10 bg-blue-600/20 border border-blue-600/40 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-white">Send Onboarding Invites</h2>
                    <p class="text-slate-400 text-sm mt-1">Email all team members an invitation to join the Operations Feed, with a personalised message.</p>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm text-slate-400 mb-1">Invite Message</label>
                <textarea wire:model="inviteMessage" rows="3"
                          class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 resize-none"
                          placeholder="Message to include in the email…"></textarea>
                @error('inviteMessage') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Team members table --}}
            <div class="overflow-x-auto mb-4 rounded-lg border border-slate-700">
                <table class="w-full text-sm">
                    <thead class="bg-slate-900/50">
                        <tr class="text-slate-400 text-xs uppercase tracking-wider">
                            <th class="px-4 py-3 text-left">Name</th>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3 text-left">Last Login</th>
                            <th class="px-4 py-3 text-left">Invite Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse ($users as $member)
                            <tr class="hover:bg-slate-700/20 transition-colors">
                                <td class="px-4 py-3 text-white font-medium">{{ $member->name }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ $member->email }}</td>
                                <td class="px-4 py-3 text-slate-400 text-xs">
                                    {{ $member->last_login_at ? \Carbon\Carbon::parse($member->last_login_at)->diffForHumans() : 'Never' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($invitesSent->contains($member->id))
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-emerald-900/40 text-emerald-400 border border-emerald-700">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            Invited
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-slate-700 text-slate-400 border border-slate-600">
                                            Not sent
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-slate-500 text-sm">No team members found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between">
                <p class="text-slate-500 text-sm">
                    {{ $invitesSent->count() }} / {{ $users->count() }} members invited
                </p>
                <button wire:click="sendInvites"
                        wire:loading.attr="disabled"
                        wire:confirm="Send onboarding invites to all {{ $users->count() }} team member(s)? Each will receive an email."
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-sm font-medium transition-colors disabled:opacity-50">
                    <span wire:loading.remove wire:target="sendInvites">📧 Send Invites to All</span>
                    <span wire:loading wire:target="sendInvites">Queueing…</span>
                </button>
            </div>
        </div>

        {{-- ─══════════════════════════════════════════════════════════════════════── --}}
        {{-- Section 3: Migration Status                                               --}}
        {{-- ─══════════════════════════════════════════════════════════════════════── --}}
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
            <div class="flex items-start gap-4 mb-5">
                <div class="w-10 h-10 bg-emerald-600/20 border border-emerald-600/40 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-white">Migration Status</h2>
                    <p class="text-slate-400 text-sm mt-1">Overview of the WhatsApp-to-Feed migration progress.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                {{-- Invite progress --}}
                <div class="bg-slate-900/50 rounded-lg p-4 border border-slate-700">
                    <div class="text-3xl font-bold text-white">{{ $users->count() > 0 ? round($invitesSent->count() / $users->count() * 100) : 0 }}%</div>
                    <div class="text-slate-400 text-sm mt-1">Team Members Invited</div>
                    <div class="mt-2 bg-slate-700 rounded-full h-1.5">
                        <div class="bg-blue-500 h-1.5 rounded-full"
                             style="width: {{ $users->count() > 0 ? round($invitesSent->count() / $users->count() * 100) : 0 }}%"></div>
                    </div>
                    <div class="text-slate-500 text-xs mt-1">{{ $invitesSent->count() }} of {{ $users->count() }}</div>
                </div>

                {{-- Go-live date status --}}
                <div class="bg-slate-900/50 rounded-lg p-4 border border-slate-700">
                    <div class="text-sm font-semibold text-white mb-1">Go-Live Date</div>
                    @if ($team->feed_go_live_at)
                        @php
                            $glDate = \Carbon\Carbon::parse($team->feed_go_live_at);
                        @endphp
                        <div class="text-lg font-bold {{ $glDate->isPast() ? 'text-emerald-400' : 'text-amber-400' }}">
                            {{ $glDate->format('M d, Y') }}
                        </div>
                        <div class="text-slate-500 text-xs mt-0.5">{{ $glDate->format('H:i') }} — {{ $glDate->diffForHumans() }}</div>
                        @if ($glDate->isPast())
                            <span class="inline-block mt-2 px-2 py-0.5 bg-emerald-900/50 text-emerald-400 border border-emerald-700 rounded-full text-xs">✓ Passed</span>
                        @else
                            <span class="inline-block mt-2 px-2 py-0.5 bg-amber-900/50 text-amber-400 border border-amber-700 rounded-full text-xs">Upcoming</span>
                        @endif
                    @else
                        <div class="text-slate-500 text-sm">Not set</div>
                    @endif
                </div>

                {{-- Decommission checklist --}}
                <div class="bg-slate-900/50 rounded-lg p-4 border border-slate-700">
                    <div class="text-sm font-semibold text-white mb-2">Decommission Checklist</div>
                    <ul class="space-y-1">
                        <li class="flex items-center gap-2 text-xs {{ $team->feed_go_live_at ? 'text-emerald-400' : 'text-slate-500' }}">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $team->feed_go_live_at ? 'M5 13l4 4L19 7' : 'M12 12v0' }}"/>
                            </svg>
                            Go-live date set
                        </li>
                        <li class="flex items-center gap-2 text-xs {{ $invitesSent->count() > 0 ? 'text-emerald-400' : 'text-slate-500' }}">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $invitesSent->count() > 0 ? 'M5 13l4 4L19 7' : 'M12 12v0' }}"/>
                            </svg>
                            Invites sent
                        </li>
                        <li class="flex items-center gap-2 text-xs text-slate-500">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12v0"/>
                            </svg>
                            WhatsApp channels archived
                        </li>
                        <li class="flex items-center gap-2 text-xs text-slate-500">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12v0"/>
                            </svg>
                            WhatsApp channels decommissioned
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
