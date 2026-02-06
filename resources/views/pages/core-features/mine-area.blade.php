@extends('layouts.app')
@section('title', 'Mine Area Management')
@section('content')
<div class="max-w-4xl mx-auto py-12 px-4">
    <h1 class="text-4xl font-bold text-green-500 mb-6">Mine Area Management</h1>
    <div class="bg-gray-800 rounded-xl p-8 shadow-lg flex flex-col gap-6">
        <p class="text-gray-300 text-lg">Define, edit, and monitor mine areas with geofencing, production targets, and shift management. Draw boundaries, assign machines, and track area-specific KPIs.</p>
        <img src="/images/screenshots/feature-mine-area-full.png" alt="Mine Area Full Screenshot" class="rounded-lg border border-gray-700">
        <ul class="list-disc pl-6 text-gray-300 space-y-2">
            <li>Draw or import area boundaries on the map</li>
            <li>Assign machines and operators to each area</li>
            <li>Set production targets and monitor progress</li>
            <li>View area history and audit logs</li>
        </ul>
    </div>
</div>
@endsection