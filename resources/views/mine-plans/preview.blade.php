<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Mine Plan Preview') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if($plan->file_type === 'pdf')
                <!-- PDF Viewer -->
                <div class="bg-gray-800 border border-gray-700 rounded-lg shadow overflow-hidden">
                    <div class="aspect-video bg-slate-900">
                        <iframe 
                            src="{{ route('storage.download', ['path' => $plan->file_path]) }}"
                            class="w-full h-full"
                            frameborder="0"
                        ></iframe>
                    </div>
                </div>
            @elseif(in_array($plan->file_type, ['jpg', 'png']))
                <!-- Image Viewer -->
                <div class="bg-gray-800 border border-gray-700 rounded-lg shadow overflow-hidden">
                    <img 
                        src="{{ route('storage.download', ['path' => $plan->file_path]) }}"
                        alt="{{ $plan->file_name }}"
                        class="w-full h-auto"
                    />
                </div>
            @else
                <!-- File Download -->
                <div class="bg-gray-800 border border-gray-700 rounded-lg shadow p-8 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 0v2m0-6H9m6 0h-3m0 0H9m6 0h3"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-white mb-2">{{ $plan->file_name }}</h3>
                    <p class="text-gray-400 mb-4">Online preview not available for this file type</p>
                    <a 
                        href="{{ route('storage.download', ['path' => $plan->file_path]) }}"
                        class="inline-flex items-center px-6 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition font-medium"
                    >
                        ⬇️ Download File
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
