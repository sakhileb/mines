@extends('layouts.app')
@section('title', 'Fleet Tracking')
@section('content')
<div class="max-w-4xl mx-auto py-12 px-4">
    <h1 class="text-4xl font-bold text-blue-500 mb-6">Real-Time Fleet Tracking</h1>
    <div class="bg-gray-800 rounded-xl p-8 shadow-lg flex flex-col gap-6">
        <p class="text-gray-300 text-lg">Monitor every machine and vehicle in real time. See live locations, status, and performance metrics on an interactive map. Instantly identify issues, optimize routes, and improve productivity.</p>
        <img src="/images/screenshots/feature-fleet-tracking-full.png" alt="Fleet Tracking Full Screenshot" class="rounded-lg border border-gray-700">
        <ul class="list-disc pl-6 text-gray-300 space-y-2">
            <li>Live map with machine icons and status colors</li>
            <li>Click any machine for detailed info and history</li>
            <li>Filter by status, type, or location</li>
            <li>Mobile-friendly and fast updates</li>
        </ul>
    </div>
</div>
@endsection