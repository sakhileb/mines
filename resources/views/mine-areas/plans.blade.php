<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Mine Plans') }} - {{ $mineArea->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @livewire('mine-plan-uploader', ['mineArea' => $mineArea])
        </div>
    </div>
</x-app-layout>
