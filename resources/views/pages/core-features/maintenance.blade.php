@extends('layouts.app')
@section('title', 'Maintenance & Alerts')
@section('content')
<div class="max-w-4xl mx-auto py-12 px-4">
    <h1 class="text-4xl font-bold text-yellow-500 mb-6">Maintenance & Alerts</h1>
    <div class="bg-gray-800 rounded-xl p-8 shadow-lg flex flex-col gap-6">
        <p class="text-gray-300 text-lg">Automate maintenance schedules, receive instant breakdown alerts, and track service history for every asset. Reduce downtime and extend equipment life.</p>
        <img src="/images/screenshots/feature-maintenance-full.png" alt="Maintenance Full Screenshot" class="rounded-lg border border-gray-700">
        <ul class="list-disc pl-6 text-gray-300 space-y-2">
            <li>Automated service reminders and logs</li>
            <li>Breakdown and warning alerts</li>
            <li>Maintenance cost tracking</li>
            <li>Exportable service history</li>
        </ul>
    </div>
</div>
@endsection