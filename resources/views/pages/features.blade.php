@extends('layouts.app')
@section('title', 'Platform Features')
@section('content')
<div class="max-w-5xl mx-auto py-12 px-4">
    <h1 class="text-4xl font-bold text-blue-500 mb-6">Platform Features</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-gray-800 rounded-xl p-6 shadow-lg flex flex-col gap-4">
            <h2 class="text-2xl font-semibold text-white">Real-Time Fleet Tracking</h2>
            <p class="text-gray-300">Monitor all machines and vehicles live on the map, view status, location, and performance metrics instantly.</p>
            <img src="/images/screenshots/feature-fleet-tracking.png" alt="Fleet Tracking Screenshot" class="rounded-lg border border-gray-700">
            <a href="{{ route('core-features.fleet-tracking') }}" class="text-blue-400 hover:underline">Learn more</a>
        </div>
        <div class="bg-gray-800 rounded-xl p-6 shadow-lg flex flex-col gap-4">
            <h2 class="text-2xl font-semibold text-white">Mine Area Management</h2>
            <p class="text-gray-300">Define, edit, and monitor mine areas with geofencing, production targets, and shift management.</p>
            <img src="/images/screenshots/feature-mine-area.png" alt="Mine Area Screenshot" class="rounded-lg border border-gray-700">
            <a href="{{ route('mine-areas') }}" class="text-blue-400 hover:underline">Learn more</a>
        </div>
        <div class="bg-gray-800 rounded-xl p-6 shadow-lg flex flex-col gap-4">
            <h2 class="text-2xl font-semibold text-white">Maintenance & Alerts</h2>
            <p class="text-gray-300">Automated maintenance scheduling, breakdown alerts, and service history for every asset.</p>
            <img src="/images/screenshots/feature-maintenance.png" alt="Maintenance Screenshot" class="rounded-lg border border-gray-700">
            <a href="{{ route('core-features.maintenance') }}" class="text-blue-400 hover:underline">Learn more</a>
        </div>
        <div class="bg-gray-800 rounded-xl p-6 shadow-lg flex flex-col gap-4">
            <h2 class="text-2xl font-semibold text-white">Fuel & Cost Management</h2>
            <p class="text-gray-300">Track fuel usage, costs, and efficiency. Generate reports for cost optimization and compliance.</p>
            <img src="/images/screenshots/feature-fuel.png" alt="Fuel Management Screenshot" class="rounded-lg border border-gray-700">
            <a href="{{ route('core-features.fuel') }}" class="text-blue-400 hover:underline">Learn more</a>
        </div>
    </div>
</div>
@endsection