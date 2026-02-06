@extends('layouts.app')
@section('title', 'Fuel & Cost Management')
@section('content')
<div class="max-w-4xl mx-auto py-12 px-4">
    <h1 class="text-4xl font-bold text-pink-500 mb-6">Fuel & Cost Management</h1>
    <div class="bg-gray-800 rounded-xl p-8 shadow-lg flex flex-col gap-6">
        <p class="text-gray-300 text-lg">Track fuel usage, costs, and efficiency for every machine. Generate reports for cost optimization, compliance, and sustainability.</p>
        <img src="/images/screenshots/feature-fuel-full.png" alt="Fuel Management Full Screenshot" class="rounded-lg border border-gray-700">
        <ul class="list-disc pl-6 text-gray-300 space-y-2">
            <li>Fuel dispensing and consumption logs</li>
            <li>Cost per machine and per area</li>
            <li>Efficiency and loss detection</li>
            <li>Exportable fuel and cost reports</li>
        </ul>
    </div>
</div>
@endsection