<div class="min-h-screen bg-slate-900 p-6">
    <div class="max-w-5xl mx-auto space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-white font-bold text-2xl">Shift Templates</h1>
                <p class="text-slate-400 text-sm mt-0.5">Pre-built message templates for the feed compose modal.</p>
            </div>
            <button wire:click="openCreate"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg text-sm transition-colors">
                + New Template
            </button>
        </div>

        {{-- Category filter tabs --}}
        <div class="flex gap-2 flex-wrap">
            @foreach ($categories as $cat)
                <button
                    wire:click="setCategory('{{ $cat }}')"
                    class="text-xs font-medium px-3 py-1.5 rounded-lg border transition-colors
                        {{ $activeCategory === $cat
                            ? 'bg-blue-600 border-blue-500 text-white'
                            : 'bg-slate-800 border-slate-700 text-slate-300 hover:border-slate-500' }}"
                >
                    {{ ucfirst(str_replace('_', ' ', $cat)) }}
                </button>
            @endforeach
        </div>

        {{-- Template list --}}
        @if ($templates->isEmpty())
            <div class="bg-slate-800 border border-slate-700 rounded-xl p-10 text-center">
                <p class="text-slate-400">No templates yet. Click <strong class="text-white">New Template</strong> to create one.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($templates as $tpl)
                    @php
                        $catColors = [
                            'breakdown'    => 'text-red-300 bg-red-900/30 border-red-800',
                            'shift_update' => 'text-amber-300 bg-amber-900/30 border-amber-800',
                            'safety_alert' => 'text-red-200 bg-red-900/40 border-red-700',
                            'production'   => 'text-green-300 bg-green-900/30 border-green-800',
                            'general'      => 'text-slate-300 bg-slate-700 border-slate-600',
                        ];
                        $catStyle  = $catColors[$tpl->category] ?? 'text-slate-300 bg-slate-700 border-slate-600';
                    @endphp
                    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 flex gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded border {{ $catStyle }}">
                                    {{ ucfirst(str_replace('_', ' ', $tpl->category)) }}
                                </span>
                                <span class="text-white font-semibold text-sm">{{ $tpl->title }}</span>
                            </div>
                            <p class="text-slate-400 text-sm line-clamp-2">{{ $tpl->template_body }}</p>
                            @if ($tpl->required_fields)
                                <p class="text-xs text-slate-500 mt-1">
                                    Required fields: {{ implode(', ', $tpl->required_fields) }}
                                </p>
                            @endif
                            <p class="text-xs text-slate-600 mt-1">
                                Created by {{ $tpl->creator?->name ?? 'Unknown' }} &middot; {{ $tpl->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="flex gap-2 items-start">
                            <button wire:click="openEdit({{ $tpl->id }})"
                                class="text-xs text-slate-400 hover:text-white bg-slate-700 hover:bg-slate-600 px-3 py-1.5 rounded-lg transition-colors">
                                Edit
                            </button>
                            <button wire:click="delete({{ $tpl->id }})"
                                wire:confirm="Delete this template?"
                                class="text-xs text-red-400 hover:text-white bg-slate-700 hover:bg-red-700 px-3 py-1.5 rounded-lg transition-colors">
                                Delete
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════
         Create / Edit Modal
    ════════════════════════════════════════════════════════════════════════ --}}
    @if ($showForm)
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-start justify-center p-4 pt-16"
             x-data x-on:keydown.escape.window="$wire.cancelForm()">
            <div class="bg-slate-800 border border-slate-700 rounded-2xl w-full max-w-lg shadow-2xl">

                <div class="flex items-center justify-between p-5 border-b border-slate-700">
                    <h2 class="text-white font-bold text-lg">
                        {{ $editingId ? 'Edit Template' : 'New Template' }}
                    </h2>
                    <button wire:click="cancelForm" class="text-slate-400 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-5 space-y-4">

                    {{-- Category --}}
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Category <span class="text-red-400">*</span></label>
                        <select wire:model="formCategory"
                            class="w-full bg-slate-700 text-white text-sm px-3 py-2 rounded-lg border border-slate-600 focus:border-blue-500 focus:outline-none">
                            <option value="breakdown">Breakdown</option>
                            <option value="shift_update">Shift Update</option>
                            <option value="safety_alert">Safety Alert</option>
                            <option value="production">Production</option>
                            <option value="general">General</option>
                        </select>
                        @error('formCategory') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Title --}}
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Title <span class="text-red-400">*</span></label>
                        <input type="text" wire:model="formTitle" placeholder="e.g. Standard Breakdown Report"
                            class="w-full bg-slate-700 text-white text-sm px-3 py-2 rounded-lg border border-slate-600 focus:border-blue-500 focus:outline-none">
                        @error('formTitle') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Template Body --}}
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Template Body <span class="text-red-400">*</span></label>
                        <textarea wire:model="formBody" rows="5"
                            placeholder="Write the template message here…"
                            class="w-full bg-slate-700 text-white text-sm px-3 py-2 rounded-lg border border-slate-600 focus:border-blue-500 focus:outline-none resize-none"
                        ></textarea>
                        @error('formBody') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Required fields hint --}}
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">
                            Required Fields
                            <span class="text-slate-500">(optional — comma-separated names)</span>
                        </label>
                        <input
                            type="text"
                            placeholder="e.g. machine_id, failure_type"
                            x-data="{ raw: @entangle('formRequired').live }"
                            x-init="raw = (Array.isArray(raw) ? raw.join(', ') : '')"
                            x-on:blur="$wire.set('formRequired', $el.value.split(',').map(s => s.trim()).filter(Boolean))"
                            :value="Array.isArray(raw) ? raw.join(', ') : raw"
                            class="w-full bg-slate-700 text-white text-sm px-3 py-2 rounded-lg border border-slate-600 focus:border-blue-500 focus:outline-none"
                        >
                    </div>
                </div>

                <div class="flex gap-3 p-5 pt-0">
                    <button wire:click="cancelForm"
                        class="flex-1 bg-slate-700 hover:bg-slate-600 text-white font-medium py-2.5 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button wire:click="save" wire:loading.attr="disabled"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-semibold py-2.5 rounded-lg transition-colors">
                        <span wire:loading.remove wire:target="save">Save Template</span>
                        <span wire:loading wire:target="save">Saving…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
