<!-- Mine Plan Uploader Main View -->
<div class="py-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <a href="{{ route('mine-areas') }}" class="text-amber-400 hover:text-amber-300 font-medium mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Mine Areas
                </a>
                <h1 class="text-3xl font-bold text-white mt-2">Mine Plans</h1>
                <p class="mt-2 text-gray-400">Manage and organize mine plans for <strong class="text-amber-400">{{ $mineArea->name }}</strong></p>
            </div>
            <button 
                wire:click="showUploadForm"
                @if($previewMode === 'upload') disabled @endif
                class="px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition font-medium shadow-lg flex items-center gap-2
                    @if($previewMode === 'upload') opacity-50 cursor-not-allowed @endif"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                Upload Plan
            </button>
        </div>

        <!-- Main Content Area -->
        <div class="space-y-6">
            @if($previewMode === 'list')
                @include('livewire.mine-plan-uploader.list')
            @elseif($previewMode === 'upload')
                @include('livewire.mine-plan-uploader.upload')
            @elseif($previewMode === 'edit')
                @include('livewire.mine-plan-uploader.edit')
            @elseif($previewMode === 'preview')
                @include('livewire.mine-plan-uploader.preview')
            @endif
        </div>
    </div>
</div>
