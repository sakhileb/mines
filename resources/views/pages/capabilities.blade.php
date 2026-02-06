@extends('layouts.app')
@section('title', 'Platform Capabilities')
@section('content')
<div class="max-w-5xl mx-auto py-12 px-4">
    <h1 class="text-4xl font-bold text-emerald-500 mb-6">Platform Capabilities</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-gray-800 rounded-xl p-6 shadow-lg flex flex-col gap-4">
            <h2 class="text-2xl font-semibold text-white">Multi-Site Management</h2>
            <p class="text-gray-300">Manage unlimited mining sites, teams, and users from a single dashboard with role-based access.</p>
            <img src="/images/screenshots/capability-multisite.png" alt="Multi-Site Screenshot" class="rounded-lg border border-gray-700">
        </div>
        <div class="bg-gray-800 rounded-xl p-6 shadow-lg flex flex-col gap-4">
            <h2 class="text-2xl font-semibold text-white">API & Integrations</h2>
            <p class="text-gray-300">RESTful API, webhooks, and integrations with popular mining and ERP software.</p>
            <img src="/images/screenshots/capability-api.png" alt="API Screenshot" class="rounded-lg border border-gray-700">
        </div>
        <div class="bg-gray-800 rounded-xl p-6 shadow-lg flex flex-col gap-4">
            <h2 class="text-2xl font-semibold text-white">Custom Reporting</h2>
            <p class="text-gray-300">Build custom dashboards and export data for compliance, audits, and operational analysis.</p>
            <img src="/images/screenshots/capability-reports.png" alt="Reports Screenshot" class="rounded-lg border border-gray-700">
        </div>
        <div class="bg-gray-800 rounded-xl p-6 shadow-lg flex flex-col gap-4">
            <h2 class="text-2xl font-semibold text-white">24/7 Support & Training</h2>
            <p class="text-gray-300">Access to a dedicated support team, onboarding, and training resources for your staff.</p>
            <img src="/images/screenshots/capability-support.png" alt="Support Screenshot" class="rounded-lg border border-gray-700">
        </div>
    </div>
</div>
@endsection