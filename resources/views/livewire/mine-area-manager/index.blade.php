<div class="min-h-screen bg-gray-900 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-white">Mine Areas Management</h1>
            <p class="mt-2 text-gray-400">Manage your mining operational areas with geospatial tracking</p>
        </div>

        <!-- Conditionally render views -->
        @if ($view === 'list')
            @include('livewire.mine-area-manager.list')
        @elseif ($view === 'create')
            @include('livewire.mine-area-manager.create-edit')
        @elseif ($view === 'edit')
            @include('livewire.mine-area-manager.create-edit')
        @elseif ($view === 'detail')
            @include('livewire.mine-area-manager.detail')
        @endif
    </div>
</div>
