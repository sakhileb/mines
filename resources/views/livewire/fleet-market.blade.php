<div class="min-h-screen bg-slate-900 text-white p-6"
     x-data="{
         notification: null,
         tab: 'browse'
     }"
     x-on:notify.window="notification = $event.detail; setTimeout(() => notification = null, 4000)">

    {{-- Notification --}}
    <div x-show="notification" x-transition
         class="fixed top-5 right-5 z-50 px-5 py-3 rounded-xl shadow-xl text-sm font-medium"
         :class="notification?.type === 'success' ? 'bg-emerald-700 text-white' : 'bg-red-700 text-white'"
         x-text="notification?.message"></div>

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="max-w-7xl mx-auto">
        <div class="bg-gradient-to-r from-blue-700 to-indigo-700 rounded-xl p-6 mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 text-blue-200 text-sm mb-1">
                    <a href="{{ route('fleet') }}" class="hover:text-white">Fleet Management</a>
                    <span>/</span>
                    <span class="text-white font-medium">Fleet Market</span>
                </div>
                <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                    🏪 Fleet Market
                </h1>
                <p class="text-blue-200 text-sm mt-1">Browse and advertise mining equipment for sale</p>
            </div>
            <div class="flex gap-2 flex-wrap">
                <button @click="tab = 'my-listings'"
                        :class="tab === 'my-listings' ? 'bg-white text-indigo-700' : 'bg-white/20 text-white hover:bg-white/30'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    My Listings ({{ $myListings->count() }})
                </button>
                <button wire:click="openCreateModal"
                        class="px-4 py-2 bg-green-500 hover:bg-green-400 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Advertise Equipment
                </button>
            </div>
        </div>

        {{-- ── Tab: Browse / My Listings ───────────────────────────────────── --}}
        {{-- ═══ BROWSE TAB ════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'browse'">

            {{-- Filters --}}
            <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 mb-6 flex flex-col md:flex-row gap-3">
                <div class="flex-1">
                    <input type="text" wire:model.live.debounce.300ms="search"
                           placeholder="Search by brand, model, location…"
                           class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-2 text-white text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <select wire:model.live="typeFilter"
                        class="bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:border-blue-500 min-w-[160px]">
                    <option value="">All Types</option>
                    @foreach ($machineTypes as $type)
                        <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
                <select wire:model.live="condFilter"
                        class="bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:border-blue-500 min-w-[160px]">
                    <option value="">All Conditions</option>
                    <option value="new">New</option>
                    <option value="used">Used</option>
                    <option value="refurbished">Refurbished</option>
                </select>
            </div>

            {{-- Listings Grid --}}
            <div wire:loading.class="opacity-50" class="transition-opacity">
                @if ($listings->isEmpty())
                    <div class="bg-slate-800 border border-slate-700 rounded-xl p-16 text-center">
                        <div class="text-5xl mb-4">🚜</div>
                        <h3 class="text-white font-semibold mb-2">No listings yet</h3>
                        <p class="text-slate-400 text-sm mb-4">Be the first to advertise equipment on the Fleet Market.</p>
                        <button wire:click="openCreateModal"
                                class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-sm font-medium">
                            Post a Listing
                        </button>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 mb-6">
                        @foreach ($listings as $listing)
                            <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden hover:border-blue-600/60 transition-colors flex flex-col">
                                {{-- Image placeholder --}}
                                <div class="h-44 bg-gradient-to-br from-slate-700 to-slate-600 flex items-center justify-center text-5xl">
                                    @switch($listing->machine_type)
                                        @case('excavator') 🏗️ @break
                                        @case('adt')       🚛 @break
                                        @case('loader')    🚜 @break
                                        @case('dozer')     🚧 @break
                                        @default           ⚙️
                                    @endswitch
                                </div>

                                <div class="p-4 flex flex-col flex-1">
                                    {{-- Badges --}}
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                            {{ $listing->condition === 'new' ? 'bg-emerald-900/50 text-emerald-400 border border-emerald-700' :
                                               ($listing->condition === 'refurbished' ? 'bg-blue-900/50 text-blue-400 border border-blue-700' :
                                               'bg-slate-700 text-slate-300 border border-slate-600') }}">
                                            {{ ucfirst($listing->condition) }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-slate-700 text-slate-300 border border-slate-600">
                                            {{ ucfirst($listing->machine_type) }}
                                        </span>
                                    </div>

                                    <h3 class="text-white font-semibold text-base leading-tight">
                                        {{ $listing->brand }} {{ $listing->model }}
                                        @if ($listing->year)<span class="text-slate-400 font-normal">({{ $listing->year }})</span>@endif
                                    </h3>

                                    @if ($listing->hours_on_machine)
                                        <p class="text-slate-400 text-xs mt-1">🕐 {{ number_format($listing->hours_on_machine) }} hours</p>
                                    @endif
                                    @if ($listing->location)
                                        <p class="text-slate-400 text-xs">📍 {{ $listing->location }}</p>
                                    @endif

                                    @if ($listing->description)
                                        <p class="text-slate-400 text-sm mt-2 line-clamp-2">{{ $listing->description }}</p>
                                    @endif

                                    <div class="mt-auto pt-4 flex items-center justify-between">
                                        <div>
                                            @if ($listing->price)
                                                <div class="text-white font-bold text-lg">
                                                    {{ $listing->currency }} {{ number_format($listing->price, 0) }}
                                                </div>
                                            @else
                                                <div class="text-slate-400 text-sm italic">Price on request</div>
                                            @endif
                                            <div class="text-slate-500 text-xs">{{ $listing->team->name }}</div>
                                        </div>
                                        <button wire:click="openEnquiry({{ $listing->id }})"
                                                class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-sm font-medium transition-colors">
                                            Enquire
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{ $listings->links() }}
                @endif
            </div>
        </div>

        {{-- ═══ MY LISTINGS TAB ═══════════════════════════════════════════════ --}}
        <div x-show="tab === 'my-listings'">
            <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-slate-900/50">
                        <tr class="text-slate-400 text-xs uppercase tracking-wider">
                            <th class="px-4 py-3 text-left">Equipment</th>
                            <th class="px-4 py-3 text-left">Type</th>
                            <th class="px-4 py-3 text-left">Price</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Posted</th>
                            <th class="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse ($myListings as $listing)
                            <tr class="hover:bg-slate-700/20 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="text-white font-medium">{{ $listing->brand }} {{ $listing->model }}</div>
                                    @if ($listing->year)<div class="text-slate-400 text-xs">{{ $listing->year }}</div>@endif
                                </td>
                                <td class="px-4 py-3 text-slate-300">{{ ucfirst($listing->machine_type) }}</td>
                                <td class="px-4 py-3 text-white">
                                    {{ $listing->price ? $listing->currency . ' ' . number_format($listing->price, 0) : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs border
                                        {{ $listing->status === 'active' ? 'bg-emerald-900/40 text-emerald-400 border-emerald-700' :
                                           ($listing->status === 'sold' ? 'bg-blue-900/40 text-blue-400 border-blue-700' :
                                           'bg-slate-700 text-slate-400 border-slate-600') }}">
                                        {{ ucfirst($listing->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-400 text-xs">{{ $listing->created_at->diffForHumans() }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <button wire:click="editListing({{ $listing->id }})"
                                                class="text-xs px-2 py-1 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded">
                                            Edit
                                        </button>
                                        @if ($listing->status === 'active')
                                            <button wire:click="markAsSold({{ $listing->id }})"
                                                    class="text-xs px-2 py-1 bg-blue-900/40 hover:bg-blue-800/60 text-blue-400 rounded">
                                                Mark Sold
                                            </button>
                                            <button wire:click="withdrawListing({{ $listing->id }})"
                                                    class="text-xs px-2 py-1 bg-amber-900/40 hover:bg-amber-800/60 text-amber-400 rounded">
                                                Withdraw
                                            </button>
                                        @endif
                                        <button wire:click="deleteListing({{ $listing->id }})"
                                                wire:confirm="Delete this listing permanently?"
                                                class="text-xs px-2 py-1 bg-red-900/40 hover:bg-red-800/60 text-red-400 rounded">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-500">
                                    You haven't listed any equipment yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- Create / Edit Listing Modal                                           --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
            <div class="bg-slate-800 border border-slate-600 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-slate-700 flex items-center justify-between">
                    <h2 class="text-white font-semibold text-lg">
                        {{ $editingId ? 'Edit Listing' : 'Advertise Equipment for Sale' }}
                    </h2>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form wire:submit.prevent="saveListing" class="p-6 space-y-4">
                    {{-- Equipment details --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-slate-400 mb-1">Brand *</label>
                            <input type="text" wire:model="brand" placeholder="e.g. Volvo, CAT, Bell"
                                   class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:border-blue-500">
                            @error('brand') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm text-slate-400 mb-1">Model *</label>
                            <input type="text" wire:model="model" placeholder="e.g. A40G"
                                   class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:border-blue-500">
                            @error('model') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-slate-400 mb-1">Machine Type *</label>
                            <select wire:model="machineType"
                                    class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:border-blue-500">
                                <option value="">Select type…</option>
                                <option value="adt">ADT (Articulated Dump Truck)</option>
                                <option value="excavator">Excavator</option>
                                <option value="loader">Loader</option>
                                <option value="dozer">Dozer / Bulldozer</option>
                                <option value="grader">Grader</option>
                                <option value="crane">Crane</option>
                                <option value="drill">Drill Rig</option>
                                <option value="other">Other</option>
                            </select>
                            @error('machineType') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm text-slate-400 mb-1">Condition</label>
                            <select wire:model="condition"
                                    class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:border-blue-500">
                                <option value="new">New</option>
                                <option value="used">Used</option>
                                <option value="refurbished">Refurbished</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm text-slate-400 mb-1">Year</label>
                            <input type="number" wire:model="year" placeholder="{{ date('Y') }}"
                                   class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-400 mb-1">Hours on Machine</label>
                            <input type="number" wire:model="hoursOnMachine" placeholder="e.g. 12500"
                                   class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-400 mb-1">Location</label>
                            <input type="text" wire:model="location" placeholder="e.g. Limpopo, SA"
                                   class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:border-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm text-slate-400 mb-1">Asking Price</label>
                            <input type="number" wire:model="price" placeholder="Leave blank for PoR"
                                   class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-400 mb-1">Currency</label>
                            <select wire:model="currency"
                                    class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:border-blue-500">
                                <option value="ZAR">ZAR</option>
                                <option value="USD">USD</option>
                                <option value="EUR">EUR</option>
                                <option value="GBP">GBP</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm text-slate-400 mb-1">Description</label>
                        <textarea wire:model="description" rows="3" placeholder="Condition notes, service history, extras…"
                                  class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:border-blue-500 resize-none"></textarea>
                    </div>

                    <hr class="border-slate-700">

                    {{-- Contact details --}}
                    <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Contact Details</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-slate-400 mb-1">Contact Name *</label>
                            <input type="text" wire:model="contactName"
                                   class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:border-blue-500">
                            @error('contactName') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm text-slate-400 mb-1">Contact Email *</label>
                            <input type="email" wire:model="contactEmail"
                                   class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:border-blue-500">
                            @error('contactEmail') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-400 mb-1">Phone (optional)</label>
                        <input type="text" wire:model="contactPhone" placeholder="+27 …"
                               class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:border-blue-500">
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showModal', false)"
                                class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-sm">
                            Cancel
                        </button>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="px-5 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg text-sm font-medium disabled:opacity-50">
                            <span wire:loading.remove wire:target="saveListing">
                                {{ $editingId ? 'Update Listing' : 'Publish Listing' }}
                            </span>
                            <span wire:loading wire:target="saveListing">Saving…</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- Enquiry Modal                                                         --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    @if ($showEnquiryModal && $enquiryListing)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
            <div class="bg-slate-800 border border-slate-600 rounded-2xl w-full max-w-md p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-white font-semibold text-lg">Enquiry Details</h2>
                    <button wire:click="closeEnquiry" class="text-slate-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="bg-slate-900 rounded-lg p-4 mb-5">
                    <h3 class="text-white font-semibold">
                        {{ $enquiryListing->brand }} {{ $enquiryListing->model }}
                        @if ($enquiryListing->year)({{ $enquiryListing->year }})@endif
                    </h3>
                    <p class="text-slate-400 text-sm mt-1">
                        {{ ucfirst($enquiryListing->condition) }} · {{ ucfirst($enquiryListing->machine_type) }}
                        @if ($enquiryListing->price)
                            · <span class="text-white font-semibold">{{ $enquiryListing->currency }} {{ number_format($enquiryListing->price, 0) }}</span>
                        @endif
                    </p>
                    <p class="text-slate-500 text-xs mt-1">Listed by {{ $enquiryListing->team->name }}</p>
                </div>

                <p class="text-slate-400 text-sm mb-4">Contact the seller directly:</p>
                <div class="space-y-3">
                    @if ($enquiryListing->contact_name)
                        <div class="flex items-center gap-3 text-sm">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="text-white">{{ $enquiryListing->contact_name }}</span>
                        </div>
                    @endif
                    @if ($enquiryListing->contact_email)
                        <div class="flex items-center gap-3 text-sm">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <a href="mailto:{{ $enquiryListing->contact_email }}" class="text-blue-400 hover:text-blue-300">
                                {{ $enquiryListing->contact_email }}
                            </a>
                        </div>
                    @endif
                    @if ($enquiryListing->contact_phone)
                        <div class="flex items-center gap-3 text-sm">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.948V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <a href="tel:{{ $enquiryListing->contact_phone }}" class="text-blue-400 hover:text-blue-300">
                                {{ $enquiryListing->contact_phone }}
                            </a>
                        </div>
                    @endif
                </div>

                <button wire:click="closeEnquiry"
                        class="mt-6 w-full py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-sm font-medium">
                    Close
                </button>
            </div>
        </div>
    @endif

</div>
